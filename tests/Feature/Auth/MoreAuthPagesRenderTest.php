<?php

test('more auth pages render', function () {
    $this->get(route('password.request'))->assertOk();
});
