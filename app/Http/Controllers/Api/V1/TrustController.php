<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\KycSubmission;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TrustController extends Controller
{
    public function showKyc(Request $request): JsonResponse
    {
        $submission = KycSubmission::query()->where('user_id', $request->user()->id)->first();

        return ApiResponse::success($this->payload($request->user()->kyc_status, $submission), __('api.trust.kyc_ok'));
    }

    public function storeKyc(Request $request): JsonResponse
    {
        $data = $request->validate([
            'legal_name' => ['required', 'string', 'min:3', 'max:80'],
            'document_type' => ['required', Rule::in(['aadhaar', 'pan', 'dl'])],
            'document_number' => ['required', 'string', 'max:20'],
            'document' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'selfie' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ]);

        $number = strtoupper(preg_replace('/\s+/', '', $data['document_number']) ?? '');
        $this->assertDocument($data['document_type'], $number);
        $hash = hash('sha256', $data['document_type'].':'.$number);

        $taken = KycSubmission::query()
            ->where('document_hash', $hash)
            ->where('user_id', '!=', $request->user()->id)
            ->exists();
        if ($taken) {
            throw ValidationException::withMessages([
                'document_number' => __('api.trust.document_used'),
            ]);
        }

        $userId = $request->user()->id;
        $submission = KycSubmission::query()->updateOrCreate(
            ['user_id' => $userId],
            [
                'legal_name' => $data['legal_name'],
                'document_type' => $data['document_type'],
                'document_last4' => substr($number, -4),
                'document_hash' => $hash,
                'document_path' => $request->file('document')->store('kyc/'.$userId, 'public'),
                'selfie_path' => $request->file('selfie')->store('kyc/'.$userId, 'public'),
                'status' => 'pending',
                'note' => null,
                'reviewed_at' => null,
            ],
        );
        $request->user()->forceFill([
            'kyc_status' => 'pending',
            'is_verified' => false,
        ])->save();

        return ApiResponse::success($this->payload('pending', $submission), __('api.trust.kyc_submitted'), 201);
    }

    public function updateBusiness(Request $request): JsonResponse
    {
        $gstin = strtoupper(preg_replace('/\s+/', '', (string) $request->input('gstin', '')) ?? '');
        $request->merge(['gstin' => $gstin === '' ? null : $gstin]);

        $data = $request->validate([
            'account_type' => ['required', Rule::in(['personal', 'business'])],
            'business_name' => ['nullable', 'required_if:account_type,business', 'string', 'min:2', 'max:80'],
            'gstin' => ['nullable', 'string', 'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/'],
        ]);

        $business = $data['account_type'] === 'business';
        $request->user()->forceFill([
            'account_type' => $data['account_type'],
            'business_name' => $business ? $data['business_name'] : null,
            'gstin' => $business && filled($data['gstin'] ?? null) ? strtoupper($data['gstin']) : null,
        ])->save();

        return ApiResponse::success(
            (new \App\Http\Resources\Api\V1\UserResource($request->user()->fresh()))->resolve(),
            __('api.trust.business_saved'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(?string $status, ?KycSubmission $submission): array
    {
        return [
            'kyc_status' => $status ?: 'none',
            'legal_name' => $submission?->legal_name,
            'document_type' => $submission?->document_type,
            'document_last4' => $submission?->document_last4,
            'note' => $submission?->note,
        ];
    }

    private function assertDocument(string $type, string $number): void
    {
        $ok = match ($type) {
            'aadhaar' => (bool) preg_match('/^[2-9]\d{11}$/', $number),
            'pan' => (bool) preg_match('/^[A-Z]{5}\d{4}[A-Z]$/', $number),
            default => (bool) preg_match('/^[A-Z0-9]{8,16}$/', $number),
        };
        if (! $ok) {
            throw ValidationException::withMessages([
                'document_number' => __('api.trust.document_invalid'),
            ]);
        }
    }
}
