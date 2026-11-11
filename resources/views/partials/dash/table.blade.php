{{-- $cols: header labels. $rows: list of rows. A cell is text, or an array with one of the keys: badge, user(+photo), thumb(+image,emoji), actions --}}
<div class="table-wrap">
    <table @if (!empty($real)) data-real @endif @isset($tableId) data-table="{{ $tableId }}" @endisset @isset($approveInto) data-approve-into="{{ $approveInto }}" @endisset>
        <thead><tr>@foreach ($cols as $c)<th>{{ $c }}</th>@endforeach</tr></thead>
        <tbody>
        @foreach ($rows as $row)
            <tr>
                @foreach ($row as $cell)
                    <td>
                        @if (is_array($cell) && isset($cell['badge']))
                            @include('partials.status-badge', ['status' => $cell['badge']])
                        @elseif (is_array($cell) && isset($cell['user']))
                            <div class="table-user">
                                @if (!empty($cell['photo']))<img class="avatar" src="{{ asset('images/' . $cell['photo']) }}" alt="">@else<span class="avatar"></span>@endif
                                <span>{{ $cell['user'] }}</span>
                            </div>
                        @elseif (is_array($cell) && isset($cell['thumb']))
                            <div class="table-user">
                                @if (!empty($cell['image']))<img class="thumb-sm" src="{{ asset('images/' . $cell['image']) }}" alt="">@else<span class="thumb-sm">{{ $cell['emoji'] ?? '📦' }}</span>@endif
                                <span>{{ $cell['thumb'] }}</span>
                            </div>
                        @elseif (is_array($cell) && isset($cell['actions']))
                            <span class="actions">
                                @foreach ($cell['actions'] as $a)
                                    @php
                                        $ai = 'three-dots';
                                        foreach (['View' => 'eye', 'Edit' => 'pencil-square', 'Delete' => 'trash', 'Approve' => 'check-lg', 'Reject' => 'x-lg', 'Assign' => 'person-plus', 'Track' => 'geo-alt', 'Download' => 'download', 'Restore' => 'arrow-counterclockwise', 'Move' => 'arrow-left-right', 'Update Stock' => 'arrow-repeat', 'Activate' => 'toggle-on', 'Deactivate' => 'toggle-off', 'Cancel' => 'x-circle'] as $k => $v) { if ($a === $k) $ai = $v; }
                                    @endphp
                                    <a href="#" data-row-action="{{ $a }}" title="{{ $a }}" @if (preg_match('/Reject|Delete|Cancel|Remove/', $a)) style="color:var(--r)" @endif><i class="bi bi-{{ $ai }}"></i> {{ $a }}</a>
                                @endforeach
                            </span>
                        @else
                            {{ $cell }}
                        @endif
                    </td>
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
