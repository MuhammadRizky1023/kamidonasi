<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Laravel\Firebase\Facades\Firebase;

class AuthController extends Controller
{

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        try {
            $auth = Firebase::auth();
            $user = $auth->signInWithEmailAndPassword($credentials['email'], $credentials['password']);
            if ($user) {
                return redirect()->intended('/donations');
            } else {
                return back()->withInput()->withErrors(['email' => 'Email atau password salah.']);
            }
        } catch (\Kreait\Firebase\Auth\SignIn\FailedToSignIn $e) {
            return back()->withInput()->withErrors(['email' => 'Email atau password salah.']);
        }
    }



    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $credentials = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        try {
            $auth = Firebase::auth();
            $auth->createUserWithEmailAndPassword($credentials['email'], $credentials['password'], [
                'displayName' => $credentials['name'],
            ]);

            return redirect()->intended('/login');
        } catch (\Kreait\Firebase\Auth\CreateUser\FailedToCreateUser $e) {
            return back()->withInput()->withErrors(['email' => 'Gagal melakukan registrasi.']);
        }
    }


    public function logout(Request $request)
    {
        Auth::logout(); // Lakukan proses logout menggunakan Auth Laravel

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home'); // Redirect ke halaman utama setelah logout
    }
}
