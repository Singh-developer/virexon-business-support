@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <div class="eyebrow">ACCESS CONTROL</div>
        <h1>{{ $role->exists ? $role->name : 'Create Role' }}</h1>
        <p>{{ $role->exists ? 'Update role name and slug.' : 'Create a new platform role and assign it to users.' }}</p>
    </div>
    <a class="btn secondary" href="{{ route('admin.roles.index') }}">← Back to Roles</a>
</div>

<div class="panel form-panel">
    <form method="POST" action="{{ $role->exists ? route('admin.roles.update', $role) : route('admin.roles.store') }}">
        @csrf
        @if($role->exists) @method('PUT') @endif

        <div class="form-grid">
            <label>
                Role Name
                <input type="text" name="name" value="{{ old('name', $role->name) }}" style="color:#1e293b" required>
            </label>
            <label>
                Slug (lowercase, no spaces)
                <input type="text" name="slug" value="{{ old('slug', $role->slug ?? '') }}" style="color:#1e293b" {{ $role->exists && $role->slug === 'super-admin' ? 'readonly' : '' }} required>
            </label>
            @if($role->exists && $role->slug === 'super-admin')
                <div class="notice">The Super Admin role slug cannot be edited.</div>
            @endif
        </div>

        <div class="form-actions">
            <button class="btn primary">{{ $role->exists ? 'Save Changes' : 'Create Role' }}</button>
        </div>
    </form>
</div>
@endsection
