<?php

use App\Models\Company;
use App\Models\Contact;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    dump(Contact::all());
    dump(Company::all());
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->prefix('dashboard')->group(function () {
    Route::get('/check-saloon', function () {});
    Volt::route('objects/company', 'companies.index')->name('dashboard.objects.companies.index');
    Volt::route('objects/contact', 'contact.index')->name('dashboard.objects.contact.index');
});

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');
    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
