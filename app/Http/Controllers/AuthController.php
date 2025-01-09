<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Traits\ApiResponder;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponder;

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);
            return $this->sendSuccess('Registration successful', $user, 201);
        } catch (UniqueConstraintViolationException $exception) {
            return $this->sendError('Email already Exists.', [['email' => __('custom.email.unique')]], 422);
        } catch (\Exception $ex) {
            return $this->sendError('An unexpected error occurred', [$ex->getMessage()], 500);
        }
    }

    public function login(Request $request): JsonResponse
    {
        try {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            if (!auth()->attempt($credentials)) {
                return $this->sendError('Invalid credentials', [], 401);
            }

            $token = auth()->user()->createToken('secret_token')->plainTextToken;

            return $this->sendSuccess('Login successful', [
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]);
        } catch (ValidationException $ex) {
            return $this->sendError('Validation failed', $ex->errors(), 422);
        } catch (\Exception $ex) {
            return $this->sendError('An unexpected error occurred', [$ex->getMessage()], 500);
        }
    }

    public function forgotPassword(Request $request): JsonResponse
    {

        $request->validate(['email' => 'required|email']);
        try {
            $status = Password::sendResetLink(
                $request->only('email')
            );
            return $status === Password::ResetLinkSent ? $this->sendSuccess(__($status)) : $this->sendError(__($status), [], 422);
        } catch (\Exception $ex) {
            return $this->sendError('An unexpected error occurred', [$ex->getMessage()], 500);
        }
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );
        return $status === Password::PasswordReset ? $this->sendSuccess(__($status)) : $this->sendError(__($status), [], 422);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();
        return $this->sendSuccess('Logged out successfully', []);
    }

}
