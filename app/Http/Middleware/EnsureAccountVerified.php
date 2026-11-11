<?php

namespace App\Http\Middleware;

use App\Services\AccountVerification;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && in_array($user->role, ['client', 'farmer'], true) && ! AccountVerification::isVerified($user->id)) {
            return redirect()->route('account.verification');
        }

        return $next($request);
    }
}
