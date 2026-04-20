<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Services\FaceRecognitionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (! $user || ! $user->employee) {
            abort(404, 'Employee record not found for this user.');
        }

        $employee = $user->employee;

        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('attendance_date', today())
            ->first();

        return view('employees.dashboard', compact('employee', 'todayAttendance'));
    }

    public function timeIn(Request $request, FaceRecognitionService $faceRecognitionService)
    {
        return $this->handleAttendance($request, $faceRecognitionService, 'time_in');
    }

    public function timeOut(Request $request, FaceRecognitionService $faceRecognitionService)
    {
        return $this->handleAttendance($request, $faceRecognitionService, 'time_out');
    }

    private function handleAttendance(Request $request, FaceRecognitionService $faceRecognitionService, string $type)
    {
        $request->validate([
            'frames' => ['required', 'array', 'min:5'],
            'frames.*' => ['required', 'string'],
            'liveness_passed' => ['required', 'boolean'],
            'quality_score' => ['nullable', 'numeric'],
        ]);

        $user = Auth::user();

        if (! $user || ! $user->employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee record not found.',
            ], 404);
        }

        $employee = $user->employee;

        if (! $employee->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Employee is inactive.',
            ], 403);
        }

        $registeredEmbeddings = $employee->faceEmbeddings()
            ->pluck('embedding_json')
            ->filter()
            ->values()
            ->all();

        if (empty($registeredEmbeddings)) {
            return response()->json([
                'success' => false,
                'message' => 'No registered face samples found for this employee.',
            ], 422);
        }

        if (! $request->boolean('liveness_passed')) {
            return response()->json([
                'success' => false,
                'message' => 'Liveness check failed.',
            ], 422);
        }

        $verification = $faceRecognitionService->verifyEmployeeFace(
            employeeId: $employee->id,
            frames: $request->frames,
            registeredEmbeddings: $registeredEmbeddings,
            threshold: 0.85,
            minMatchedFrames: 4
        );

        if (! ($verification['success'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => $verification['message'] ?? 'Verification failed.',
                'confidence' => $verification['confidence'] ?? null,
                'matched_frames' => $verification['matched_frames'] ?? 0,
                'quality_score' => $request->input('quality_score'),
            ], 422);
        }

        $now = now();
        $today = $now->toDateString();

        DB::beginTransaction();

        try {
            $attendance = Attendance::firstOrCreate(
                [
                    'employee_id' => $employee->id,
                    'attendance_date' => $today,
                ],
                [
                    'time_in' => null,
                    'time_out' => null,
                ]
            );

            if ($type === 'time_in') {
                if ($attendance->time_in) {
                    DB::rollBack();

                    return response()->json([
                        'success' => false,
                        'message' => 'Time In already recorded for today.',
                        'attendance' => $this->formatAttendance($attendance),
                        'confidence' => $verification['confidence'] ?? null,
                        'matched_frames' => $verification['matched_frames'] ?? 0,
                    ], 422);
                }

                $attendance->time_in = $now;
            }

            if ($type === 'time_out') {
                if (! $attendance->time_in) {
                    DB::rollBack();

                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot Time Out before Time In.',
                        'attendance' => $this->formatAttendance($attendance),
                        'confidence' => $verification['confidence'] ?? null,
                        'matched_frames' => $verification['matched_frames'] ?? 0,
                    ], 422);
                }

                if ($attendance->time_out) {
                    DB::rollBack();

                    return response()->json([
                        'success' => false,
                        'message' => 'Time Out already recorded for today.',
                        'attendance' => $this->formatAttendance($attendance),
                        'confidence' => $verification['confidence'] ?? null,
                        'matched_frames' => $verification['matched_frames'] ?? 0,
                    ], 422);
                }

                $attendance->time_out = $now;
            }

            $attendance->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $type === 'time_in'
                    ? 'Time In recorded successfully.'
                    : 'Time Out recorded successfully.',
                'attendance' => $this->formatAttendance($attendance),
                'confidence' => $verification['confidence'] ?? null,
                'matched_frames' => $verification['matched_frames'] ?? 0,
                'quality_score' => $verification['quality_score'] ?? $request->input('quality_score'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to save attendance.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function formatAttendance(Attendance $attendance): array
    {
        return [
            'date' => Carbon::parse($attendance->attendance_date)->format('F d, Y'),
            'time_in' => $attendance->time_in ? Carbon::parse($attendance->time_in)->format('h:i:s A') : 'Not yet',
            'time_out' => $attendance->time_out ? Carbon::parse($attendance->time_out)->format('h:i:s A') : 'Not yet',
        ];
    }
}
