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

    public function logs(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $attendanceLogs = Attendance::with('employee')
            ->when($search, function ($query) use ($search) {
                $query->whereHas('employee', function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('employee_no', 'like', "%{$search}%");
                });
            })
            ->when($dateFrom, function ($query) use ($dateFrom) {
                $query->whereDate('attendance_date', '>=', $dateFrom);
            })
            ->when($dateTo, function ($query) use ($dateTo) {
                $query->whereDate('attendance_date', '<=', $dateTo);
            })
            ->latest('attendance_date')
            ->latest('time_in')
            ->paginate(15)
            ->withQueryString();

        return view('attendance-logs.index', compact('attendanceLogs', 'search', 'dateFrom', 'dateTo'));
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
            threshold: 0.70,
            minMatchedFrames: 4
        );

        if (! ($verification['success'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => $verification['message'] ?? 'Verification failed.',
                'confidence' => $verification['confidence'] ?? null,
                'matched_frames' => $verification['matched_frames'] ?? 0,
                'quality_score' => $verification['quality_score'] ?? $request->input('quality_score'),
            ], 422);
        }

        $now = now();
        $today = $now->toDateString();
        $confidence = isset($verification['confidence']) ? (float) $verification['confidence'] : null;

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
                    'time_in_method' => null,
                    'time_out_method' => null,
                    'time_in_confidence' => null,
                    'time_out_confidence' => null,
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
                $attendance->time_in_method = 'face';
                $attendance->time_in_confidence = $confidence;
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
                $attendance->time_out_method = 'face';
                $attendance->time_out_confidence = $confidence;
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
                'method' => 'face',
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
            'time_in_method' => $attendance->time_in_method ?? '-',
            'time_out_method' => $attendance->time_out_method ?? '-',
            'time_in_confidence' => ! is_null($attendance->time_in_confidence)
                ? number_format((float) $attendance->time_in_confidence * 100, 2).'%'
                : '-',
            'time_out_confidence' => ! is_null($attendance->time_out_confidence)
                ? number_format((float) $attendance->time_out_confidence * 100, 2).'%'
                : '-',
        ];
    }
}
