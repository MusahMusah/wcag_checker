<?php

use App\Http\Controllers\WCAGController;
use Illuminate\Support\Facades\Route;

Route::post('/check', WCAGController::class)->name('check');