<?php

namespace App\Http\Controllers;

use App\Models\EmployeeFaceSample;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FaceRecognitionController extends Controller
{
    public function index()
    {
        return view('face-recognition.index');
    }

    /*---------------------------------------------------
     | IDENTIFY (NO PYTHON - PURE LARAVEL)
    ----------------------------------------------------*/
    public function identify(Request $request)
    {
        $request->validate([
            'embedding' => ['required', 'array'],
        ]);

        try {
            // ✅ Ensure numeric input
            $inputEmbedding = array_map('floatval', $request->embedding);

            $samples = EmployeeFaceSample::with('employee')->get();

            $bestMatch = null;
            $highestScore = 0;

            foreach ($samples as $sample) {

                if (! $sample->embedding || count($sample->embedding) < 10) {
                    continue;
                }

                // ✅ FIX HERE (NO json_decode)
                $dbEmbedding = array_map('floatval', $sample->embedding);

                $score = $this->similarity($inputEmbedding, $dbEmbedding);

                if ($score > $highestScore) {
                    $highestScore = $score;
                    $bestMatch = $sample;
                }
            }

            // 🎯 Threshold (adjust later)
            if ($highestScore > 0.75 && $bestMatch) {

                $employee = $bestMatch->employee;

                return response()->json([
                    'success' => true,
                    'matched' => true,
                    'employee' => [
                        'id' => $employee->id,
                        'employee_no' => $employee->employee_no,
                        'full_name' => $employee->full_name,
                        'department' => $employee->department,
                        'position' => $employee->position,
                        'photo_url' => $employee->photo_path
                            ? asset('storage/'.$employee->photo_path)
                            : null,
                    ],
                    'confidence' => round($highestScore, 4),
                ]);
            }

            return response()->json([
                'success' => true,
                'matched' => false,
                'message' => 'No matching employee found.',
            ]);

        } catch (\Throwable $e) {
            Log::error('Face recognition failed', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Recognition failed.',
            ], 500);
        }
    }

    /*---------------------------------------------------
     | SIMILARITY (CORE MATCHING LOGIC)
    ----------------------------------------------------*/
    private function similarity($a, $b)
    {
        $dot = 0;
        $normA = 0;
        $normB = 0;

        foreach ($a as $i => $val) {
            if (! isset($b[$i])) {
                continue;
            }

            $dot += $val * $b[$i];
            $normA += $val * $val;
            $normB += $b[$i] * $b[$i];
        }

        if ($normA == 0 || $normB == 0) {
            return 0;
        }

        return $dot / (sqrt($normA) * sqrt($normB));
    }
}
