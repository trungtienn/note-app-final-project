<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PreferenceController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\LabelController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::patch('/preferences', [PreferenceController::class, 'update'])->name('preferences.update');
    Route::get('/api/notes', [NoteController::class, 'index'])->name('notes.index');
    Route::post('/api/notes/auto-save', [NoteController::class, 'autoSave'])->name('notes.autoSave');
    Route::post('/api/notes/{note}/toggle-pin', [NoteController::class, 'togglePin'])->name('notes.togglePin');
    Route::delete('/api/notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');
    Route::post('/api/notes/{note}/attachments', [NoteController::class, 'uploadAttachments'])->name('notes.attachments.upload');
    Route::delete('/api/attachments/{attachment}', [NoteController::class, 'deleteAttachment'])->name('attachments.destroy');
    Route::get('/api/labels', [LabelController::class, 'index'])->name('labels.index');
    Route::post('/api/labels', [LabelController::class, 'store'])->name('labels.store');
    Route::put('/api/labels/{label}', [LabelController::class, 'update'])->name('labels.update');
    Route::delete('/api/labels/{label}', [LabelController::class, 'destroy'])->name('labels.destroy');
    Route::post('/api/notes/{note}/sync-labels', [NoteController::class, 'syncLabels'])->name('notes.syncLabels');
});

require __DIR__.'/auth.php';
