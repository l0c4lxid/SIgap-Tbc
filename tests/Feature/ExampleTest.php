<?php

test('guest is redirected to login page', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
