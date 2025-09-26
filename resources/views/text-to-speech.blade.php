<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Text to Speech Translator</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #6366f1;
            --secondary-color: #8b5cf6;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --dark-color: #1f2937;
            --light-color: #f8fafc;
        }

        body {
            background: linear-gradient(135deg, var(--light-color) 0%, #e0e7ff 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .main-container {
            min-height: 100vh;
            padding: 2rem 0;
        }

        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 1rem 1rem 0 0 !important;
            padding: 1.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
        }

        .form-control, .form-select {
            border-radius: 0.5rem;
            border: 2px solid #e5e7eb;
            padding: 0.75rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .language-selector {
            background: white;
            border-radius: 0.75rem;
            padding: 1rem;
            margin-bottom: 1rem;
            border: 2px solid #e5e7eb;
        }

        .voice-controls {
            background: #f8fafc;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin: 1rem 0;
        }

        .audio-player {
            background: linear-gradient(135deg, #1f2937, #374151);
            color: white;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin: 1rem 0;
        }

        #audioPlayer {
            width: 100%;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            outline: none;
        }

        #audioPlayer::-webkit-media-controls-panel {
            background-color: rgba(255, 255, 255, 0.1);
        }

        #audioPlayer::-webkit-media-controls-play-button {
            background-color: var(--primary-color);
            border-radius: 50%;
        }

        .history-item {
            background: white;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 0.5rem;
            border-left: 4px solid var(--primary-color);
            transition: transform 0.2s;
        }

        .history-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .loading-spinner {
            display: none;
            text-align: center;
            padding: 2rem;
        }

        .alert {
            border-radius: 0.75rem;
            border: none;
            padding: 1rem 1.5rem;
        }

        .progress {
            height: 8px;
            border-radius: 4px;
            background-color: #e5e7eb;
        }

        .progress-bar {
            border-radius: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }

        .text-count {
            font-size: 0.875rem;
            color: #6b7280;
            text-align: right;
            margin-top: 0.25rem;
        }

        @media (max-width: 768px) {
            .main-container {
                padding: 1rem 0;
            }
            
            .card {
                margin: 0 0.5rem;
            }
        }

        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container-fluid main-container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10 col-xl-8">
                <!-- Header -->
                <div class="text-center mb-4">
                    <h1 class="display-4 fw-bold text-primary mb-2">
                        <i class="bi bi-translate me-3"></i>Text to Speech Translator
                    </h1>
                    <p class="lead text-muted">Translate text into multiple languages and convert to speech</p>
                </div>

                <!-- Main Translation Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-chat-text me-2"></i>Text Translation & Speech Generation</h5>
                    </div>
                    <div class="card-body">
                        <!-- Language Selection -->
                        <div class="row mb-3">
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">From Language</label>
                                <select class="form-select" id="sourceLanguage">
                                    <option value="auto">Auto-detect</option>
                                    @foreach($popularLanguages as $code => $name)
                                        <option value="{{ $code }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end justify-content-center">
                                <button type="button" class="btn btn-outline-primary" id="swapLanguages" title="Swap Languages">
                                    <i class="bi bi-arrow-left-right"></i>
                                </button>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">To Language</label>
                                <select class="form-select" id="targetLanguage" required>
                                    <option value="">Select target language</option>
                                    @foreach($popularLanguages as $code => $name)
                                        <option value="{{ $code }}" {{ $code === 'es' ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Text Input -->
                        <div class="mb-3">
                            <label for="inputText" class="form-label fw-semibold">Enter text to translate</label>
                            <textarea class="form-control" id="inputText" rows="4" 
                                     placeholder="Type or paste your text here..." maxlength="5000"></textarea>
                            <div class="text-count">
                                <span id="charCount">0</span>/5000 characters
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-3">
                            <button type="button" class="btn btn-outline-secondary" id="clearText">
                                <i class="bi bi-x-circle me-1"></i>Clear
                            </button>
                            <button type="button" class="btn btn-primary" id="translateBtn">
                                <i class="bi bi-translate me-1"></i>Translate Text
                            </button>
                        </div>

                        <!-- Translation Result -->
                        <div id="translationResult" class="d-none">
                            <div class="alert alert-success">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="alert-heading mb-2">Translation Result:</h6>
                                        <p class="mb-0" id="translatedText"></p>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-success" id="copyTranslation" title="Copy translation">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Voice Settings Card -->
                <div class="card mb-4" id="voiceCard" style="display: none;">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-mic me-2"></i>Voice Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="useElevenLabs" checked>
                                    <label class="form-check-label fw-semibold text-primary" for="useElevenLabs">
                                        <i class="bi bi-robot me-1"></i>Use Eleven Labs AI Voice (Premium Quality)
                                    </label>
                                    <small class="text-muted d-block">
                                        <strong>Checked:</strong> High-quality AI voices with downloadable files<br>
                                        <strong>Unchecked:</strong> Browser speech synthesis (Web Speech API)
                                    </small>
                                </div>
                            </div>
                        </div>
                        <!-- First Row: Voice Gender and Voice Selection (when Eleven Labs is enabled) -->
                        <div class="row mb-3" id="voiceRow">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Voice Gender</label>
                                <select class="form-select" id="voiceGender">
                                    <option value="female" selected>Female</option>
                                    <option value="male">Male</option>
                                </select>
                            </div>
                            <div class="col-md-9" id="voiceSelectionDiv" style="display: none;">
                                <label class="form-label fw-semibold">Select Voice</label>
                                <select class="form-select" id="voiceSelection">
                                    <option value="">Loading voices...</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Second Row: Speed, Pitch, and Generate Button -->
                        <div class="row" id="controlsRow">
                            <div class="col-md-4" id="speedDiv">
                                <label class="form-label fw-semibold">Speed</label>
                                <div class="d-flex align-items-center">
                                    <input type="range" class="form-range me-2" id="voiceSpeed" 
                                           min="0.25" max="2" step="0.25" value="1">
                                    <span id="speedValue" class="badge bg-primary">1x</span>
                                </div>
                            </div>
                            <div class="col-md-4" id="pitchDiv">
                                <label class="form-label fw-semibold">Pitch</label>
                                <div class="d-flex align-items-center">
                                    <input type="range" class="form-range me-2" id="voicePitch" 
                                           min="-10" max="10" step="1" value="0">
                                    <span id="pitchValue" class="badge bg-primary">0</span>
                                </div>
                            </div>
                            <div class="col-md-4 d-flex align-items-end" id="generateDiv">
                                <button type="button" class="btn btn-success w-100" id="generateSpeechBtn">
                                    <i class="bi bi-play-circle me-1"></i>Generate Speech
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Audio Player Card -->
                <div class="card mb-4" id="audioCard" style="display: none;">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="bi bi-music-note me-2"></i>Audio Player</h5>
                    </div>
                    <div class="card-body bg-dark text-white">
                        <div class="row align-items-center">
                            <div class="col-12 col-md-8 mb-3 mb-md-0">
                                <audio controls id="audioPlayer" preload="metadata" class="w-100">
                                    Your browser does not support the audio element.
                                </audio>
                                <small class="text-light mt-1 d-block" id="audioInfo">Ready to play generated audio</small>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="button" class="btn btn-outline-light btn-sm" id="downloadBtn">
                                        <i class="bi bi-download me-1"></i>Download
                                    </button>
                                    <button type="button" class="btn btn-outline-light btn-sm" id="shareBtn">
                                        <i class="bi bi-share me-1"></i>Share
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- History Card -->
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Translation History</h5>
                            <button type="button" class="btn btn-sm btn-outline-light" id="refreshHistory">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                        <div id="historyContainer">
                            @if($recentTranslations->count() > 0)
                                @foreach($recentTranslations as $translation)
                                <div class="history-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-1">
                                                <span class="badge bg-primary me-2">{{ strtoupper($translation->source_language) }}</span>
                                                <i class="bi bi-arrow-right me-2"></i>
                                                <span class="badge bg-success me-2">{{ strtoupper($translation->target_language) }}</span>
                                                <small class="text-muted">{{ $translation->created_at->diffForHumans() }}</small>
                                            </div>
                                            <div class="mb-1">
                                                <strong>Original:</strong> {{ Str::limit($translation->original_text, 100) }}
                                            </div>
                                            <div class="mb-1">
                                                <strong>Translation:</strong> {{ Str::limit($translation->translated_text, 100) }}
                                            </div>
                                            @if($translation->audioFiles->count() > 0)
                                            <div class="mt-2">
                                                @foreach($translation->audioFiles as $audioFile)
                                                <span class="badge bg-info me-1">
                                                    <i class="bi bi-music-note me-1"></i>{{ ucfirst($audioFile->voice_gender) }} Voice
                                                </span>
                                                @endforeach
                                            </div>
                                            @endif
                                        </div>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-primary replay-btn" 
                                                    data-id="{{ $translation->id }}" title="Replay">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-btn" 
                                                    data-id="{{ $translation->id }}" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            @else
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-clock-history display-4"></i>
                                    <p class="mt-2">No translation history yet. Start by translating some text!</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Modal -->
    <div class="modal fade" id="loadingModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body text-center py-5">
                    <div class="mb-4">
                        <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <h5 class="text-primary fw-bold mb-2" id="loadingTitle">Processing Request</h5>
                    <p class="text-muted mb-0" id="loadingText">Please wait while we process your request...</p>
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            <span id="loadingTip">This may take a few moments</span>
                        </small>
                    </div>
                    <div class="mt-4" id="stopButtonContainer" style="display: none;">
                        <button type="button" class="btn btn-outline-danger btn-sm" id="stopGenerationBtn">
                            <i class="bi bi-stop-circle me-1"></i>Stop Generation
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- JavaScript -->
    <script>
        // Global variables
        let currentTranslationId = null;
        let currentAudioFileId = null;
        let currentSpeechRequest = null; // For tracking ongoing requests
        let currentSpeechController = null; // For AbortController
        
        // CSRF token setup
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // DOM elements
        const inputText = document.getElementById('inputText');
        const sourceLanguage = document.getElementById('sourceLanguage');
        const targetLanguage = document.getElementById('targetLanguage');
        const translateBtn = document.getElementById('translateBtn');
        const generateSpeechBtn = document.getElementById('generateSpeechBtn');
        const translationResult = document.getElementById('translationResult');
        const translatedText = document.getElementById('translatedText');
        const voiceCard = document.getElementById('voiceCard');
        const audioCard = document.getElementById('audioCard');
        const audioPlayer = document.getElementById('audioPlayer');
        const charCount = document.getElementById('charCount');
        const speedValue = document.getElementById('speedValue');
        const pitchValue = document.getElementById('pitchValue');
        const voiceSpeed = document.getElementById('voiceSpeed');
        const voicePitch = document.getElementById('voicePitch');
        const downloadBtn = document.getElementById('downloadBtn');
        const stopGenerationBtn = document.getElementById('stopGenerationBtn');
        const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
        
        // New voice-related elements
        const useElevenLabs = document.getElementById('useElevenLabs');
        const voiceGender = document.getElementById('voiceGender');
        const voiceSelection = document.getElementById('voiceSelection');
        const voiceSelectionDiv = document.getElementById('voiceSelectionDiv');
        const speedDiv = document.getElementById('speedDiv');
        const pitchDiv = document.getElementById('pitchDiv');
        const generateDiv = document.getElementById('generateDiv');

        // Character count
        inputText.addEventListener('input', function() {
            const count = this.value.length;
            charCount.textContent = count;
            
            if (count > 4500) {
                charCount.parentElement.classList.add('text-warning');
            } else {
                charCount.parentElement.classList.remove('text-warning');
            }
        });

        // Voice controls
        voiceSpeed.addEventListener('input', function() {
            speedValue.textContent = this.value + 'x';
        });

        voicePitch.addEventListener('input', function() {
            pitchValue.textContent = this.value;
        });

        // Clear text
        document.getElementById('clearText').addEventListener('click', function() {
            inputText.value = '';
            charCount.textContent = '0';
            translationResult.classList.add('d-none');
            voiceCard.style.display = 'none';
            audioCard.style.display = 'none';
            currentTranslationId = null;
            currentAudioFileId = null;
            
            // Reset audio player
            audioPlayer.src = '';
            const audioInfo = document.getElementById('audioInfo');
            if (audioInfo) {
                audioInfo.textContent = 'Ready to play generated audio';
            }
        });

        // Load voice options based on language and gender
        async function loadVoiceOptions() {
            if (!useElevenLabs.checked) {
                return;
            }
            
            try {
                const language = targetLanguage.value || 'en';
                const gender = voiceGender.value || 'female';
                
                console.log('Loading voices for:', language, gender);
                
                const response = await fetch(`/api/voice-options?language=${language}&gender=${gender}`);
                const result = await response.json();
                
                if (result.success && result.voices) {
                    console.log('Found', result.voices.length, 'voices');
                    voiceSelection.innerHTML = '';
                    result.voices.forEach((voice) => {
                        const option = document.createElement('option');
                        option.value = voice.id;
                        option.textContent = voice.description;
                        voiceSelection.appendChild(option);
                    });
                } else {
                    console.error('Failed to load voices:', result);
                    voiceSelection.innerHTML = '<option value="">No voices available</option>';
                }
            } catch (error) {
                console.error('Error loading voice options:', error);
                voiceSelection.innerHTML = '<option value="">Error loading voices</option>';
            }
        }

        // Handle Eleven Labs checkbox change
        useElevenLabs.addEventListener('change', function() {
            if (this.checked) {
                // Show voice selection for Eleven Labs
                voiceSelectionDiv.style.display = 'block';
                loadVoiceOptions();
            } else {
                // Hide voice selection for basic TTS
                voiceSelectionDiv.style.display = 'none';
            }
        });

        // Handle voice gender change
        voiceGender.addEventListener('change', loadVoiceOptions);

        // Handle target language change
        targetLanguage.addEventListener('change', loadVoiceOptions);

        // Stop generation button
        stopGenerationBtn.addEventListener('click', function() {
            console.log('Stop generation button clicked');
            
            // Cancel Web Speech API if speaking
            if (speechSynthesis.speaking) {
                speechSynthesis.cancel();
                console.log('Web Speech API cancelled');
            }
            
            // Abort ongoing HTTP request if exists
            if (currentSpeechController) {
                currentSpeechController.abort();
                console.log('HTTP request aborted');
                currentSpeechController = null;
            }
            
            // Reset button states
            generateSpeechBtn.innerHTML = '<i class="bi bi-play-circle me-1"></i>Generate Speech';
            generateSpeechBtn.disabled = false;
            generateSpeechBtn.onclick = null;
            
            // Hide loading modal
            hideLoading();
            
            showToast('Speech generation stopped', 'info');
        });

        // Swap languages
        document.getElementById('swapLanguages').addEventListener('click', function() {
            const sourceValue = sourceLanguage.value;
            const targetValue = targetLanguage.value;
            
            if (sourceValue !== 'auto' && targetValue) {
                sourceLanguage.value = targetValue;
                targetLanguage.value = sourceValue;
            }
        });

        // Copy translation
        document.getElementById('copyTranslation').addEventListener('click', function() {
            navigator.clipboard.writeText(translatedText.textContent).then(function() {
                showToast('Translation copied to clipboard!', 'success');
            });
        });

        // Translate text
        translateBtn.addEventListener('click', async function() {
            const text = inputText.value.trim();
            const target = targetLanguage.value;
            const source = sourceLanguage.value;

            if (!text) {
                showToast('Please enter text to translate', 'warning');
                return;
            }

            if (!target) {
                showToast('Please select a target language', 'warning');
                return;
            }

            try {
                showLoading('Translating your text...', 'Translation in Progress', 'Processing your text with advanced AI translation');
                
                const response = await fetch('/api/translate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        text: text,
                        target_language: target,
                        source_language: source
                    })
                });

                const result = await response.json();

                if (result.success) {
                    translatedText.textContent = result.translated_text;
                    translationResult.classList.remove('d-none');
                    translationResult.classList.add('fade-in');
                    voiceCard.style.display = 'block';
                    currentTranslationId = result.translation_id;
                    
                    showToast('Translation completed successfully!', 'success');
                    loadHistory(); // Refresh history
                } else {
                    showToast(result.error || 'Translation failed', 'danger');
                }
            } catch (error) {
                console.error('Translation error:', error);
                showToast('Translation failed. Please try again.', 'danger');
            } finally {
                hideLoading();
            }
        });

        // Generate speech
        generateSpeechBtn.addEventListener('click', async function() {
            // If Web Speech API is currently speaking, this click should stop it
            if (speechSynthesis.speaking) {
                console.log('Stopping current speech synthesis');
                speechSynthesis.cancel();
                generateSpeechBtn.innerHTML = '<i class="bi bi-play-circle me-1"></i>Generate Speech';
                generateSpeechBtn.disabled = false;
                generateSpeechBtn.onclick = null;
                hideLoading();
                showToast('Speech stopped', 'info');
                return;
            }
            
            const text = translatedText.textContent;
            const language = targetLanguage.value;
            const gender = document.getElementById('voiceGender').value;
            const speed = parseFloat(voiceSpeed.value);
            const pitch = parseFloat(voicePitch.value);
            const useElevenLabsChecked = document.getElementById('useElevenLabs').checked;
            const voiceId = useElevenLabsChecked ? voiceSelection.value : null;

            if (!text || !currentTranslationId) {
                showToast('Please translate text first', 'warning');
                return;
            }

            try {
                const isElevenLabs = useElevenLabs.checked;
                const loadingTitle = isElevenLabs ? 'Generating Premium Voice' : 'Generating Speech';
                const loadingTip = isElevenLabs ? 'Creating high-quality AI voice using ElevenLabs' : 'Converting text to speech using Web Speech API';
                
                showLoading('Generating speech...', loadingTitle, loadingTip, true);
                
                // Create AbortController for cancelling requests
                currentSpeechController = new AbortController();
                
                // Disable the button to prevent multiple clicks
                generateSpeechBtn.disabled = true;
                generateSpeechBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Generating...';
                
                const requestBody = {
                    text: text,
                    language: language,
                    gender: gender,
                    speed: speed,
                    pitch: pitch,
                    translation_id: currentTranslationId,
                    use_elevenlabs: useElevenLabsChecked
                };

                // Add voice_id if Eleven Labs is selected and a voice is chosen
                if (useElevenLabsChecked && voiceId) {
                    requestBody.voice_id = voiceId;
                }
                
                const response = await fetch('/api/generate-speech', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(requestBody),
                    signal: currentSpeechController.signal
                });

                if (!response.ok) {
                    // Try to get detailed error information from server
                    let errorDetails = `HTTP error! status: ${response.status}`;
                    try {
                        const errorData = await response.json();
                        console.error('Server error details:', errorData);
                        
                        if (errorData.error) {
                            errorDetails = errorData.error;
                        }
                        if (errorData.details) {
                            console.error('Additional error details:', errorData.details);
                        }
                    } catch (parseError) {
                        console.error('Could not parse error response:', parseError);
                        // Try to get text response instead
                        try {
                            const errorText = await response.text();
                            console.error('Error response text:', errorText);
                            if (errorText) {
                                errorDetails += ` - ${errorText}`;
                            }
                        } catch (textError) {
                            console.error('Could not get error text:', textError);
                        }
                    }
                    throw new Error(errorDetails);
                }

                const result = await response.json();

                if (result.success) {
                    console.log('Speech generation result:', result);
                    console.log('Provider:', result.provider);
                    console.log('Use Eleven Labs:', useElevenLabs);
                    
                    // Always show audio card
                    audioCard.style.display = 'block';
                    
                    if (result.provider === 'elevenlabs' && result.url) {
                        // Eleven Labs - show audio player with generated file
                        console.log('Setting audio source to:', result.url);
                        console.log('Audio file ID:', result.audio_file_id);
                        
                        audioPlayer.src = result.url;
                        currentAudioFileId = result.audio_file_id;
                        
                        // Show the audio player controls
                        audioPlayer.style.display = 'block';
                        
                        // Show download button
                        downloadBtn.style.display = 'inline-block';
                        
                        // Add error handling for audio loading
                        audioPlayer.onerror = function(e) {
                            console.error('Audio loading error:', e);
                            showToast('Failed to load generated audio file', 'warning');
                        };
                        
                        audioPlayer.oncanplaythrough = function() {
                            console.log('Audio can play through');
                        };
                        
                        audioPlayer.onloadeddata = function() {
                            console.log('Audio data loaded');
                        };
                        
                        audioPlayer.onloadedmetadata = function() {
                            console.log('Audio metadata loaded, duration:', audioPlayer.duration);
                            const audioInfo = document.getElementById('audioInfo');
                            if (audioInfo && audioPlayer.duration) {
                                const minutes = Math.floor(audioPlayer.duration / 60);
                                const seconds = Math.floor(audioPlayer.duration % 60);
                                audioInfo.textContent = `Duration: ${minutes}:${seconds.toString().padStart(2, '0')} (Eleven Labs AI)`;
                            }
                        };
                        
                        audioPlayer.onload = function() {
                            console.log('Audio loaded successfully');
                        };
                        
                        // Force reload the audio element
                        audioPlayer.load();
                        
                        // Hide loading dialog and reset button for Eleven Labs
                        hideLoading();
                        generateSpeechBtn.innerHTML = '<i class="bi bi-play-circle me-1"></i>Generate Speech';
                        generateSpeechBtn.disabled = false;
                        
                        // Clean up controller reference
                        currentSpeechController = null;
                        
                        showToast(`Speech generated successfully with Eleven Labs AI!`, 'success');
                    } else if (result.provider === 'webspeech') {
                        // Web Speech API - no file playback, direct speech synthesis
                        console.log('Using Web Speech API for playback');
                        
                        // Hide the HTML5 audio player since we'll use Web Speech API
                        audioPlayer.style.display = 'none';
                        
                        // No downloadable file for Web Speech API
                        currentAudioFileId = null;
                        downloadBtn.style.display = 'none';
                        
                        // Update audio info
                        const audioInfo = document.getElementById('audioInfo');
                        if (audioInfo) {
                            audioInfo.textContent = 'Using Web Speech API - speech will play directly through browser';
                        }
                        
                        // Use Web Speech API for actual playback with parameters from server
                        const speechText = result.text || text;
                        const speechLanguage = result.language || language;
                        const speechGender = result.gender || gender;
                        const speechSpeed = result.speed || speed;
                        const speechPitch = result.pitch || pitch;
                        
                        // Hide loading dialog immediately for Web Speech API
                        hideLoading();
                        generateSpeechBtn.innerHTML = '<i class="bi bi-play-circle me-1"></i>Generate Speech';
                        generateSpeechBtn.disabled = false;
                        
                        // Clean up controller reference
                        currentSpeechController = null;
                        
                        generateWebSpeech(speechText, speechLanguage, speechGender, speechSpeed, speechPitch);
                        showToast(`Speech will be played using Web Speech API!`, 'info');
                    } else {
                        // Fallback case - hide everything
                        console.log('Unknown provider or no speech generation');
                        audioPlayer.style.display = 'none';
                        currentAudioFileId = null;
                        downloadBtn.style.display = 'none';
                        
                        showToast('Speech generation method not supported', 'warning');
                    }
                    
                    loadHistory(); // Refresh history
                } else {
                    throw new Error(result.error || 'Speech generation failed');
                }
            } catch (error) {
                console.error('Speech generation error:', error);
                
                // Check if it's an abort error
                if (error.name === 'AbortError') {
                    console.log('Request was aborted');
                    showToast('Speech generation cancelled', 'info');
                } else {
                    showToast('Speech generation failed. Please try again.', 'danger');
                }
                
                // Ensure loading is hidden and button is reset on error
                hideLoading();
                generateSpeechBtn.innerHTML = '<i class="bi bi-play-circle me-1"></i>Generate Speech';
                generateSpeechBtn.disabled = false;
            } finally {
                // Clean up controller reference
                currentSpeechController = null;
                
                // Reset button state if not already handled by specific providers
                if (generateSpeechBtn.disabled && !generateSpeechBtn.innerHTML.includes('Playing')) {
                    generateSpeechBtn.innerHTML = '<i class="bi bi-play-circle me-1"></i>Generate Speech';
                    generateSpeechBtn.disabled = false;
                }
            }
        });

        // Web Speech API implementation for demo
        function generateWebSpeech(text, language, gender, speed, pitch) {
            console.log('generateWebSpeech called with:', { text: text.substring(0, 50) + '...', language, gender, speed, pitch });
            
            if (!('speechSynthesis' in window)) {
                hideLoading();
                showToast('Speech synthesis not supported in this browser', 'warning');
                return;
            }

            // Cancel any existing speech to prevent conflicts
            speechSynthesis.cancel();
            
            // Small delay to ensure cancellation is complete
            setTimeout(() => {
                startSpeech();
            }, 100);
            
            function startSpeech() {
                // Load voices if not already loaded
                let voices = speechSynthesis.getVoices();
                console.log('Available voices count:', voices.length);
                
                if (voices.length === 0) {
                    console.log('Waiting for voices to load...');
                    speechSynthesis.addEventListener('voiceschanged', () => {
                        console.log('Voices loaded, retrying...');
                        voices = speechSynthesis.getVoices();
                        speakText();
                    }, { once: true });
                    
                    // Fallback timeout in case voiceschanged doesn't fire
                    setTimeout(() => {
                        voices = speechSynthesis.getVoices();
                        if (voices.length > 0) {
                            speakText();
                        } else {
                            console.warn('No voices available after timeout');
                            hideLoading();
                            showToast('No voices available for speech synthesis', 'warning');
                        }
                    }, 2000);
                    return;
                }
                
                speakText();
            }

            function speakText() {
                console.log('Creating speech utterance...');
                const utterance = new SpeechSynthesisUtterance(text);
                
                // Set language code properly
                const langCode = getLanguageCode(language);
                utterance.lang = langCode;
                console.log('Using language code:', langCode);
                
                // Get voices again to ensure scope access
                const availableVoices = speechSynthesis.getVoices();
                
                // Find appropriate voice based on gender and language
                let selectedVoice = null;
                
                console.log('Available voices:', availableVoices.map(v => ({ name: v.name, lang: v.lang })));
                
                // Enhanced voice selection logic
                const langVoices = availableVoices.filter(v => {
                    const voiceLang = v.lang.toLowerCase();
                    return voiceLang.startsWith(language.toLowerCase()) || 
                           voiceLang.startsWith(langCode.toLowerCase());
                });
                
                console.log('Language matching voices:', langVoices.map(v => ({ name: v.name, lang: v.lang })));
                
                if (langVoices.length > 0) {
                    if (gender === 'female') {
                        // Try to find female voices with more patterns
                        selectedVoice = langVoices.find(v => {
                            const voiceName = v.name.toLowerCase();
                            return voiceName.includes('female') || 
                                   voiceName.includes('woman') || 
                                   voiceName.includes('samantha') || 
                                   voiceName.includes('susan') || 
                                   voiceName.includes('victoria') || 
                                   voiceName.includes('alice') || 
                                   voiceName.includes('karen') || 
                                   voiceName.includes('sarah') || 
                                   voiceName.includes('emily') || 
                                   voiceName.includes('zoe') || 
                                   voiceName.includes('allison') ||
                                   voiceName.includes('ava') ||
                                   voiceName.includes('jenny') ||
                                   voiceName.includes('hazel') ||
                                   // Pattern-based detection
                                   /\b(she|her|ms\.?|mrs\.?)\b/i.test(voiceName) ||
                                   // Sometimes female voices have higher numbers or specific patterns
                                   (voiceName.includes('voice') && /[02468]/.test(voiceName)) ||
                                   // Check if it's marked as not male
                                   (!voiceName.includes('male') && !voiceName.includes('man') && 
                                    !voiceName.includes('david') && !voiceName.includes('daniel') && 
                                    !voiceName.includes('thomas') && !voiceName.includes('alex') &&
                                    !voiceName.includes('microsoft mark'));
                        });
                        
                        // If no clearly female voice, prefer first available that's not clearly male
                        if (!selectedVoice) {
                            selectedVoice = langVoices.find(v => {
                                const voiceName = v.name.toLowerCase();
                                return !voiceName.includes('male') && 
                                       !voiceName.includes('man') && 
                                       !voiceName.includes('david') && 
                                       !voiceName.includes('daniel') && 
                                       !voiceName.includes('thomas') && 
                                       !voiceName.includes('alex') &&
                                       !voiceName.includes('microsoft mark');
                            });
                        }
                    } else {
                        // Find male voices
                        selectedVoice = langVoices.find(v => {
                            const voiceName = v.name.toLowerCase();
                            return voiceName.includes('male') || 
                                   voiceName.includes('man') || 
                                   voiceName.includes('daniel') || 
                                   voiceName.includes('david') || 
                                   voiceName.includes('thomas') || 
                                   voiceName.includes('alex') ||
                                   voiceName.includes('microsoft mark') ||
                                   // Pattern-based detection
                                   /\b(he|him|mr\.?)\b/i.test(voiceName);
                        });
                    }
                    
                    // If still no voice selected, use first available for language
                    if (!selectedVoice) {
                        selectedVoice = langVoices[0];
                    }
                }
                
                // Final fallback to any available voice
                if (!selectedVoice && availableVoices.length > 0) {
                    selectedVoice = availableVoices[0];
                }
                
                if (selectedVoice) {
                    utterance.voice = selectedVoice;
                    console.log('Selected voice:', selectedVoice.name, 'for', gender, language);
                } else {
                    console.warn('No suitable voice found, using default');
                }
                
                utterance.rate = Math.max(0.1, Math.min(2.0, speed));
                utterance.pitch = Math.max(0, Math.min(2, (pitch + 10) / 10));
                
                console.log('Speech settings:', { rate: utterance.rate, pitch: utterance.pitch, lang: utterance.lang });
                
                utterance.onstart = function() {
                    console.log('Speech started');
                    generateSpeechBtn.innerHTML = '<i class="bi bi-stop-circle me-1"></i>Stop Speech';
                    generateSpeechBtn.disabled = false; // Enable button so user can stop
                    
                    // Change button click behavior to stop speech
                    generateSpeechBtn.onclick = function() {
                        console.log('Stop button clicked');
                        speechSynthesis.cancel();
                        generateSpeechBtn.innerHTML = '<i class="bi bi-play-circle me-1"></i>Generate Speech';
                        generateSpeechBtn.disabled = false;
                        generateSpeechBtn.onclick = null; // Reset click handler
                        hideLoading();
                        showToast('Speech stopped', 'info');
                    };
                };
                
                utterance.onend = function() {
                    console.log('Speech ended');
                    generateSpeechBtn.innerHTML = '<i class="bi bi-play-circle me-1"></i>Generate Speech';
                    generateSpeechBtn.disabled = false;
                    generateSpeechBtn.onclick = null; // Reset click handler
                    hideLoading();
                };
                
                utterance.onerror = function(event) {
                    console.error('Speech synthesis error:', event);
                    generateSpeechBtn.innerHTML = '<i class="bi bi-play-circle me-1"></i>Generate Speech';
                    generateSpeechBtn.disabled = false;
                    generateSpeechBtn.onclick = null; // Reset click handler
                    hideLoading();
                    showToast('Speech synthesis failed: ' + event.error, 'danger');
                };
                
                utterance.onboundary = function(event) {
                    console.log('Speech boundary:', event.name, event.charIndex);
                };
                
                utterance.onpause = function() {
                    console.log('Speech paused');
                };
                
                utterance.onresume = function() {
                    console.log('Speech resumed');
                };
                
                console.log('Starting speech synthesis...');
                try {
                    speechSynthesis.speak(utterance);
                    
                    // Workaround for Chrome issue where speech might not start
                    setTimeout(() => {
                        if (speechSynthesis.speaking && speechSynthesis.paused) {
                            console.log('Resuming paused speech...');
                            speechSynthesis.resume();
                        } else if (!speechSynthesis.speaking) {
                            // If speech didn't start, reset button
                            console.log('Speech did not start, resetting button');
                            generateSpeechBtn.innerHTML = '<i class="bi bi-play-circle me-1"></i>Generate Speech';
                            generateSpeechBtn.disabled = false;
                            generateSpeechBtn.onclick = null;
                            hideLoading();
                            showToast('Speech did not start. Please try again.', 'warning');
                        }
                    }, 500);
                } catch (error) {
                    console.error('Error starting speech:', error);
                    generateSpeechBtn.innerHTML = '<i class="bi bi-play-circle me-1"></i>Generate Speech';
                    generateSpeechBtn.disabled = false;
                    generateSpeechBtn.onclick = null;
                    hideLoading();
                    showToast('Failed to start speech synthesis', 'danger');
                }
            }
        }

        // Helper function to get proper language code
        function getLanguageCode(lang) {
            const langCodes = {
                'en': 'en-US',
                'es': 'es-ES', 
                'fr': 'fr-FR',
                'de': 'de-DE',
                'it': 'it-IT',
                'pt': 'pt-BR',
                'ru': 'ru-RU',
                'ja': 'ja-JP',
                'ko': 'ko-KR',
                'zh': 'zh-CN'
            };
            return langCodes[lang] || lang + '-' + lang.toUpperCase();
        }

        // Download audio file
        downloadBtn.addEventListener('click', async function() {
            console.log('Download button clicked, currentAudioFileId:', currentAudioFileId);
            
            if (!currentAudioFileId) {
                showToast('No audio file available for download. Try generating speech again.', 'warning');
                return;
            }

            try {
                console.log('Downloading audio file from:', `/api/download-audio/${currentAudioFileId}`);
                const response = await fetch(`/api/download-audio/${currentAudioFileId}`);
                
                console.log('Download response status:', response.status);
                
                if (response.ok) {
                    // Create a download link
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `tts-audio-${Date.now()}.wav`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                    
                    showToast('Audio downloaded successfully!', 'success');
                } else {
                    console.error('Download failed with status:', response.status);
                    const errorText = await response.text();
                    console.error('Error response:', errorText);
                    showToast('Download failed. Please try again.', 'danger');
                }
            } catch (error) {
                console.error('Download error:', error);
                showToast('Download failed. Please try again.', 'danger');
            }
        });

        // Load history
        async function loadHistory() {
            try {
                const response = await fetch('/api/history');
                const result = await response.json();
                
                if (result.success) {
                    updateHistoryDisplay(result.data);
                }
            } catch (error) {
                console.error('Failed to load history:', error);
            }
        }

        // Update history display
        function updateHistoryDisplay(translations) {
            const container = document.getElementById('historyContainer');
            
            if (translations.length === 0) {
                container.innerHTML = `
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-clock-history display-4"></i>
                        <p class="mt-2">No translation history yet. Start by translating some text!</p>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = translations.map(translation => `
                <div class="history-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-1">
                                <span class="badge bg-primary me-2">${translation.source_language.toUpperCase()}</span>
                                <i class="bi bi-arrow-right me-2"></i>
                                <span class="badge bg-success me-2">${translation.target_language.toUpperCase()}</span>
                                <small class="text-muted">${formatDate(translation.created_at)}</small>
                            </div>
                            <div class="mb-1">
                                <strong>Original:</strong> ${truncateText(translation.original_text, 100)}
                            </div>
                            <div class="mb-1">
                                <strong>Translation:</strong> ${truncateText(translation.translated_text, 100)}
                            </div>
                            ${translation.audio_files && translation.audio_files.length > 0 ? 
                                `<div class="mt-2">
                                    ${translation.audio_files.map(audio => 
                                        `<span class="badge bg-info me-1">
                                            <i class="bi bi-music-note me-1"></i>${audio.voice_gender} Voice
                                        </span>`
                                    ).join('')}
                                </div>` : ''
                            }
                        </div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-outline-primary replay-btn" 
                                    data-id="${translation.id}" title="Replay">
                                <i class="bi bi-arrow-repeat"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger delete-btn" 
                                    data-id="${translation.id}" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        // Helper functions
        function showLoading(text = 'Processing...', title = 'Processing Request', tip = 'This may take a few moments', showStopButton = false) {
            // Update loading modal content
            document.getElementById('loadingTitle').textContent = title;
            document.getElementById('loadingText').textContent = text;
            document.getElementById('loadingTip').textContent = tip;
            
            // Show or hide stop button based on parameter
            const stopButtonContainer = document.getElementById('stopButtonContainer');
            if (showStopButton) {
                stopButtonContainer.style.display = 'block';
            } else {
                stopButtonContainer.style.display = 'none';
            }
            
            // Disable all interactive buttons to prevent conflicts
            disableAllButtons();
            
            loadingModal.show();
        }

        function hideLoading() {
            // Force hide the modal and clean up any stuck backdrop
            loadingModal.hide();
            
            // Additional cleanup for stuck modals
            setTimeout(() => {
                // Force remove modal backdrop if it exists
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.remove();
                }
                
                // Ensure body doesn't have modal classes
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
                
                // Force modal to be hidden
                const modalElement = document.getElementById('loadingModal');
                if (modalElement) {
                    modalElement.classList.remove('show');
                    modalElement.style.display = 'none';
                    modalElement.setAttribute('aria-hidden', 'true');
                }
            }, 100);
            
            // Hide stop button when loading is hidden
            const stopButtonContainer = document.getElementById('stopButtonContainer');
            if (stopButtonContainer) {
                stopButtonContainer.style.display = 'none';
            }
            
            // Re-enable all buttons
            enableAllButtons();
        }

        function disableAllButtons() {
            // Disable main action buttons
            translateBtn.disabled = true;
            translateBtn.style.opacity = '0.6';
            
            // Don't disable generateSpeechBtn as it might be handling its own state
            
            // Disable other interactive elements
            const clearBtn = document.getElementById('clearText');
            if (clearBtn) {
                clearBtn.disabled = true;
                clearBtn.style.opacity = '0.6';
            }
            
            const swapBtn = document.getElementById('swapLanguages');
            if (swapBtn) {
                swapBtn.disabled = true;
                swapBtn.style.opacity = '0.6';
            }
            
            const copyBtn = document.getElementById('copyTranslation');
            if (copyBtn) {
                copyBtn.disabled = true;
                copyBtn.style.opacity = '0.6';
            }
            
            const refreshBtn = document.getElementById('refreshHistory');
            if (refreshBtn) {
                refreshBtn.disabled = true;
                refreshBtn.style.opacity = '0.6';
            }
            
            // Disable form inputs
            sourceLanguage.disabled = true;
            targetLanguage.disabled = true;
            voiceGender.disabled = true;
            voiceSelection.disabled = true;
            voiceSpeed.disabled = true;
            voicePitch.disabled = true;
            useElevenLabs.disabled = true;
            inputText.disabled = true;
            
            // Style disabled inputs
            const inputs = [sourceLanguage, targetLanguage, voiceGender, voiceSelection, voiceSpeed, voicePitch, useElevenLabs, inputText];
            inputs.forEach(input => {
                if (input) {
                    input.style.opacity = '0.6';
                    input.style.pointerEvents = 'none';
                }
            });
        }

        function enableAllButtons() {
            // Re-enable main action buttons
            translateBtn.disabled = false;
            translateBtn.style.opacity = '1';
            
            // Re-enable other interactive elements
            const clearBtn = document.getElementById('clearText');
            if (clearBtn) {
                clearBtn.disabled = false;
                clearBtn.style.opacity = '1';
            }
            
            const swapBtn = document.getElementById('swapLanguages');
            if (swapBtn) {
                swapBtn.disabled = false;
                swapBtn.style.opacity = '1';
            }
            
            const copyBtn = document.getElementById('copyTranslation');
            if (copyBtn) {
                copyBtn.disabled = false;
                copyBtn.style.opacity = '1';
            }
            
            const refreshBtn = document.getElementById('refreshHistory');
            if (refreshBtn) {
                refreshBtn.disabled = false;
                refreshBtn.style.opacity = '1';
            }
            
            // Re-enable form inputs
            sourceLanguage.disabled = false;
            targetLanguage.disabled = false;
            voiceGender.disabled = false;
            voiceSelection.disabled = false;
            voiceSpeed.disabled = false;
            voicePitch.disabled = false;
            useElevenLabs.disabled = false;
            inputText.disabled = false;
            
            // Reset input styles
            const inputs = [sourceLanguage, targetLanguage, voiceGender, voiceSelection, voiceSpeed, voicePitch, useElevenLabs, inputText];
            inputs.forEach(input => {
                if (input) {
                    input.style.opacity = '1';
                    input.style.pointerEvents = 'auto';
                }
            });
        }

        function showToast(message, type = 'info') {
            // Create toast element
            const toastContainer = document.getElementById('toastContainer') || createToastContainer();
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-white bg-${type} border-0`;
            toast.setAttribute('role', 'alert');
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            
            toastContainer.appendChild(toast);
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            
            // Remove toast element after it's hidden
            toast.addEventListener('hidden.bs.toast', () => {
                toast.remove();
            });
        }

        function createToastContainer() {
            const container = document.createElement('div');
            container.id = 'toastContainer';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
            return container;
        }

        function truncateText(text, maxLength) {
            return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMs / 3600000);
            const diffDays = Math.floor(diffMs / 86400000);
            
            if (diffMins < 1) return 'Just now';
            if (diffMins < 60) return `${diffMins}m ago`;
            if (diffHours < 24) return `${diffHours}h ago`;
            if (diffDays < 7) return `${diffDays}d ago`;
            
            return date.toLocaleDateString();
        }

        // Event delegation for history buttons
        document.addEventListener('click', function(e) {
            if (e.target.closest('.replay-btn')) {
                const id = e.target.closest('.replay-btn').dataset.id;
                replayTranslation(id);
            } else if (e.target.closest('.delete-btn')) {
                const id = e.target.closest('.delete-btn').dataset.id;
                deleteTranslation(id);
            }
        });

        // Replay translation
        function replayTranslation(id) {
            // Implementation for replaying a translation
            showToast('Replay functionality will be implemented', 'info');
        }

        // Delete translation
        async function deleteTranslation(id) {
            if (!confirm('Are you sure you want to delete this translation?')) {
                return;
            }
            
            try {
                const response = await fetch(`/api/translation/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast('Translation deleted successfully', 'success');
                    loadHistory();
                } else {
                    showToast(result.error || 'Failed to delete translation', 'danger');
                }
            } catch (error) {
                console.error('Delete error:', error);
                showToast('Failed to delete translation', 'danger');
            }
        }

        // Refresh history
        document.getElementById('refreshHistory').addEventListener('click', loadHistory);

        // Load voices when page loads
        window.addEventListener('load', function() {
            if ('speechSynthesis' in window) {
                speechSynthesis.getVoices();
                speechSynthesis.onvoiceschanged = function() {
                    console.log('Voices loaded:', speechSynthesis.getVoices().length);
                };
            }
        });

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Focus on input
            inputText.focus();
            
            // Load initial history
            loadHistory();
            
            // Initialize voice selection for Eleven Labs (if checked by default)
            if (useElevenLabs.checked) {
                voiceSelectionDiv.style.display = 'block';
                loadVoiceOptions();
            }
        });
    </script>
</body>
</html>