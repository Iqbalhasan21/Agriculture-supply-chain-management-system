@php($fieldName = $name ?? 'district')
<div class="district-picker" data-district-picker>
    <i class="bi bi-search district-picker-search-icon" aria-hidden="true"></i>
    <input id="{{ $id ?? $fieldName }}" name="{{ $fieldName }}" value="{{ $selected ?? '' }}" placeholder="{{ $placeholder ?? 'Search district...' }}" autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" data-district-input @if (!empty($required)) required @endif>
    <i class="bi bi-chevron-down district-picker-chevron" aria-hidden="true"></i>
    <div class="district-picker-options" role="listbox" hidden>
        @foreach ($districts as $d)
            <button type="button" role="option" class="district-picker-option" data-district-option="{{ $d['name'] }}"><span>{{ $d['name'] }}</span><small>{{ $d['division'] }}</small></button>
        @endforeach
        <p class="district-picker-empty" data-district-empty hidden>No matching district found.</p>
    </div>
</div>
