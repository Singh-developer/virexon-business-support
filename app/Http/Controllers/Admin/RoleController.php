<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Role Management
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $roles = Role::withCount('users')->latest()->get();

        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        return view('admin.roles.form', ['role' => new Role()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:roles,slug'],
        ]);

        Role::create($data);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role created successfully.');
    }

    public function edit(Role $role)
    {
        return view('admin.roles.form', compact('role'));
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('roles')->ignore($role)],
        ]);

        $role->update($data);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        if ($role->slug === 'super-admin') {
            return back()->with('error', 'Cannot delete the super-admin role.');
        }

        if ($role->users()->exists()) {
            return back()->with('error', 'Cannot delete a role that is assigned to users.');
        }

        $role->delete();

        return back()->with('success', 'Role deleted.');
    }

    /*
    |--------------------------------------------------------------------------
    | User list + role assignment
    |--------------------------------------------------------------------------
    */

    public function users()
    {
        $users = User::with('role', 'business', 'detail')
            ->orderByDesc('id')
            ->paginate(25);

        $roles = Role::orderBy('name')->get();

        return view('admin.roles.users', compact('users', 'roles'));
    }

    public function assignRole(Request $request, User $user)
    {
        $request->validate([
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $user->update(['role_id' => $request->role_id]);

        return back()->with('success', "Role updated for {$user->name}.");
    }
}