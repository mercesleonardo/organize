<?php

test('auth pages render', function () {
    $this->get(route('login'))->assertOk();

    if (\Illuminate\Support\Facades\Route::has('register')) {
        $this->get(route('register'))->assertOk();
    }
});
