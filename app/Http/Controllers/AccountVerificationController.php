<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\FarmDirectNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\ValidationException;

class AccountVerificationController
{
    public function show(Request $request)
    {
        $verification = DB::table('user_verifications')->where('user_id', $request->user()->id)->first();
        abort_unless($verification, 404);

        return view('pages.account-verification', compact('verification'));
    }

    public function submit(Request $request)
    {
        $user = $request->user();
        abort_unless(in_array($user->role, ['client', 'farmer'], true), 403);
        $request->merge(['nid_number' => User::normalizeNid((string) $request->input('nid_number'))]);
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
            'nid_number' => ['required', 'regex:/^(?:[0-9]{10}|[0-9]{13}|[0-9]{17})$/'],
            'id_front' => ['required', File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max(5 * 1024)],
            'id_back' => ['required', File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max(5 * 1024)],
            'face_photo' => ['required', File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max(5 * 1024)],
            'consent' => ['accepted'],
        ]);
        $nid = User::normalizeNid($data['nid_number']);
        $submittedNidHash = User::nidHashFor($nid);
        if ($user->nid_hash && ! hash_equals($user->nid_hash, $submittedNidHash)) {
            throw ValidationException::withMessages([
                'nid_number' => 'This NID does not match the number on your account. Check the number you entered at signup or contact support.',
            ]);
        }

        $disk = Storage::disk('local');
        $files = [
            'id_front_path' => $disk->putFile('verification/' . $user->id, $request->file('id_front')),
            'id_back_path' => $disk->putFile('verification/' . $user->id, $request->file('id_back')),
            'face_path' => $disk->putFile('verification/' . $user->id, $request->file('face_photo')),
        ];
        abort_if(in_array(false, $files, true), 500, 'The verification files could not be saved. Please try again.');
        $now = now();
        DB::transaction(function () use ($user, $data, $files, $now, $submittedNidHash, $nid) {
            $userUpdate = ['phone' => trim($data['phone']), 'updated_at' => $now];
            // Older accounts may predate NID hashing; bind their first submitted NID for later checks.
            if (! $user->nid_hash) {
                $userUpdate['nid_hash'] = $submittedNidHash;
            }
            DB::table('users')->where('id', $user->id)->update($userUpdate);
            DB::table('user_verifications')->where('user_id', $user->id)->update($files + [
            'nid_encrypted' => Crypt::encryptString($nid), 'consent_at' => $now,
            'status' => 'pending', 'submitted_at' => $now,
            'phone_verified_at' => null, 'nid_verified_at' => null, 'face_verified_at' => null,
            'reviewed_by' => null, 'reviewed_at' => null, 'review_note' => null, 'updated_at' => $now,
            ]);
        });
        FarmDirectNotifier::role('admin', 'Account verification submitted', $user->name . ' submitted identity verification documents.', '/admin/users');

        return back()->with('status', 'Verification submitted. An administrator will call your phone and review your NID and face photo.');
    }

    public function document(Request $request, int $userId, string $kind)
    {
        abort_unless($request->user()?->role === 'admin', 403);
        abort_unless(in_array($kind, ['id-front', 'id-back', 'face'], true), 404);
        $column = match ($kind) {
            'id-front' => 'id_front_path', 'id-back' => 'id_back_path', 'face' => 'face_path',
        };
        $path = DB::table('user_verifications')->where('user_id', $userId)->value($column);
        abort_unless($path && Storage::disk('local')->exists($path), 404);
        $absolute = Storage::disk('local')->path($path);

        return response()->file($absolute, [
            'Content-Type' => mime_content_type($absolute) ?: 'application/octet-stream',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function withdraw(Request $request)
    {
        DB::transaction(function () use ($request) {
            $verification = DB::table('user_verifications')->where('user_id', $request->user()->id)->lockForUpdate()->first();
            abort_unless($verification && $verification->status === 'pending', 409, 'There is no pending verification request to withdraw.');
            DB::table('user_verifications')->where('user_id', $request->user()->id)->update([
                'status' => 'not_submitted', 'id_front_path' => null, 'id_back_path' => null, 'face_path' => null,
                'nid_encrypted' => null, 'consent_at' => null, 'submitted_at' => null,
                'phone_verified_at' => null, 'nid_verified_at' => null, 'face_verified_at' => null,
                'reviewed_by' => null, 'reviewed_at' => null, 'review_note' => 'Consent was withdrawn. Submit a new request to continue.', 'updated_at' => now(),
            ]);
        });
        Storage::disk('local')->deleteDirectory('verification/' . $request->user()->id);

        return back()->with('status', 'Consent withdrawn. Your uploaded ID and face photos and temporary NID data were deleted.');
    }
}
