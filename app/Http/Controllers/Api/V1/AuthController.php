<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\SendOtpRequest;
use App\Http\Requests\Api\V1\VerifyOtpRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Services\Auth\PhoneAuthService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use RuntimeException;

class AuthController extends Controller
{
    public function __construct(
        private readonly PhoneAuthService $auth,
    ) {}

    public function sendOtp(SendOtpRequest $request): JsonResponse
    {
        try {
            $data = $this->auth->sendLoginOtp((string) $request->validated('phone'));
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 422, [
                'phone' => [$e->getMessage()],
            ]);
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 502);
        }

        return ApiResponse::success([
            'phone' => $data['phone'],
            'masked' => $data['masked'],
            'expires_in' => $data['expires_in'],
        ], __('api.auth.otp_sent'));
    }

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        try {
            $result = $this->auth->verifyLoginOtp(
                (string) $request->validated('phone'),
                (string) $request->validated('otp'),
                $request->validated('name'),
            );
        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 422, [
                'otp' => [$e->getMessage()],
            ]);
        } catch (RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 401);
        }

        return ApiResponse::success([
            'token' => $result['token'],
            'token_type' => $result['token_type'],
            'is_new_user' => $result['is_new_user'],
            'user' => (new UserResource($result['user']))->resolve(),
        ], __('api.auth.login_ok'));
    }

    public function me(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $user->forceFill(['last_active_at' => now()])->save();

        return ApiResponse::success(
            (new UserResource($user->fresh()))->resolve(),
            __('api.auth.me_ok'),
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return ApiResponse::success(null, __('api.auth.logout_ok'));
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'min:2', 'max:80'],
            'bio' => ['nullable', 'string', 'max:500'],
            'locale' => ['sometimes', 'in:en,hi'],
            'theme' => ['sometimes', 'in:light,dark,system'],
            'show_phone' => ['sometimes', 'boolean'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();
        $user->fill($data)->save();

        return ApiResponse::success(
            (new UserResource($user->fresh()))->resolve(),
            __('api.auth.profile_updated'),
        );
    }
}
