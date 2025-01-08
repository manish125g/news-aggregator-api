<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Traits\ApiResponder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponder;

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);
        return $this->sendSuccess('Registration successful', $user, 201);
    }

    public function login(Request $request)
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

    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();
        return $this->sendSuccess('Logged out successfully', []);
    }

}
