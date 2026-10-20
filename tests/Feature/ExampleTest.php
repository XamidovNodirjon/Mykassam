<?php

test('redirects guests from the home page to the login page', function () {
    $response = $this->get(route('home'));

    $response->assertRedirect(route('login'));
});
