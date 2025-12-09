<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn() => Inertia::render('RadioFrancine'))->name('radio.francine');

require __DIR__.'/settings.php';
