@extends('layouts.app')

@section('content')
<style>
    .table-wrap { overflow-x: auto; background: white; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; }
    table.dataTable thead th { border-bottom: 1px solid #e2e8f0; color: #475569; font-size: 12px; text-transform: uppercase; background: #f8fafc; white-space: nowrap; }
    table.dataTable tbody td { border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 13px; vertical-align: middle; }
    table.dataTable tbody tr:hover { background-color: #f8fafc; }
    .trash-tabs { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px; }
    .trash-tab { text-decoration: none; display: inline-flex; align-items: center; gap: 8px; padding: 8px 14px; border-radius: 8px; border: 1px solid #e2e8f0; background: white; color: #475569; font-size: 13px; font-weight: 600; }
    .trash-tab:hover { border-color: #93c5fd; color: #1e40af; }
    .trash-tab.active { background: #2563eb; border-color: #2563eb; color: white; }
    .trash-tab .count { background: #f1f5f9; color: #475569; border-radius: 99px; padding: 1px 8px; font-size: 11px; font-weight: 700; }
    .trash-tab.active .count { background: rgba(255,255,255,0.25); color: white; }
    .trash-tab.empty .count { background: #fee2e2; color: #b91c1c; }
    .btn.danger { background: #dc2626; color: white; border: 1px solid #b91c1c; }
    .btn.danger:hover { background: #b91c1c; }
    .btn.restore { background: #059669; color: white; border: 1px solid #047857; }
    .btn.restore:hover { background: #047857; }
    .notice { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; border-radius: 8px; padding: 12px 16px; font-size: 13px; margin-bottom: 16px; }
</style>

<div class="page-head">
    <div>
        <div class="eyebrow">ADMIN TRASH</div>
        <h1>Trash</h1>
        <p style="color:#64748b; font-size:14px; margin-top:5px;">Soft-deleted agents, sanction letters, virtual cards and related records. Files stay on disk until a record is permanently deleted.</p>
    </div>
</div>

<div class="notice">
    Deleting an agent also moves its sanction letters, virtual card, documents, advances, commissions, payments, details and references to the trash. Uploaded files are only removed when you <strong>permanently delete</strong> from here — restoring first brings everything back.
</div>

<div class="trash-tabs">
    @foreach($types as $key => $type)
        @php $c = $counts[$key] ?? 0; @endphp
        <a class="trash-tab {{ $key === $active ? 'active' : '' }} {{ $c === 0 ? 'empty' : '' }}" href="{{ route('admin.trash.index', ['type' => $key]) }}">
            <span>{{ $type['icon'] }}</span>
            <span>{{ $type['label'] }}</span>
            <span class="count">{{ $c }}</span>
        </a>
    @endforeach
</div>

@php $config = $types[$active]; @endphp

<div class="table-wrap">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; flex-wrap:wrap; gap:10px;">
        <strong style="font-size:14px; color:#1e293b;">{{ $config['label'] }} — Trashed</strong>
        @if(auth()->user()->isSuperAdmin())
            @if(($counts[$active] ?? 0) > 0)
            <form method="POST" action="{{ route('admin.trash.purge', $active) }}" onsubmit="return confirm('Permanently delete ALL {{ ($counts[$active] ?? 0) }} trashed {{ strtolower($config['label']) }}? Files on disk will be removed. This cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn danger tiny">Empty category</button>
            </form>
            @endif
        @endif
    </div>

    <table class="display nowrap" style="width:100%">
        <thead>
            <tr>
                <th>Record</th>
                <th>Details</th>
                <th>Deleted At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                <tr>
                    <td><strong>{{ $config['title']($item) }}</strong></td>
                    <td>
                        @if(isset($config['meta']) && is_callable($config['meta']))
                            <span style="color:#64748b; font-size:13px;">{{ $config['meta']($item) }}</span>
                        @else
                            <span style="color:#94a3b8;">—</span>
                        @endif
                    </td>
                    <td style="color:#64748b;">
                        {{ optional($item->deleted_at)->format('d M Y, h:i A') ?? '—' }}
                    </td>
                    <td>
                        <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                            <form method="POST" action="{{ route('admin.trash.restore', [$active, $item->getKey()]) }}">
                                @csrf
                                <button type="submit" class="btn restore tiny">Restore</button>
                            </form>

                            <form method="POST" action="{{ route('admin.trash.force-delete', [$active, $item->getKey()]) }}"
                                onsubmit="return confirm('Permanently delete this record? Any uploaded files for it will be removed from disk. This cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn danger tiny">Delete Permanently</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align:center; padding:30px; color:#64748b;">
                        No trashed {{ strtolower($config['label']) }} right now.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($items->hasPages())
    <div style="margin-top:15px; display:flex; justify-content:center;">
        {{ $items->links() }}
    </div>
    @endif
</div>
@endsection