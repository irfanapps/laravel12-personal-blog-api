<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\LoginResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\PersonalAccessToken;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Hashids\Hashids;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Register a new user
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            // Data already validated RegisterRequest
            $validated = $request->validated();

            // Prepare user data
            $data = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ];

            // Create user instance and save separately
            $user = new User($data);

            $user->save();

            // Return response menggunakan UserResource
            return response()->json([
                'status' => true,
                'message' => 'User registered successfully',
                'data' => [
                    'user' => new UserResource($user)
                ]
            ], 201);
        } catch (\Exception $e) {
            // Log error untuk debugging
            Log::error('Registration error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Failed to register user',
                'errors' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Login user
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            // Attempt to authenticate user
            if (!Auth::attempt($request->validated())) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            // Get authenticated user
            $user = User::where('email', $request->email)->firstOrFail();

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            // Set expiration time (example 1 day from now)
            //$expiresAt = Carbon::now()->addDays(1);
            $expiresAt = Carbon::now()->addMinutes(30);

            // Create new Sanctum token
            //$token = $user->createToken('auth_token')->plainTextToken;
            // Create token dan simpan expired_at
            $token = $user->createToken('auth_token', ['*'], $expiresAt)->plainTextToken;
            $user->tokens()->latest()->first()->update(['expires_at' => $expiresAt]);

            // Return response using LoginResource
            return response()->json([
                'status' => true,
                'message' => 'Login successful',
                'data' => new LoginResource([
                    'user' => $user,
                    'token' => $token,
                    'expires_at' => $expiresAt->toDateTimeString()
                ])
            ]);
        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Failed to login',
                'errors' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Logout user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            //$user =  $request->user()->currentAccessToken();
            //Log::info(json_encode($user));

            // Revoke current token
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'status' => true,
                'message' => 'Successfully logged out'
            ]);
        } catch (\Exception $e) {
            Log::error('Logout error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Failed to logout'
            ], 500);
        }
    }

    /**
     * Get authenticated user profile
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        try {
            // Check if user is authenticated via Sanctum
            if (!$request->user()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthenticated: No valid authentication token provided'
                ], 401);
            }

            // Verify the token exists in database
            $token = $request->user()->currentAccessToken();
            if (!$token) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid or expired token'
                ], 401);
            }

            // Return user data
            return response()->json([
                'status' => true,
                'message' => 'User profile retrieved successfully',
                'data' => new UserResource($request->user())
            ]);
        } catch (\Illuminate\Auth\AuthenticationException $e) {
            Log::error('Authentication error in me endpoint: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Authentication failed',
                'error' => $e->getMessage()
            ], 401);
        } catch (\Exception $e) {
            Log::error('Get profile error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Failed to get user profile',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Handle avatar upload if present
            if ($request->hasFile('avatar')) {
                // Delete old avatar if exists
                if ($user->avatar) {
                    Storage::delete('public/avatars/' . $user->avatar);
                }

                $avatarPath = $request->file('avatar')->store('avatars', 'public');
                $user->avatar = basename($avatarPath);
            }

            // Update other fields
            if ($request->filled('name')) {
                $user->name = $request->name;
            }

            Log::info(json_encode($request->filled('name')));


            if ($request->filled('email')) {
                $user->email = $request->email;
            }

            if ($request->filled('bio')) {
                $user->bio = $request->bio;
            }

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'Profile updated successfully',
                'data' => new UserResource($user)
            ]);
        } catch (\Illuminate\Auth\AuthenticationException $e) {
            Log::error('Authentication error in profile update: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Authentication failed',
                'error' => $e->getMessage()
            ], 401);
        } catch (\Exception $e) {
            Log::error('Profile update error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Failed to update profile',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function createToken(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Set expired_at 30 hari dari sekarang
        $expiredAt = Carbon::now()->addDays(30);

        $token = $user->createToken($request->device_name, ['*'], $expiredAt)->plainTextToken;

        return response()->json([
            'token' => $token,
            'expired_at' => $expiredAt->toDateTimeString()
        ]);
    }

    // /**
    //  * Send password reset link
    //  */
    // public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    // {
    //     $request->validated();

    //     // Check if user exists
    //     $user = User::where('email', $request->email)->first();

    //     if (!$user) {
    //         return response()->json([
    //             'message' => 'If the email exists in our system, a password reset link has been sent.'
    //         ], 200);
    //     }

    //     // Generate token
    //     $token = Password::createToken($user);

    //     // Send email (gunakan Mailtrap atau service email lainnya untuk testing)
    //     Mail::to($user->email)->send(new PasswordResetMail($token, $user->email));

    //     return response()->json([
    //         'message' => 'If the email exists in our system, a password reset link has been sent.'
    //     ]);
    // }

    // /**
    //  * Reset password
    //  */
    // public function resetPassword(ResetPasswordRequest $request): JsonResponse
    // {
    //     $request->validated();

    //     $response = Password::reset(
    //         $request->only('email', 'password', 'password_confirmation', 'token'),
    //         function ($user, $password) {
    //             $user->forceFill([
    //                 'password' => Hash::make($password),
    //                 'remember_token' => Str::random(60),
    //             ])->save();
    //         }
    //     );

    //     return $response == Password::PASSWORD_RESET
    //         ? response()->json(['message' => 'Password reset successfully'])
    //         : response()->json(['message' => 'Unable to reset password'], 400);
    // }
}
