<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl leading-tight title-gradient"><i class="bi bi-person-vcard me-2"></i>Face Enrollment</h2>
    </x-slot>

    <div class="page-wrap py-8 sm:py-10">
        <div class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
            <div class="panel card-fade-in p-6">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm text-slate-500">Enroll a user by capturing multiple face samples from the laptop camera.</p>
                        <h3 class="mt-1 text-xl font-semibold text-slate-800">{{ $user->name }}</h3>
                        <p class="text-sm text-slate-500">{{ $user->email }}</p>
                    </div>
                    <div class="flex gap-2">
                        <button id="start-enroll-camera" class="btn-secondary"><i class="bi bi-camera-video"></i> Start Camera</button>
                        <button id="capture-sample" class="btn-primary" disabled><i class="bi bi-camera-fill"></i> Capture Sample</button>
                    </div>
                </div>

                <div class="grid gap-4 lg:grid-cols-[1fr_320px]">
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-950 shadow-inner">
                        <video id="enroll-camera" autoplay playsinline muted class="h-[420px] w-full object-cover"></video>
                    </div>
                    <div class="space-y-4">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm font-semibold text-slate-600">Guidelines</p>
                            <ul class="mt-2 space-y-2 text-sm text-slate-600">
                                <li>Keep face centered and still.</li>
                                <li>Capture 5 to 8 samples for best accuracy.</li>
                                <li>Use good lighting, no mask, no heavy shadows.</li>
                            </ul>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <p class="text-sm font-semibold text-slate-600">Status</p>
                            <div id="enroll-status" class="mt-2 text-sm text-slate-600">Camera not started.</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm font-semibold text-slate-600">Samples collected</p>
                            <p id="sample-count" class="mt-1 text-3xl font-bold text-indigo-600">0</p>
                            <p class="text-xs text-slate-500">Minimum 3, recommended 5-8</p>
                        </div>
                    </div>
                </div>

                <canvas id="enroll-canvas" class="hidden"></canvas>
            </div>

            <div class="panel card-fade-in p-6">
                <h3 class="section-title mb-3"><i class="bi bi-shield-check text-indigo-600"></i>Enrollment profile</h3>
                @if($faceProfile)
                    <div class="space-y-2 text-sm text-slate-600">
                        <div><span class="font-semibold text-slate-800">Current samples:</span> {{ $faceProfile->sample_count }}</div>
                        <div><span class="font-semibold text-slate-800">Last enrolled:</span> {{ $faceProfile->last_enrolled_at?->format('d M Y, h:i A') ?? '-' }}</div>
                        <div><span class="font-semibold text-slate-800">Status:</span> Active</div>
                    </div>
                @else
                    <p class="text-sm text-slate-500">No face profile exists yet. Capture samples below to create one.</p>
                @endif

                <form id="save-enrollment-form" class="mt-5 space-y-4">
                    @csrf
                    <input type="hidden" name="samples" id="samples-input">
                    <button id="save-samples" type="submit" class="btn-primary w-full" disabled><i class="bi bi-save"></i> Save Enrollment</button>
                    <a href="{{ route('users.index') }}" class="btn-secondary w-full">Back to Users</a>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
        <script>
            const enrollStatusEl = document.getElementById('enroll-status');
            const sampleCountEl = document.getElementById('sample-count');
            const startEnrollButton = document.getElementById('start-enroll-camera');
            const captureSampleButton = document.getElementById('capture-sample');
            const saveEnrollmentForm = document.getElementById('save-enrollment-form');
            const saveSamplesButton = document.getElementById('save-samples');
            const samplesInput = document.getElementById('samples-input');
            const enrollVideo = document.getElementById('enroll-camera');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const modelUrl = 'https://justadudewhohacks.github.io/face-api.js/models';

            let enrollStream = null;
            let enrollModelsReady = false;
            const samples = [];

            const setEnrollStatus = (message, tone = 'default') => {
                const toneMap = {
                    default: 'text-slate-600',
                    success: 'text-emerald-700',
                    error: 'text-red-700',
                    warning: 'text-amber-700',
                };

                enrollStatusEl.className = `mt-2 text-sm ${toneMap[tone] ?? toneMap.default}`;
                enrollStatusEl.textContent = message;
            };

            const loadEnrollModels = async () => {
                if (enrollModelsReady) {
                    return;
                }

                setEnrollStatus('Loading face recognition models...');
                await Promise.all([
                    faceapi.nets.ssdMobilenetv1.loadFromUri(modelUrl),
                    faceapi.nets.faceLandmark68Net.loadFromUri(modelUrl),
                    faceapi.nets.faceRecognitionNet.loadFromUri(modelUrl),
                ]);
                enrollModelsReady = true;
                setEnrollStatus('Models loaded. Start the camera and collect samples.', 'success');
            };

            const startEnrollCamera = async () => {
                await loadEnrollModels();

                if (enrollStream) {
                    return;
                }

                enrollStream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'user',
                        width: { ideal: 1280 },
                        height: { ideal: 720 },
                    },
                    audio: false,
                });

                enrollVideo.srcObject = enrollStream;
                captureSampleButton.disabled = false;
                setEnrollStatus('Camera ready. Capture 5 or more samples for better accuracy.', 'success');
            };

            const captureSample = async () => {
                try {
                    captureSampleButton.disabled = true;
                    setEnrollStatus('Detecting face...', 'warning');

                    const detection = await faceapi.detectSingleFace(enrollVideo, new faceapi.SsdMobilenetv1Options({ minConfidence: 0.65 }))
                        .withFaceLandmarks()
                        .withFaceDescriptor();

                    if (!detection) {
                        throw new Error('No face detected. Adjust lighting and try again.');
                    }

                    samples.push(Array.from(detection.descriptor));
                    sampleCountEl.textContent = samples.length.toString();
                    samplesInput.value = JSON.stringify(samples);
                    saveSamplesButton.disabled = samples.length < 3;

                    setEnrollStatus(`Sample ${samples.length} captured successfully.`, 'success');
                } catch (error) {
                    setEnrollStatus(error.message || 'Failed to capture sample.', 'error');
                } finally {
                    captureSampleButton.disabled = false;
                }
            };

            saveEnrollmentForm.addEventListener('submit', async (event) => {
                event.preventDefault();

                if (samples.length < 3) {
                    setEnrollStatus('Capture at least 3 samples before saving.', 'warning');
                    return;
                }

                try {
                    saveSamplesButton.disabled = true;
                    setEnrollStatus('Saving face profile...', 'warning');

                    const response = await fetch('{{ route('attendance.enroll.store', $user) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({ samples }),
                    });

                    if (!response.ok) {
                        const payload = await response.json();
                        throw new Error(payload.message || 'Failed to save enrollment.');
                    }

                    window.location.href = '{{ route('users.index') }}';
                } catch (error) {
                    setEnrollStatus(error.message || 'Unexpected error while saving.', 'error');
                } finally {
                    saveSamplesButton.disabled = samples.length < 3;
                }
            });

            startEnrollButton.addEventListener('click', startEnrollCamera);
            captureSampleButton.addEventListener('click', captureSample);
        </script>
    @endpush
</x-app-layout>