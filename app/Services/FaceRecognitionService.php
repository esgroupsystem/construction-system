<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FaceRecognitionService
{
    public function registerEmployee($employee, array $frames): array
    {
        $response = Http::timeout(60)
            ->acceptJson()
            ->post(config('services.face_api.base_url').'/register-face', [
                'employee_id' => $employee->id,
                'employee_no' => $employee->employee_no,
                'frames' => $frames,
            ]);

        if (! $response->successful()) {
            return [
                'success' => false,
                'message' => $response->json('detail')
                    ?? $response->json('message')
                    ?? 'Face API request failed.',
            ];
        }

        return $response->json();
    }

    public function verifyEmployeeFace(
        int $employeeId,
        array $frames,
        array $registeredEmbeddings,
        float $threshold = 0.58,
        int $minMatchedFrames = 4
    ): array {
        $response = Http::timeout(60)
            ->acceptJson()
            ->post(config('services.face_api.base_url').'/verify-employee-face', [
                'employee_id' => $employeeId,
                'frames' => $frames,
                'registered_embeddings' => $registeredEmbeddings,
                'threshold' => $threshold,
                'min_matched_frames' => $minMatchedFrames,
            ]);

        if (! $response->successful()) {
            return [
                'success' => false,
                'message' => $response->json('detail')
                    ?? $response->json('message')
                    ?? 'Face verification request failed.',
            ];
        }

        return $response->json();
    }
}
