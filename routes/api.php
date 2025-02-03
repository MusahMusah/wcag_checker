<?php

use App\Http\Controllers\WCAGController;
use Illuminate\Support\Facades\Route;

Route::post('/check', WCAGController::class)->name('check');

Route::get('/openai', function () {
    $result = \Gemini\Laravel\Facades\Gemini::geminiPro()->generateContent("Suggest an improved accessibility fix for: Provide a meaningful alt attribute for images.");

    dd($result->text());
});
