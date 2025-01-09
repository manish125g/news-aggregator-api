<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class RegisterTest extends TestCase
{
    public function test_user_can_register_with_valid_data()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'data' => ['id', 'name', 'email']]);

        $this->assertDatabaseHas('users', ['email' => 'johndoe@example.com']);
    }

    public function test_registration_fails_with_duplicate_email()
    {
        User::factory()->create(['email' => 'johndoe0@example.com']);

        $response = $this->postJson('/api/register', [
            'name' => 'Jane Doe',
            'email' => 'johndoe0@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors'])
            ->assertJsonFragment([
                'email' => ['The email address is already in use.']
            ]);
    }

    public function test_registration_fails_with_invalid_data()
    {
        $response = $this->postJson('/api/register', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'short',
            'password_confirmation' => 'notmatching',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors'])
            ->assertJsonFragment([
                'name' => ['Name is required.'],
                'email' => ['The email must be a valid email address.'],
                'password' => [
                    'Password must be at least 8 characters.',
                    'The password confirmation does not match.',
                ],
            ]);
    }

    public function test_user_can_login_with_valid_credentials()
    {
        $user = \App\Models\User::factory()->create([
            'email' => 'johndoe1@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'johndoe1@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'access_token',
                    'token_type',
                ]
            ]);
    }

    public function test_login_fails_with_invalid_credentials()
    {
        \App\Models\User::factory()->create([
            'email' => 'johndoe2@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'johndoe2@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Invalid credentials']);
    }

    public function test_forgot_password_sends_email_for_valid_user()
    {
        Mail::fake();

        $user = User::factory()->create(['email' => 'johndoe3@example.com']);

        $response = $this->postJson('/api/forgot-password', ['email' => 'johndoe3@example.com']);

        $response->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'We have emailed your password reset link.']);

    }

    public function test_forgot_password_fails_for_invalid_email()
    {
        $response = $this->postJson('/api/forgot-password', ['email' => 'nonexistent@example.com']);

        $response->assertStatus(422)
            ->assertJson(['success' => false, 'message' => 'We can\'t find a user with that email address.']);
    }

    public function test_forgot_password_fails_for_missing_email()
    {
        $response = $this->postJson('/api/forgot-password', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_password_reset_works_with_valid_token()
    {
        Password::shouldReceive('reset')->once()->andReturn(Password::PASSWORD_RESET);

        $user = User::factory()->create(['email' => 'johndoe4@example.com']);

        $response = $this->postJson('/api/reset-password', [
            'email' => 'johndoe4@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
            'token' => 'valid-token',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Your password has been reset.']);
    }

    public function test_password_reset_fails_with_invalid_token()
    {
        Password::shouldReceive('reset')->once()->andReturn(Password::INVALID_TOKEN);

        $response = $this->postJson('/api/reset-password', [
            'email' => 'johndoe4@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
            'token' => 'invalid-token',
        ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false, 'message' => 'This password reset token is invalid.']);
    }

    public function test_password_reset_fails_for_invalid_data()
    {
        $response = $this->postJson('/api/reset-password', [
            'email' => 'invalid-email',
            'password' => 'short',
            'password_confirmation' => 'notmatching',
            'token' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['token', 'email', 'password']);
    }

    public function test_forgot_password_rate_limit()
    {
        for ($i = 0; $i < 10; $i++) {
            $response = $this->postJson('/api/forgot-password', ['email' => 'johndoe@example.com']);
        }

        $response->assertStatus(422)
            ->assertJson(['message' => 'Please wait before retrying.']);
    }

    public function test_user_can_logout()
    {
        $user = \App\Models\User::factory()->create();

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Logged out successfully']);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }

}
