<?php
namespace App\Http\Controllers;
use App\Http\Requests\LoginRequest;use Illuminate\Support\Facades\Auth;use Illuminate\Http\RedirectResponse;use Illuminate\Http\Request;
class AuthController extends Controller {
 public function showLogin(){return view('auth.login');}
 public function login(LoginRequest $request):RedirectResponse{if(Auth::attempt($request->only('email','password'),$request->boolean('remember'))){$request->session()->regenerate();if(Auth::user()->status!=='active'){Auth::logout();return back()->withErrors(['email'=>'This account is inactive.']);}return redirect()->intended(route('dashboard'));}return back()->withErrors(['email'=>'The provided credentials are incorrect.'])->onlyInput('email');}
 public function logout(Request $request):RedirectResponse{Auth::logout();$request->session()->invalidate();$request->session()->regenerateToken();return redirect()->route('login');}
}
