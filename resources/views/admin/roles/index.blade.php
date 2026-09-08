@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<style>
    .table-wrap { overflow-x: auto; background: white; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; }
    .page-head { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
    table.dataTable thead th { border-bottom: 1px solid #e2e8f0; color: #475569; font-size: 12px; text-transform: uppercase; background: #f8fafc; }
    table.dataTable tbody td { border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 14px; vertical-align: middle; }
    table.dataTable tbody tr:hover { background-color: #f8fafc; }
</style>

<div class="page-head">
    <div>
        <div class="eyebrow">ACCESS CONTROL</div>
        <h1>Roles</h1>
        <p style="color:#64748b; font-size:14px; margin-top:5px;">Define and manage platform roles. A default Super Admin role is protected.</p>
    </div>
    <div style="display: flex; gap: 10px; flex-shrink: 0;">
        <a class="btn secondary" href="{{ route('admin.users.index') }}"><i class="fa-solid fa-users"></i> Users List</a>
        <a class="btn primary" href="{{ route('admin.roles.create') }}">+ New Role</a>
    </div>
</div>

<div class="panel" style="padding: 0; background: transparent; border: none; box-shadow: none;">
    <div class="table-wrap">
        <table id="rolesTable" class="display nowrap" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Role Name</th>
                    <th>Slug</th>
                    <th>Users</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($roles as $role)
                <tr>
                    <td>{{ $role->id }}</td>
                    <td><strong>{{ $role->name }}</strong></td>
                    <td>{{ $role->slug }}</td>
                    <td>{{ $role->users_count }}</td>
                    <td class="text-end">
                        <a class="btn tiny secondary" href="{{ route('admin.roles.edit', $role) }}">Edit</a>
                        @if($role->slug === 'super-admin' || $role->users_count > 0)
                            <button type="button" class="btn tiny secondary" style="color:#94a3b8;border-color:#cbd5e1;cursor:not-allowed" title="Protected or in use">Delete</button>
                        @else
                            <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" style="display:inline" onsubmit="return confirm('Delete role {{ $role->name }}?'); ">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn tiny secondary" style="color:#e53e3e;border-color:#e53e3e">Delete</button>
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center" style="padding:20px;color:#94a3b8;">No roles found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function () {
    $('#rolesTable').DataTable({ pageLength: 25, order: [[0, 'desc']] });
});
</script>
@endsection
