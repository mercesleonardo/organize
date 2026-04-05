<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', 'pages::dashboard')->name('dashboard');

    Route::livewire('finance/categories', 'pages::finance.categories')->name('finance.categories.index');
    Route::livewire('finance/despesas', 'pages::finance.expenses-index')->name('finance.expenses.index');
    Route::livewire('finance/despesas/criar', 'pages::finance.expense-create')->name('finance.expenses.create');
    Route::livewire('finance/receitas', 'pages::finance.incomes-index')->name('finance.incomes.index');
    Route::livewire('finance/receitas/criar', 'pages::finance.income-create')->name('finance.incomes.create');
});

require __DIR__ . '/settings.php';
