<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        return view('auth.register');
    }
     function registerPost(Request $request){
        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');

        $user = new \App\Models\User();
        $user->name = $name;
        $user->email = $email;
        $user->password = bcrypt($password);
        $user->save();
        return redirect('/login')->with('success', 'Registration successful. Please log in.');
    }

    public function login(Request $request)
    {
        return view('auth.login');
    }
    function loginPost(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            return redirect('/')->with('success', 'Login successful.');
        }

        return redirect('/')->with('error', 'Invalid credentials.');
    }    
    
    function logout()
    {
        Auth::logout();
        return redirect('/login')->with('success', 'You have been logged out.');
    }
}
