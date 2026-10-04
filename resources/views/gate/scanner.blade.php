@extends('layouts.app')

@section('title', 'Gate Scanner')

@section('content')
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex gap-2 mb-3">
                        <button type="button" id="dir-in" class="btn btn-lg flex-fill btn-success">IN</button>
                        <button type="button" id="dir-out" class="btn btn-lg flex-fill btn-outline-danger">OUT</button>
                    </div>

                    <div id="result" class="alert d-none py-4 text-center" role="alert">
                        <div id="result-status" class="display-6 fw-bold"></div>
                        <div id="result-name" class="fs-5"></div>
                        <div id="result-message" class="small"></div>
                    </div>

                    <video id="camera" class="w-100 rounded bg-dark" style="max-height: 320px; object-fit: cover;" muted playsinline></video>
                    <canvas id="camera-canvas" class="d-none"></canvas>
                    <p id="camera-unavailable" class="text-muted small mt-2 d-none">
                        The camera could not be started (no camera, or permission was not granted). Use search or "Log by name" below.
                    </p>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <label class="form-label" for="search">Search by name (if the QR won't scan)</label>
                    <input type="text" id="search" class="form-control mb-2" placeholder="Type a name&hellip;" autocomplete="off">
                    <div id="search-results" class="list-group mb-3"></div>

                    <div class="d-flex gap-2">
                        <input type="text" id="manual-name" class="form-control" placeholder="Not found &mdash; type their full name">
                        <button type="button" id="log-by-name" class="btn btn-outline-secondary text-nowrap">Log by name</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Roster status</h6>
                    <p class="mb-1">Last downloaded: <span id="roster-synced">never</span></p>
                    <p class="mb-3"><span id="roster-count">0</span> Attendees on this device</p>
                    <button type="button" id="refresh-roster" class="btn btn-sm btn-outline-primary mb-4">Refresh list</button>

                    <h6 class="card-title">Sync status</h6>
                    <p id="online-status" class="mb-1">Online</p>
                    <p><span id="queue-count">0</span> scan(s) waiting to sync</p>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/jsqr.js') }}"></script>
    <script>
(function () {
    'use strict';

    var ROSTER_KEY = 'gate_roster';
    var ROSTER_SYNCED_KEY = 'gate_roster_synced_at';
    var QUEUE_KEY = 'gate_queue';

    var direction = 'in';
    var roster = [];

    function uuid() {
        if (window.crypto && crypto.randomUUID) {
            return crypto.randomUUID();
        }
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            var r = Math.random() * 16 | 0, v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    function loadJSON(key, fallback) {
        try {
            var raw = localStorage.getItem(key);
            return raw ? JSON.parse(raw) : fallback;
        } catch (e) {
            return fallback;
        }
    }

    function saveJSON(key, value) {
        try {
            localStorage.setItem(key, JSON.stringify(value));
        } catch (e) { /* storage unavailable; scan still works for this session */ }
    }

    function refreshRosterUi() {
        roster = loadJSON(ROSTER_KEY, []);
        document.getElementById('roster-count').textContent = roster.length;
        var syncedAt = localStorage.getItem(ROSTER_SYNCED_KEY);
        document.getElementById('roster-synced').textContent = syncedAt ? new Date(syncedAt).toLocaleString() : 'never';
    }

    function refreshQueueUi() {
        document.getElementById('queue-count').textContent = loadJSON(QUEUE_KEY, []).length;
    }

    function downloadRoster() {
        fetch('/gate/roster.json', {headers: {Accept: 'application/json'}})
            .then(function (r) { return r.ok ? r.json() : Promise.reject(); })
            .then(function (data) {
                saveJSON(ROSTER_KEY, data);
                localStorage.setItem(ROSTER_SYNCED_KEY, new Date().toISOString());
                refreshRosterUi();
            })
            .catch(function () { /* offline or failed: keep using the cached roster */ });
    }

    function setDirection(next) {
        direction = next;
        document.getElementById('dir-in').className = 'btn btn-lg flex-fill ' + (next === 'in' ? 'btn-success' : 'btn-outline-success');
        document.getElementById('dir-out').className = 'btn btn-lg flex-fill ' + (next === 'out' ? 'btn-danger' : 'btn-outline-danger');
    }

    function showResult(payload) {
        var box = document.getElementById('result');
        box.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-warning');

        var statusEl = document.getElementById('result-status');
        var nameEl = document.getElementById('result-name');
        var messageEl = document.getElementById('result-message');

        if (payload.status === 'error') {
            box.classList.add('alert-danger');
            statusEl.textContent = 'NOT SAVED';
            nameEl.textContent = '';
            messageEl.textContent = payload.message || 'The scan could not be recorded.';
        } else if (payload.status === 'invalid') {
            box.classList.add('alert-danger');
            statusEl.textContent = 'INVALID';
            nameEl.textContent = '';
            messageEl.textContent = payload.message || 'Card not recognized.';
        } else if (payload.mismatch) {
            box.classList.add('alert-warning');
            statusEl.textContent = payload.status.toUpperCase();
            nameEl.textContent = payload.attendee ? (payload.attendee.first_name + ' · ' + payload.attendee.circuit) : '';
            messageEl.textContent = payload.message || '';
        } else {
            box.classList.add(payload.status === 'in' ? 'alert-success' : 'alert-danger');
            statusEl.textContent = payload.status.toUpperCase();
            nameEl.textContent = payload.attendee ? (payload.attendee.first_name + ' · ' + payload.attendee.circuit) : (payload.queued ? 'Saved offline' : '');
            messageEl.textContent = payload.queued ? 'No connection — will sync automatically.' : '';
        }
    }

    function enqueue(scan) {
        var queue = loadJSON(QUEUE_KEY, []);
        queue.push(scan);
        saveJSON(QUEUE_KEY, queue);
        refreshQueueUi();
    }

    function sendScan(scan) {
        return fetch('/gate/scans', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify(scan),
        });
    }

    function recordScan(qrCode, manualName) {
        var scan = {
            client_scan_id: uuid(),
            direction: direction,
            scanned_at: new Date().toISOString(),
            qr_code: qrCode || null,
            manual_name: manualName || null,
        };

        sendScan(scan)
            .then(function (r) {
                if (r.status === 401 || r.status === 419) {
                    return {status: 'error', message: 'Your session expired. Please sign in again.'};
                }
                if (!r.ok) {
                    return {status: 'error', message: 'The scan could not be recorded.'};
                }
                return r.json();
            })
            .then(function (payload) { showResult(payload); })
            .catch(function () {
                // fetch() itself rejected: no response was received at all, i.e. genuinely offline.
                enqueue(scan);
                showResult({status: direction, mismatch: false, queued: true, attendee: null});
            });
    }

    function flushQueue() {
        var queue = loadJSON(QUEUE_KEY, []);
        if (queue.length === 0 || !navigator.onLine) {
            return;
        }

        var remaining = queue.slice();

        function sendNext() {
            if (remaining.length === 0) {
                saveJSON(QUEUE_KEY, []);
                refreshQueueUi();
                return;
            }

            sendScan(remaining[0])
                .then(function (r) { return r.ok ? Promise.resolve() : Promise.reject(); })
                .then(function () {
                    remaining.shift();
                    saveJSON(QUEUE_KEY, remaining);
                    refreshQueueUi();
                    sendNext();
                })
                .catch(function () { /* stop; try again on the next flush */ });
        }

        sendNext();
    }

    function updateOnlineStatus() {
        document.getElementById('online-status').textContent = navigator.onLine ? 'Online' : 'Offline';
        if (navigator.onLine) {
            flushQueue();
            downloadRoster();
        }
    }

    document.getElementById('dir-in').addEventListener('click', function () { setDirection('in'); });
    document.getElementById('dir-out').addEventListener('click', function () { setDirection('out'); });
    document.getElementById('refresh-roster').addEventListener('click', downloadRoster);

    document.getElementById('search').addEventListener('input', function (e) {
        var term = e.target.value.trim().toLowerCase();
        var box = document.getElementById('search-results');
        box.innerHTML = '';
        if (term.length < 2) {
            return;
        }

        roster.filter(function (a) { return a.first_name.toLowerCase().indexOf(term) !== -1; })
            .slice(0, 8)
            .forEach(function (a) {
                var item = document.createElement('button');
                item.type = 'button';
                item.className = 'list-group-item list-group-item-action';
                item.textContent = a.first_name + ' — ' + a.local_church + ' (' + a.circuit + ')';
                item.addEventListener('click', function () {
                    recordScan(a.qr_code, null);
                    box.innerHTML = '';
                    document.getElementById('search').value = '';
                });
                box.appendChild(item);
            });
    });

    document.getElementById('log-by-name').addEventListener('click', function () {
        var name = document.getElementById('manual-name').value.trim();
        if (name === '') {
            return;
        }
        recordScan(null, name);
        document.getElementById('manual-name').value = '';
    });

    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);
    setInterval(flushQueue, 15000);
    setInterval(function () { if (navigator.onLine) { downloadRoster(); } }, 300000);

    refreshRosterUi();
    refreshQueueUi();
    updateOnlineStatus();
    setDirection('in');

    // Camera-based QR scanning. Decoded locally with the bundled jsQR library (public/js/jsqr.js)
    // rather than the browser's native BarcodeDetector, which is not reliably available on
    // desktop Chrome or Safari/iPhone — jsQR works the same on every camera-capable browser,
    // online or offline, since it's a self-hosted file rather than a CDN script.
    var video = document.getElementById('camera');
    var canvas = document.getElementById('camera-canvas');
    var canvasContext = canvas.getContext('2d', {willReadFrequently: true});
    var lastCode = null;
    var lastCodeAt = 0;

    function scanFrame() {
        if (video.readyState === video.HAVE_ENOUGH_DATA) {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvasContext.drawImage(video, 0, 0, canvas.width, canvas.height);

            var imageData = canvasContext.getImageData(0, 0, canvas.width, canvas.height);
            var code = window.jsQR ? window.jsQR(imageData.data, imageData.width, imageData.height) : null;

            if (code && code.data) {
                var now = Date.now();
                if (!(code.data === lastCode && (now - lastCodeAt) < 3000)) {
                    lastCode = code.data;
                    lastCodeAt = now;
                    recordScan(code.data, null);
                }
            }
        }

        requestAnimationFrame(scanFrame);
    }

    function startCamera(stream) {
        video.srcObject = stream;
        video.play();
        requestAnimationFrame(scanFrame);
    }

    var CAMERA_ERROR_REASONS = {
        NotAllowedError: 'Camera permission was denied. Check your browser’s site settings and allow the camera for this page, then reload.',
        PermissionDeniedError: 'Camera permission was denied. Check your browser’s site settings and allow the camera for this page, then reload.',
        NotFoundError: 'No camera was found on this device.',
        NotReadableError: 'The camera is already in use by another app (e.g. Zoom, Teams, or another browser tab). Close it and reload this page.',
        OverconstrainedError: 'No camera on this device matches what was requested.',
        SecurityError: 'This page isn’t considered secure by your browser, so it won’t allow camera access. Camera access needs https:// (or http://localhost).',
    };

    function showCameraUnavailable(error) {
        video.classList.add('d-none');
        var message = document.getElementById('camera-unavailable');
        message.classList.remove('d-none');

        var reason = error && CAMERA_ERROR_REASONS[error.name];
        message.textContent = reason
            ? reason
            : 'The camera could not be started (no camera, or permission was not granted). Use search or "Log by name" below.';

        if (error && window.console && console.error) {
            console.error('Gate scanner camera error:', error.name, error.message);
        }
    }

    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        // Prefer the rear camera (facingMode as "ideal", not a hard requirement) — a laptop or
        // desktop webcam has no "environment"-facing camera at all, and a hard constraint there
        // makes getUserMedia reject outright even though a perfectly usable camera exists.
        navigator.mediaDevices.getUserMedia({video: {facingMode: {ideal: 'environment'}}})
            .then(startCamera)
            .catch(function () {
                // Fall back to whatever camera is available, with no facing preference at all.
                navigator.mediaDevices.getUserMedia({video: true})
                    .then(startCamera)
                    .catch(showCameraUnavailable);
            });
    } else {
        showCameraUnavailable();
    }
})();
    </script>
@endsection
