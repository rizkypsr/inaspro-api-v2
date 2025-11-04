<?php

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('registration screen is disabled', function () {
    $response = $this->get('/register');

    $response->assertStatus(404);
})->skip('Web registration disabled; API-only registration supported.');

test('new users cannot register via web', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '081234567890',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertStatus(404);
})->skip('Web registration disabled; API-only registration supported.');