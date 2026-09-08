@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<style>
    .table-wrap { overflow-x: auto; background: white; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; }
    .page-head { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
    table.dataTable thead th { border-bottom: 1px solid #e2e8f0; color: #475569; font-size: 12px; text-transform: uppercase; background: #f8fafc; }
    table.dataTable tbody td { border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 14px; vertical-align: middle; }
    table.dataTable tbody tr:hover { background-color: #f8fafc; }
    .role-form { display: inline-flex; align-items: center; gap: 4px; }
</style>

<div class="page-head">
    <div>
        <div class="eyebrow">ACCESS CONTROL</div>
        <h1>Users</h1>
        <p style="color:#64748b; font-size:14px; margin-top:5px;">List of all platform users. Admins (Super Admin or Admin role) can reassign a user's role inline.</p>
    </div>
    <div style="display: flex; gap: 10px; flex-shrink: 0;">
        <a class="btn secondary" href="{{ route('admin.roles.index') }}">← Roles</a>
    </div>
</div>

<div class="panel" style="padding: 0; background: transparent; border: none; box-shadow: none;">
    <div class="table-wrap">
        <table id="usersTable" class="display nowrap" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Business</th>
                    <th>Created</th>
                    <th class="text-end">Assign Role</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                @php
                    $badge = match($user->status) {
                        'active'  => 'success',
                        'inactive'=> 'failed',
                        default   => 'pending',
                    };
                @endphp
                <tr>
                    <td>{{ $user->id }}</td>
                    <td><strong>{{ $user->name }}</strong></td>
                    <td>{{ $user->email }}</td>
                    <td><span class="badge {{ $user->role?->slug === 'super-admin' ? 'neutral' : ($user->role?->slug === 'admin' ? 'success' : 'pending' ); }}">{{ $user->role?->name ?? '—' }}</span></td>
                    <td><span class="badge {{ $badge }}">{{ ucfirst($user->status) }}</span></td>
                    <td>{{ $user->business?->name ?? '—' }}</td>
                    <td>{{ optional($user->created_at)?->format('Y-m-d H:i') ?? '—' }}</td>
                    <td class="text-end">
                        @if($user->role?->slug === 'super-admin')
                            <span class="text-muted" style="font-size:12px;color:#94a3b8">Protected</span>
                        @else
                            <form method="POST" action="{{ route('admin.users.role', $user) }}" class="role-form" onsubmit="return confirm('Reassign role for {{ $user->name }}?');">
                                @csrf
                                @method('PATCH')
                                <select name="role_id" required>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" @selected($user->role_id === $role->id)>{{ $role->name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn tiny primary">Update</button>
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center" style="padding:20px;color:#94a3b8;">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="padding: 15px 20px;">
        {{ $users->withQueryString()->links() }}
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function () {
    $('#usersTable').DataTable({
        pageLength: 25,
        order: [[6, 'desc']],
        columnDefs: [{ targets: [7], orderable: false }],
        language: { search: "Quick Search:" }
    });
});
</script>
@endsection
