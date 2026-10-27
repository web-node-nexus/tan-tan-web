<?php

namespace App\Http\Resources\Api\V1;

use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin JobApplication */
class JobApplicationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'note' => $this->note,
            'experience' => $this->experience,
            'education' => $this->education,
            'can_join' => $this->can_join,
            'expected_salary' => $this->expected_salary,
            'interview_on' => $this->interview_on?->toDateString(),
            'interview_slot' => $this->interview_slot,
            'interview_place' => $this->interview_place,
            'interview_reply' => $this->interview_reply,
            'created_at' => $this->created_at?->toIso8601String(),
            'applicant' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ]),
            'listing' => $this->whenLoaded('listing', fn () => [
                'id' => $this->listing->id,
                'title' => $this->listing->title,
            ]),
        ];
    }
}
