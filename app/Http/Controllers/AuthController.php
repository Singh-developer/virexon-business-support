<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }
    public function login(LoginRequest $request): RedirectResponse
    {
        $loginField = $request->input('login');
        
        $user = \App\Models\User::where('email', $loginField)
            ->orWhereHas('detail', function ($query) use ($loginField) {
                $query->where('agent_id_number', $loginField);
            })->first();

        if ($user && Auth::attempt(['email' => $user->email, 'password' => $request->input('password')], $request->boolean('remember'))) {
            $request->session()->regenerate();
            if (Auth::user()->status !== 'active') {
                Auth::logout();
                return back()->withErrors(['login' => 'This account is inactive.']);
            }
            return redirect()->intended(route('dashboard'));
        }
        return back()->withErrors(['login' => 'The provided credentials are incorrect.'])->onlyInput('login');
    }
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
