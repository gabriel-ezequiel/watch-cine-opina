<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return redirect()->route('feed');
});

Route::middleware('guest')->group(function () {
    Route::livewire('/register', 'pages::auth.register')->name('register');
    Route::livewire('/login', 'pages::auth.login')->name('login');
});

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->middleware('auth')->name('logout');

Route::livewire('/feed', 'pages::home.feed')->middleware('auth')->name('feed');
