<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginApiUserRequest;
use App\Http\Requests\StoreUserApiRequest;
use App\Models\User;
use App\Traits\ApiResponses;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    use ApiResponses;

    /**
     * Handle an incoming registration request.
     * Registration new user for api.
     *
     * @param StoreUserApiRequest $request
     * @return JsonResponse
     */

    public function registerUserApi(StoreUserApiRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        $validatedData['password'] = bcrypt($request->password);

        Auth::login($user = User::create($validatedData));

        $accessToken = $user->createToken(
            'authToken',
            ['*'],
            now()->addMonth()
        )->accessToken;

        event(new Registered($user));

        return $this->success(
            'Registered',
            [
                'user' => $user,
                'access_token' => $accessToken,
                'token_type' => 'Bearer',
            ],
            201
        );
    }

    /**
     * Login user in api.
     *
     * @param LoginApiUserRequest $request
     * @return JsonResponse
     */

    public function loginUserApi(LoginApiUserRequest $request): JsonResponse
    {
        $loginData = $request->validated();

        if (!Auth::attempt($loginData)) {
            return $this->error('Invalid Credentials', 401);
        }
        $user = User::firstWhere('email', $request->email);

        $accessToken = $user->createToken(
            'authToken',
            ['*'],
            now()->addMonth()
        )->plainTextToken;

        return $this->ok(
            'Authenticated',
            [
                'access_token' => $accessToken,
                'token_type' => 'Bearer',
            ]
        );
    }

    /**
     * Logout user from api.
     *
     * @param Request $request
     * @return JsonResponse
     */

    public function logoutUserApi(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->ok('');
    }
}
