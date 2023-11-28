<?php
// app/Http/Controllers/AuthController.php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();    }

    /**
     * Obtain the user information from Google.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleGoogleCallback()
    {
        try {
            $userData = Socialite::driver('google')->user();
       

            $user = User::where('email', $userData->email)->first();
            if (!$user) {
            // User does not exist, create a new user
            $user = User::create([
                'name' => $userData->name,
                'email' => $userData->email,
                'google_id' => $userData->id, // Save the Google ID if needed
                // Add other relevant user fields
            ]);
        }

        $user->update(['gcalendar_access_token'=>$userData->token]);
 
        Auth::login($user,true);
            return redirect('/events');
        } catch (\Exception $e) {
             // Handle exceptions (e.g., user denied access)
            return redirect('/login')->withErrors('Google authentication failed.');
        }
    }
}
