<?php

// tests/Feature/UserTest.php

use App\Models\User;

// Higher order testing
it('has admin role')->expect(fn() => 
    User::factory()->create(['role' => 'admin'])->role
)->toBe('admin');

// Test for response
it('returns a successful response when accessing homepage')
    ->get('/')
    ->assertStatus(200);
    
// Chained assertions
it('creates a user in database')
    ->tap(fn() => User::factory()->create(['email' => 'test@example.com']))
    ->assertDatabaseHas('users', ['email' => 'test@example.com']);