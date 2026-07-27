<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\UserValidationTrait;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    use UserValidationTrait;

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate using the trait
        $validated = $this->validateUserRegistration($request->all());

        $user = User::create([
            'name' => $validated['name'],
            'id_num' => $validated['id_num'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'age' => $validated['age'],
            'gender' => $validated['gender'],
            'address' => $validated['address'],
            'role' => $validated['role'] ?? 'user',
        ]);

        event(new Registered($user));

       // Auth::login($user);

        // Redirect to login page
        return redirect()->route('login')->with('success', 'Account created successfully! Please login.');
    }
}