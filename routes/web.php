<?php

use App\Http\Controllers\LandingPageController;
use App\Livewire\SignUp;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingPageController::class)->name('home');
Route::livewire('/sign-up', SignUp::class)->name('sign-up');
