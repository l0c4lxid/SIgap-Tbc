<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Actions\CreateNewUser;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class FortifyServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Custom login view route
        Fortify::loginView(function () {
            return view('auth.login');
        });

        Fortify::registerView(function () {
            return view('auth.register');
        });

        // Login handling (default Fortify handles)
        // Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::createUsersUsing(\Laravel\Fortify\Actions\CreateNewUser::class);

        // If you want to customize authentication validation, override here.

        // Enable email verification routes (Fortify only handles backend)
        // Ensure Illuminate\Auth\Notifications\VerifyEmail used (default)
    }
}
