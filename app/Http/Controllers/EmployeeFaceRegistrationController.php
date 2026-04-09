<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeFaceSample;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmployeeFaceRegistrationController extends Controller
{
    public function index()
    {
        return view('face-registration.list', [
            'employees' => Employee::latest()->get(),
        ]);
    }

    public function show(Employee $employee)
    {
        $employee->load('faceSamples');

        return view('face-registration.index', compact('employee'));
    }

    public function store(Request $request, Employee $employee): JsonResponse
    {
        try {
            $validated = $request->validate([
                'samples' => ['required', 'array', 'min:1', 'max:5'],
                'samples.*.image' => ['required', 'string'],
                'samples.*.embedding' => ['required', 'array', 'min:100'],
            ]);

            $saved = DB::transaction(function () use ($validated, $employee) {
                $records = [];

                foreach ($validated['samples'] as $index => $sample) {

                    $embedding = array_map('floatval', $sample['embedding']);

                    if (count($embedding) < 100) {
                        abort(422, 'Invalid embedding');
                    }

                    $imagePath = $this->storeBase64Image(
                        $sample['image'],
                        "employees/faces/{$employee->id}"
                    );

                    $records[] = EmployeeFaceSample::create([
                        'employee_id' => $employee->id,
                        'image_path' => $imagePath,
                        'embedding' => $embedding,
                        'is_primary' => $index === 0,
                        'captured_at' => now(),
                    ]);
                }

                $employee->update([
                    'face_registered_at' => now(),
                ]);

                return $records;
            });

            return response()->json([
                'success' => true,
                'saved_count' => count($saved),
            ]);

        } catch (\Throwable $e) {

            Log::error('Face registration failed', [
                'employee_id' => $employee->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Employee $employee, EmployeeFaceSample $sample): JsonResponse
    {
        abort_unless($sample->employee_id === $employee->id, 404);

        DB::transaction(function () use ($employee, $sample) {
            $employee->faceSamples()->update(['is_primary' => false]);

            $sample->update(['is_primary' => true]);

            $employee->update([
                'photo_path' => $sample->image_path,
                'face_registered_at' => $employee->face_registered_at ?? now(),
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Primary sample updated.',
        ]);
    }

    public function destroy(Employee $employee, EmployeeFaceSample $sample): JsonResponse
    {
        abort_unless($sample->employee_id === $employee->id, 404);

        DB::transaction(function () use ($sample, $employee) {

            if ($sample->image_path && Storage::disk('public')->exists($sample->image_path)) {
                Storage::disk('public')->delete($sample->image_path);
            }

            $wasPrimary = $sample->is_primary;
            $sample->delete();

            if ($wasPrimary) {
                $next = $employee->faceSamples()->latest('captured_at')->first();

                $employee->update([
                    'photo_path' => $next?->image_path,
                    'face_registered_at' => $next ? $employee->face_registered_at : null,
                ]);

                if ($next) {
                    $next->update(['is_primary' => true]);
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Sample deleted successfully.',
        ]);
    }

    protected function storeBase64Image(string $dataUrl, string $dir): string
    {
        if (! preg_match('/^data:image\/(\w+);base64,/', $dataUrl, $m)) {
            abort(422, 'Invalid image');
        }

        $ext = strtolower($m[1]);
        if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            abort(422, 'Unsupported image');
        }

        $data = base64_decode(substr($dataUrl, strpos($dataUrl, ',') + 1), true);
        if (! $data) {
            abort(422, 'Invalid base64');
        }

        $filename = Str::uuid().'.'.($ext === 'jpeg' ? 'jpg' : $ext);
        $path = "{$dir}/{$filename}";

        Storage::disk('public')->put($path, $data);

        return $path;
    }
}
