<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Support\TicketController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/auth/google/redirect', [GoogleController::class, 'redirect'])->name('auth.google.redirect');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', 'pages::dashboard')->name('dashboard');

    Route::post('suporte/chamados', [TicketController::class, 'store'])
        ->middleware(['throttle:support-ticket'])
        ->name('support.tickets.store');

    Route::livewire('finance/categories', 'pages::finance.categories')->name('finance.categories.index');
    Route::livewire('finance/despesas', 'pages::finance.expenses-index')->name('finance.expenses.index');
    Route::livewire('finance/despesas/criar', 'pages::finance.expense-create')->name('finance.expenses.create');
    Route::livewire('finance/receitas', 'pages::finance.incomes-index')->name('finance.incomes.index');
    Route::livewire('finance/receitas/criar', 'pages::finance.income-create')->name('finance.incomes.create');

    Route::livewire('investments', 'pages::investments.goals-index')->name('investments.goals.index');
    Route::livewire('investments/{goal}', 'pages::investments.goal-show')->name('investments.goals.show');
});

require __DIR__ . '/settings.php';
