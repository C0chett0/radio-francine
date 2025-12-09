<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\RadioFrancineController;

Route::get('/radio/francine/now-playing', [RadioFrancineController::class, 'nowPlaying']);
