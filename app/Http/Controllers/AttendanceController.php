<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\FaceProfile;
use App\Models\User;
use App\Services\FaceRecognitionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(private readonly FaceRecognitionService $faceRecognitionService)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('attendance_date', today())
            ->first();

        $faceProfile = FaceProfile::where('user_id', $user->id)->first();

        $recentAttendances = collect();
        $enrolledProfiles = collect();

        if ($user->can('users.manage')) {
            $recentAttendances = Attendance::with('user')
                ->latest('attendance_date')
                ->latest('check_in_at')
                ->limit(12)
                ->get();

            $enrolledProfiles = FaceProfile::with('user')
                ->latest('last_enrolled_at')
                ->limit(12)
                ->get();
        }

        return view('attendance.index', compact('todayAttendance', 'faceProfile', 'recentAttendances', 'enrolledProfiles'));
    }

    public function enroll(User $user): View
    {
        $user->loadMissing('roles');
        $faceProfile = FaceProfile::where('user_id', $user->id)->first();

        return view('attendance.enroll', compact('user', 'faceProfile'));
    }

    public function storeEnrollment(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'samples' => ['required', 'array', 'min:3', 'max:10'],
            'samples.*' => ['required', 'array', 'size:128'],
            'samples.*.*' => ['required', 'numeric'],
        ]);

        $samples = collect($validated['samples'])
            ->map(fn (array $sample): array => array_map('floatval', $sample))
            ->values()
            ->all();

        $existing = FaceProfile::where('user_id', $user->id)->first();

        if ($existing) {
            $samples = array_slice(array_merge($existing->descriptors ?? [], $samples), -10);
        }

        FaceProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'descriptors' => $samples,
                'sample_count' => count($samples),
                'last_enrolled_at' => now(),
                'is_active' => true,
            ]
        );

        return redirect()->route('users.index')->with('success', 'Face profile saved successfully.');
    }

    public function scan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'descriptor' => ['required', 'array', 'size:128'],
            'descriptor.*' => ['required', 'numeric'],
            'source_device' => ['nullable', 'string', 'max:255'],
        ]);

        $descriptor = array_map('floatval', $validated['descriptor']);
        $match = $this->faceRecognitionService->match($descriptor);

        if ($match === null) {
            return response()->json([
                'success' => false,
                'message' => 'Face not recognized. Please try again with better lighting and keep your face straight.',
            ], 422);
        }

        $attendance = $this->markAttendance($match['user']->id, $match['distance'], $validated['source_device'] ?? null);

        return response()->json([
            'success' => true,
            'message' => $attendance['message'],
            'status' => $attendance['status'],
            'user' => [
                'id' => $match['user']->id,
                'name' => $match['user']->name,
                'distance' => round($match['distance'], 4),
            ],
            'attendance' => [
                'date' => $attendance['attendance']->attendance_date->format('Y-m-d'),
                'check_in_at' => optional($attendance['attendance']->check_in_at)->format('h:i A'),
                'check_out_at' => optional($attendance['attendance']->check_out_at)->format('h:i A'),
            ],
        ]);
    }

    private function markAttendance(int $userId, float $distance, ?string $sourceDevice = null): array
    {
        return DB::transaction(function () use ($userId, $distance, $sourceDevice): array {
            $attendance = Attendance::firstOrNew([
                'user_id' => $userId,
                'attendance_date' => today()->toDateString(),
            ]);

            if (! $attendance->exists) {
                $attendance->fill([
                    'check_in_at' => now(),
                    'check_in_method' => 'face',
                    'check_in_distance' => $distance,
                    'source_device' => $sourceDevice,
                ]);
                $attendance->save();

                return [
                    'attendance' => $attendance,
                    'status' => 'checked_in',
                    'message' => 'Check-in marked successfully.',
                ];
            }

            if ($attendance->check_out_at === null) {
                $attendance->update([
                    'check_out_at' => now(),
                    'check_out_method' => 'face',
                    'check_out_distance' => $distance,
                    'source_device' => $sourceDevice,
                ]);

                return [
                    'attendance' => $attendance->fresh(),
                    'status' => 'checked_out',
                    'message' => 'Check-out marked successfully.',
                ];
            }

            return [
                'attendance' => $attendance,
                'status' => 'completed',
                'message' => 'Attendance already completed for today.',
            ];
        });
    }
}