<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeFaceEmbedding;
use App\Models\FaceRegistrationAttempt;
use App\Services\FaceRecognitionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FaceRegistrationController extends Controller
{
    public function index()
    {
        $employees = Employee::latest()->paginate(20);

        return view('face-registration.index', compact('employees'));
    }

    public function show(Employee $employee)
    {
        $employee->load(['faceEmbeddings']);

        return view('face-registration.show', compact('employee'));
    }

    public function store(Request $request, Employee $employee, FaceRecognitionService $faceRecognitionService)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'frames' => ['required', 'array', 'min:1'],
                'frames.*' => ['required', 'string'],
            ]);

            $attempt = FaceRegistrationAttempt::create([
                'employee_id' => $employee->id,
                'status' => 'processing',
                'message' => 'Face registration started.',
                'frames_received' => count($request->frames),
                'accepted_frames' => 0,
                'meta_json' => [
                    'frames_received' => count($request->frames),
                ],
            ]);

            $apiResult = $faceRecognitionService->registerEmployee($employee, $request->frames);

            if (! ($apiResult['success'] ?? false)) {
                $attempt->update([
                    'status' => 'failed',
                    'message' => $apiResult['message'] ?? 'Face registration failed.',
                    'accepted_frames' => 0,
                    'meta_json' => $this->compactApiResult($apiResult),
                ]);

                DB::commit();

                return response()->json([
                    'success' => false,
                    'message' => $apiResult['message'] ?? 'Face registration failed.',
                ], 422);
            }

            $samples = $apiResult['samples'] ?? $apiResult['accepted_samples'] ?? [];

            if (empty($samples)) {
                $attempt->update([
                    'status' => 'failed',
                    'message' => 'No valid face samples returned by API.',
                    'accepted_frames' => 0,
                    'meta_json' => $this->compactApiResult($apiResult),
                ]);

                DB::commit();

                return response()->json([
                    'success' => false,
                    'message' => 'No valid face samples detected.',
                ], 422);
            }

            $savedCount = 0;
            $existingCount = EmployeeFaceEmbedding::where('employee_id', $employee->id)->count();

            foreach ($samples as $sample) {
                $imageBase64 = $sample['image_base64'] ?? null;
                $embedding = $sample['embedding'] ?? null;

                if (empty($imageBase64) || empty($embedding) || ! is_array($embedding)) {
                    continue;
                }

                $imagePath = $this->storeBase64Image(
                    $imageBase64,
                    'face-registrations/'.$employee->id
                );

                EmployeeFaceEmbedding::create([
                    'employee_id' => $employee->id,
                    'image_path' => $imagePath,
                    'is_primary' => ($existingCount === 0 && $savedCount === 0),
                    'model_name' => $sample['model_name'] ?? 'insightface',
                    'model_version' => $sample['model_version'] ?? null,
                    'det_score' => $sample['det_score'] ?? null,
                    'quality_score' => $sample['quality_score'] ?? null,
                    'yaw' => $sample['yaw'] ?? null,
                    'pitch' => $sample['pitch'] ?? null,
                    'roll' => $sample['roll'] ?? null,
                    'landmarks_json' => $sample['landmarks'] ?? null,
                    'embedding_json' => $embedding,
                    'captured_at' => now(),
                ]);

                $savedCount++;
            }

            if ($savedCount === 0) {
                $attempt->update([
                    'status' => 'failed',
                    'message' => 'No samples were saved.',
                    'accepted_frames' => 0,
                    'meta_json' => $this->compactApiResult($apiResult),
                ]);

                DB::commit();

                return response()->json([
                    'success' => false,
                    'message' => 'No samples were saved.',
                ], 422);
            }

            $employee->update([
                'face_registered_at' => now(),
                'face_samples_count' => EmployeeFaceEmbedding::where('employee_id', $employee->id)->count(),
            ]);

            $attempt->update([
                'status' => 'success',
                'message' => 'Face registration completed successfully.',
                'accepted_frames' => $savedCount,
                'meta_json' => $this->compactApiResult($apiResult),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Face registered successfully.',
                'saved_count' => $savedCount,
                'redirect' => route('face-registration.show', $employee),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Face registration failed', [
                'employee_id' => $employee->id ?? null,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Employee $employee, EmployeeFaceEmbedding $sample)
    {
        abort_unless($sample->employee_id === $employee->id, 404);

        DB::transaction(function () use ($employee, $sample) {
            $employee->faceEmbeddings()->update(['is_primary' => false]);
            $sample->update(['is_primary' => true]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Primary sample updated.',
        ]);
    }

    public function destroy(Employee $employee, EmployeeFaceEmbedding $sample)
    {
        abort_unless($sample->employee_id === $employee->id, 404);

        DB::transaction(function () use ($employee, $sample) {
            if ($sample->image_path && Storage::disk('public')->exists($sample->image_path)) {
                Storage::disk('public')->delete($sample->image_path);
            }

            $wasPrimary = $sample->is_primary;
            $sample->delete();

            if ($wasPrimary) {
                $nextPrimary = EmployeeFaceEmbedding::where('employee_id', $employee->id)
                    ->latest('captured_at')
                    ->first();

                if ($nextPrimary) {
                    $nextPrimary->update(['is_primary' => true]);
                }
            }

            $remainingCount = EmployeeFaceEmbedding::where('employee_id', $employee->id)->count();

            $employee->update([
                'face_samples_count' => $remainingCount,
                'face_registered_at' => $remainingCount > 0 ? $employee->face_registered_at : null,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Sample deleted successfully.',
        ]);
    }

    private function storeBase64Image(string $base64Image, string $directory): string
    {
        $extension = 'jpg';

        if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $matches)) {
            $extension = strtolower($matches[1]);
            $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
        }

        $decoded = base64_decode($base64Image);

        if ($decoded === false) {
            throw new \Exception('Invalid base64 image data.');
        }

        $filename = $directory.'/'.uniqid('face_', true).'.'.$extension;

        Storage::disk('public')->put($filename, $decoded);

        return $filename;
    }

    private function compactApiResult(array $apiResult): array
    {
        $rawSamples = $apiResult['samples'] ?? $apiResult['accepted_samples'] ?? [];

        $samples = collect($rawSamples)->map(function ($sample) {
            return [
                'det_score' => $sample['det_score'] ?? null,
                'quality_score' => $sample['quality_score'] ?? null,
                'yaw' => $sample['yaw'] ?? null,
                'pitch' => $sample['pitch'] ?? null,
                'roll' => $sample['roll'] ?? null,
                'model_name' => $sample['model_name'] ?? null,
                'model_version' => $sample['model_version'] ?? null,
                'has_embedding' => ! empty($sample['embedding']),
                'has_image_base64' => ! empty($sample['image_base64']),
            ];
        })->values()->all();

        return [
            'success' => $apiResult['success'] ?? false,
            'message' => $apiResult['message'] ?? null,
            'samples_count' => count($samples),
            'samples' => $samples,
        ];
    }
}
