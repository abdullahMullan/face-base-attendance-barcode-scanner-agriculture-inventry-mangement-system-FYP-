<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl leading-tight title-gradient"><i class="bi bi-camera-reels me-2"></i>Face Attendance</h2>
    </x-slot>

    <div class="page-wrap py-8 sm:py-10">
        <div class="grid gap-6 xl:grid-cols-[1.35fr_0.65fr]">
            <div class="panel card-fade-in p-6">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm text-slate-500">Use your laptop camera to recognize a face and mark attendance.</p>
                        <h3 class="mt-1 text-xl font-semibold text-slate-800">Live face scan</h3>
                    </div>
                    <div class="flex gap-2">
                        <button id="start-camera" class="btn-secondary"><i class="bi bi-camera-video"></i> Start Camera</button>
                        <button id="scan-face" class="btn-primary" disabled><i class="bi bi-person-bounding-box"></i> Scan & Mark</button>
                    </div>
                </div>

                <div class="grid gap-4 lg:grid-cols-[1fr_320px]">
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-950 shadow-inner">
                        <video id="camera-feed" autoplay playsinline muted class="h-[420px] w-full object-cover"></video>
                    </div>
                    <div class="space-y-4">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm font-semibold text-slate-600">Current user</p>
                            <p class="mt-1 text-lg font-bold text-slate-900">{{ auth()->user()->name }}</p>
                            <p class="text-sm text-slate-500">{{ auth()->user()->email }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm font-semibold text-slate-600">Face profile</p>
                            @if($faceProfile)
                                <p class="mt-1 text-emerald-700 font-semibold">Enrolled</p>
                                <p class="text-sm text-slate-500">{{ $faceProfile->sample_count }} samples saved</p>
                            @else
                                <p class="mt-1 text-amber-700 font-semibold">Not enrolled yet</p>
                                <p class="text-sm text-slate-500">Ask admin to enroll your face first.</p>
                            @endif
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <p class="text-sm font-semibold text-slate-600">Status</p>
                            <div id="scan-status" class="mt-2 text-sm text-slate-600">Camera not started.</div>
                        </div>
                    </div>
                </div>

                <canvas id="capture-canvas" class="hidden"></canvas>
            </div>

            <div class="space-y-6">
                <div class="panel card-fade-in p-5">
                    <h3 class="section-title mb-3"><i class="bi bi-calendar-check text-indigo-600"></i>Today's attendance</h3>
                    @if($todayAttendance)
                        @php $tz = config('app.timezone'); @endphp
                        <div class="space-y-2 text-sm text-slate-600">
                            <div><span class="font-semibold text-slate-800">Date:</span> {{ $todayAttendance->attendance_date->format('d M Y') }}</div>
                            <div><span class="font-semibold text-slate-800">Check-in:</span> {{ $todayAttendance->check_in_at ? $todayAttendance->check_in_at->setTimezone($tz)->format('h:i A') : '-' }}</div>
                            <div><span class="font-semibold text-slate-800">Check-out:</span> {{ $todayAttendance->check_out_at ? $todayAttendance->check_out_at->setTimezone($tz)->format('h:i A') : '-' }}</div>
                        </div>
                    @else
                        <p class="text-sm text-slate-500">No attendance marked yet for today.</p>
                    @endif
                </div>

                @if(auth()->user()->can('users.manage'))
                    <div class="panel card-fade-in p-5">
                        <h3 class="section-title mb-3"><i class="bi bi-people text-indigo-600"></i>Enrolled faces</h3>
                        <div class="space-y-3 max-h-72 overflow-auto pr-1">
                            @forelse($enrolledProfiles as $profile)
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                    <div class="flex items-center justify-between gap-2">
                                        <div>
                                            <p class="font-semibold text-slate-900">{{ $profile->user?->name ?? 'Deleted user' }}</p>
                                            <p class="text-xs text-slate-500">{{ $profile->sample_count }} samples</p>
                                        </div>
                                        <a class="text-sm font-semibold text-indigo-600" href="{{ route('attendance.enroll', $profile->user) }}">Re-enroll</a>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500">No face profiles enrolled yet.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="panel card-fade-in p-5">
                        <h3 class="section-title mb-3"><i class="bi bi-clock-history text-indigo-600"></i>Recent attendance</h3>
                        @php $tz = config('app.timezone'); @endphp
                        <div class="space-y-3 max-h-80 overflow-auto pr-1 text-sm">
                            @forelse($recentAttendances as $attendance)
                                <div class="rounded-xl border border-slate-200 bg-white p-3">
                                    <div class="flex items-center justify-between gap-2">
                                        <div>
                                            <p class="font-semibold text-slate-900">{{ $attendance->user?->name ?? '-' }}</p>
                                            <p class="text-xs text-slate-500">{{ $attendance->attendance_date->format('d M Y') }}</p>
                                        </div>
                                        <span class="badge-neutral">{{ $attendance->check_out_at ? 'Completed' : 'Open' }}</span>
                                    </div>
                                    <div class="mt-2 text-slate-600">
                                        In: {{ $attendance->check_in_at ? $attendance->check_in_at->setTimezone($tz)->format('h:i A') : '-' }} | Out: {{ $attendance->check_out_at ? $attendance->check_out_at->setTimezone($tz)->format('h:i A') : '-' }}
                                    </div>
                                </div>
                            @empty
                                <p class="text-slate-500">No recent attendance records.</p>
                            @endforelse
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
        <script>
            const statusEl = document.getElementById('scan-status');
            const startButton = document.getElementById('start-camera');
            const scanButton = document.getElementById('scan-face');
            const video = document.getElementById('camera-feed');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const modelUrl = 'https://justadudewhohacks.github.io/face-api.js/models';

            let stream = null;
            let modelsReady = false;

            const setStatus = (message, tone = 'default') => {
                const toneMap = {
                    default: 'text-slate-600',
                    success: 'text-emerald-700',
                    error: 'text-red-700',
                    warning: 'text-amber-700',
                };

                statusEl.className = `mt-2 text-sm ${toneMap[tone] ?? toneMap.default}`;
                statusEl.textContent = message;
            };

            const loadModels = async () => {
                if (modelsReady) {
                    return;
                }

                setStatus('Loading face recognition models...');
                await Promise.all([
                    faceapi.nets.ssdMobilenetv1.loadFromUri(modelUrl),
                    faceapi.nets.faceLandmark68Net.loadFromUri(modelUrl),
                    faceapi.nets.faceRecognitionNet.loadFromUri(modelUrl),
                ]);
                modelsReady = true;
                setStatus('Models loaded. Start the camera to begin.', 'success');
            };

            const startCamera = async () => {
                await loadModels();

                if (stream) {
                    return;
                }

                stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'user',
                        width: { ideal: 1280 },
                        height: { ideal: 720 },
                    },
                    audio: false,
                });

                video.srcObject = stream;
                scanButton.disabled = false;
                setStatus('Camera is live. Keep your face centered and click scan.', 'success');
            };

            const captureDescriptor = async () => {
                if (!stream) {
                    throw new Error('Camera is not active.');
                }

                const detection = await faceapi.detectSingleFace(video, new faceapi.SsdMobilenetv1Options({ minConfidence: 0.65 }))
                    .withFaceLandmarks()
                    .withFaceDescriptor();

                if (!detection) {
                    throw new Error('No face detected. Move closer, improve lighting, and try again.');
                }

                return Array.from(detection.descriptor);
            };

            const markAttendance = async () => {
                try {
                    scanButton.disabled = true;
                    setStatus('Detecting face...', 'warning');

                    const descriptor = await captureDescriptor();

                    setStatus('Matching face and saving attendance...', 'warning');

                    const response = await fetch('{{ route('attendance.scan') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            descriptor,
                            source_device: navigator.userAgent,
                        }),
                    });

                    const payload = await response.json();

                    if (!response.ok) {
                        throw new Error(payload.message || 'Face scan failed.');
                    }

                    setStatus(`${payload.user.name}: ${payload.message} (${payload.status})`, 'success');
                    window.location.reload();
                } catch (error) {
                    setStatus(error.message || 'Unexpected error while scanning.', 'error');
                } finally {
                    scanButton.disabled = false;
                }
            };

            startButton.addEventListener('click', startCamera);
            scanButton.addEventListener('click', markAttendance);
        </script>
    @endpush
</x-app-layout>