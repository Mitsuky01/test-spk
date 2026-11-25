<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\PhController;
use App\Http\Controllers\PicController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SpkController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::redirect('/', '/spks');

    // Master data
    Route::get('clients-export', [ClientController::class, 'export'])->name('clients.export');
    Route::resource('clients', ClientController::class)->except('show');

    Route::get('pics-export', [PicController::class, 'export'])->name('pics.export');
    Route::resource('pics', PicController::class)->except('show');

    Route::get('vendors-export', [VendorController::class, 'export'])->name('vendors.export');
    Route::resource('vendors', VendorController::class)->except('show');

    // Project  (?status=open|done|canceled)
    Route::resource('projects', ProjectController::class)->except('show');

    // Dashboard: PH & SPK
    Route::get('phs-export', [PhController::class, 'export'])->name('phs.export');
    Route::patch('phs/{ph}/status', [PhController::class, 'updateStatus'])->name('phs.status');
    Route::resource('phs', PhController::class)->except('show')->parameters(['phs' => 'ph']);

    Route::get('spks-export', [SpkController::class, 'export'])->name('spks.export');
    Route::post('spks/{spk}/payments', [SpkController::class, 'addPayment'])->name('spks.payments.store');
    Route::delete('spk-payments/{payment}', [SpkController::class, 'deletePayment'])->name('spks.payments.destroy');
    Route::resource('spks', SpkController::class)->except('show')->parameters(['spks' => 'spk']);
    Route::get('spks/{spk}/pdf', [SpkController::class, 'pdf'])->name('spks.pdf');

    // Belum dibuat (ada di sidebar pada desain asli)
    Route::view('invoice', 'placeholder', ['title' => 'Invoice'])->name('invoice');
    Route::view('po', 'placeholder', ['title' => 'PO'])->name('po');

    // User
    Route::resource('users', UserController::class)->only(['index', 'create', 'store', 'destroy']);
});
