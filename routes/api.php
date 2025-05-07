<?php

use App\Http\Controllers\WebHookController;
use Illuminate\Support\Facades\Route;

Route::post('sync-contact', [WebHookController::class, 'syncContact'])->name('sync-contact');
Route::post('sync-company', [WebHookController::class, 'syncCompanies'])->name('sync-company');
Route::post('contact-association', [WebHookController::class, 'contactAssociationSync'])->name('contact-association');
