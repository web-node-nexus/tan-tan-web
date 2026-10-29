<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\JobApplicationResource;
use App\Models\JobApplication;
use App\Models\Listing;
use App\Services\Chat\ChatService;
use App\Services\Search\SavedSearchAlertService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JobApplicationController extends Controller
{
    public function __construct(private readonly ChatService $chat) {}

    public function store(Request $request, Listing $listing): JsonResponse
    {
        abort_unless($listing->status === 'active', 404);
        $listing->loadMissing('category');
        abort_unless($listing->category?->kind === 'job', 422);

        if ($listing->user_id === $request->user()->id) {
            return ApiResponse::error(__('api.applications.own_listing'), 422);
        }

        $data = $request->validate([
            'note' => ['required', 'string', 'min:10', 'max:1000'],
            'experience' => ['required', Rule::in(JobApplication::EXPERIENCE)],
            'education' => ['required', Rule::in(JobApplication::EDUCATION)],
            'can_join' => ['required', Rule::in(JobApplication::JOIN)],
            'expected_salary' => ['required', 'integer', 'min:1', 'max:999999'],
        ]);

        $existing = JobApplication::query()
            ->where('listing_id', $listing->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existing) {
            return ApiResponse::error(__('api.applications.already_applied'), 409);
        }

        $application = JobApplication::query()->create([
            'listing_id' => $listing->id,
            'user_id' => $request->user()->id,
            'note' => $data['note'],
            'experience' => $data['experience'],
            'education' => $data['education'],
            'can_join' => $data['can_join'],
            'expected_salary' => $data['expected_salary'],
            'status' => 'applied',
        ]);

        $conversation = $this->chat->start($request->user(), $listing, $data['note']);
        $application->load('user');

        return ApiResponse::success([
            'application' => (new JobApplicationResource($application))->resolve(),
            'conversation_id' => $conversation->id,
        ], __('api.applications.applied'), 201);
    }

    public function mine(Request $request): JsonResponse
    {
        $applications = JobApplication::query()
            ->where('user_id', $request->user()->id)
            ->with(['listing:id,title,slug,status', 'user:id,name'])
            ->latest()
            ->get();

        return ApiResponse::success(
            JobApplicationResource::collection($applications)->resolve(),
            __('api.applications.mine_ok'),
        );
    }

    public function index(Request $request, Listing $listing): JsonResponse
    {
        abort_unless($listing->user_id === $request->user()->id, 403);

        $applications = JobApplication::query()
            ->where('listing_id', $listing->id)
            ->with('user:id,name')
            ->latest()
            ->get();

        return ApiResponse::success(
            JobApplicationResource::collection($applications)->resolve(),
            __('api.applications.list_ok'),
        );
    }

    public function update(Request $request, JobApplication $application): JsonResponse
    {
        $application->load('listing');
        abort_unless($application->listing?->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'status' => ['required', Rule::in(JobApplication::EMPLOYER_STATUSES)],
            'interview_on' => ['required_if:status,interview', 'nullable', 'date', 'after_or_equal:today'],
            'interview_slot' => ['required_if:status,interview', 'nullable', Rule::in(JobApplication::INTERVIEW_SLOTS)],
            'interview_place' => ['required_if:status,interview', 'nullable', 'string', 'min:3', 'max:120'],
        ]);

        $payload = ['status' => $data['status']];
        if ($data['status'] === 'interview') {
            $payload['interview_on'] = $data['interview_on'];
            $payload['interview_slot'] = $data['interview_slot'];
            $payload['interview_place'] = $data['interview_place'];
            $payload['interview_reply'] = null;
        }

        $application->update($payload);
        $application->load(['user:id,name', 'listing']);
        if ($application->listing) {
            app(SavedSearchAlertService::class)->notifyStatus(
                $application->user_id,
                $application->listing,
                'job_status',
                'job:'.$application->id.':'.$application->status,
                'Application update',
                $application->listing->title.' is now '.$application->status.'.',
            );
        }

        return ApiResponse::success(
            (new JobApplicationResource($application))->resolve(),
            __('api.applications.updated'),
        );
    }

    public function respond(Request $request, JobApplication $application): JsonResponse
    {
        abort_unless($application->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'action' => ['required', Rule::in(['confirm', 'decline', 'withdraw'])],
        ]);

        if ($data['action'] === 'withdraw') {
            abort_unless(in_array($application->status, ['applied', 'shortlisted', 'interview'], true), 422);
            $application->update(['status' => 'withdrawn']);
        }

        if ($data['action'] === 'confirm') {
            abort_unless($application->status === 'interview', 422);
            $application->update(['interview_reply' => 'confirmed']);
        }

        if ($data['action'] === 'decline') {
            abort_unless($application->status === 'interview', 422);
            $application->update(['interview_reply' => 'declined', 'status' => 'rejected']);
        }

        $application->load('listing:id,title,slug,status');

        return ApiResponse::success(
            (new JobApplicationResource($application))->resolve(),
            __('api.applications.updated'),
        );
    }
}
