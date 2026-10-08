<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AnimalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeedingController;
use App\Http\Controllers\OwnershipTransferController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/analytics', AnalyticsController::class)->name('analytics');
    Route::get('/feeding', [FeedingController::class, 'index'])->name('feeding.index');
    Route::post('/feeding', [FeedingController::class, 'store'])->name('feeding.store');

    Route::get('/animals', [AnimalController::class, 'index'])->name('animals.index');
    Route::get('/animals/create', [AnimalController::class, 'create'])->name('animals.create');
    Route::post('/animals', [AnimalController::class, 'store'])->name('animals.store');
    Route::get('/animals/{animal}/edit', [AnimalController::class, 'edit'])->name('animals.edit');
    Route::put('/animals/{animal}', [AnimalController::class, 'update'])->name('animals.update');
    Route::delete('/animals/{animal}', [AnimalController::class, 'destroy'])->name('animals.destroy');
    Route::get('/animals/{animal}', [AnimalController::class, 'show'])->name('animals.show');
    Route::post('/animals/{animal}/vaccinations', [AnimalController::class, 'storeVaccination'])->name('animals.vaccinations.store');
    Route::post('/animals/{animal}/health-records', [AnimalController::class, 'storeHealthRecord'])->name('animals.health-records.store');
    Route::post('/animals/{animal}/missing', [AnimalController::class, 'markMissing'])->name('animals.missing');
    Route::post('/animals/{animal}/transfer', [OwnershipTransferController::class, 'store'])->name('animals.transfer.store');
    Route::get('/animals/{animal}/print', [AnimalController::class, 'printRecord'])->name('animals.print');
    Route::get('/animals/{animal}/qr/download', [AnimalController::class, 'downloadQrCode'])->name('animals.qr.download');
    Route::get('/transfers', [OwnershipTransferController::class, 'index'])->name('transfers.index');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('profile', 'profile')->name('profile');
});

Route::get('/t/{token}', [AnimalController::class, 'publicShow'])->name('animal.public');

require __DIR__.'/auth.php';
