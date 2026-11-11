<div class="tabs" data-filter>
    @foreach ($tabs as $t)
        <span class="tab {{ $loop->first ? 'active' : '' }}" role="button" tabindex="0">{{ $t }}</span>
    @endforeach
</div>
