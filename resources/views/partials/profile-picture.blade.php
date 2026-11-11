<div class="profile-picture-control" data-profile-picture-control>
    <button class="profile-picture-trigger" type="button" aria-label="Profile picture options" aria-expanded="false" data-profile-picture-trigger>
        <span class="profile-picture-avatar" data-profile-photo-preview>
            @if (!empty($photo))<img src="{{ asset('images/' . $photo) }}" alt="{{ $name }} profile picture">@else<span>{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($name, 0, 1)) }}</span>@endif
        </span>
        <span class="profile-picture-camera"><i class="bi bi-camera-fill"></i></span>
    </button>
    <div class="profile-picture-menu" data-profile-picture-menu hidden>
        <button type="button" data-see-profile-picture><i class="bi bi-person-square"></i><span>See profile picture</span></button>
        <label class="profile-picture-choose" for="{{ $photoInputId }}" data-choose-profile-picture><i class="bi bi-image"></i><span>Choose profile picture</span></label>
    </div>
</div>
<dialog class="profile-picture-dialog" data-profile-picture-dialog aria-label="Profile picture">
    <button type="button" class="profile-picture-dialog-close" aria-label="Close" data-close-profile-picture><i class="bi bi-x-lg"></i></button>
    @if (!empty($photo))<img src="{{ asset('images/' . $photo) }}" alt="{{ $name }} profile picture" data-profile-picture-full>@else<div class="profile-picture-empty" data-profile-picture-empty>{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($name, 0, 1)) }}</div>@endif
</dialog>
