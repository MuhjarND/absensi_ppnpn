@extends('layouts.app')

@section('title', 'Absensi Hari Ini - PPNPN')
@section('page-title', 'Absensi Hari Ini')
@section('page-subtitle', now()->translatedFormat('l, d F Y'))

@section('sidebar-menu')
    <li class="menu-label">Menu Utama</li>
    <li class="menu-item"><a href="{{ route('pegawai.dashboard') }}"><i class="fas fa-home"></i> Dashboard</a></li>
    <li class="menu-item"><a href="{{ route('pegawai.attendance') }}" class="active"><i class="fas fa-fingerprint"></i>
            Absensi Hari Ini</a></li>
    <li class="menu-label">Laporan</li>
    <li class="menu-item"><a href="{{ route('pegawai.history') }}"><i class="fas fa-history"></i> Riwayat Absensi</a></li>
    <li class="menu-item"><a href="{{ route('pegawai.leave-requests.index') }}"><i class="fas fa-envelope-open-text"></i>
            Pengajuan Izin</a></li>
    <li class="menu-label">Akun</li>
    <li class="menu-item"><a href="{{ route('pegawai.account.password.edit') }}" class="{{ request()->routeIs('pegawai.account.password.*') ? 'active' : '' }}"><i class="fas fa-key"></i> Ubah Password</a></li>
@endsection

@section('content')
    <div class="card mb-4" style="border-left: 4px solid var(--primary);">
        <div class="card-body py-3"
            style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div>
                <div style="font-size: 13px; color: var(--text-secondary);">Shift Aktif Anda</div>
                <div style="font-weight: 700; font-size: 16px;">
                    @if($shift)
                        {{ $shift->name }} ({{ date('H:i', strtotime($shift->start_time)) }} -
                        {{ date('H:i', strtotime($shift->end_time)) }})
                    @else
                        Belum Ditentukan
                    @endif
                </div>
            </div>
            <div style="text-align: right;">
                <div id="live-clock"
                    style="font-size: 24px; font-weight: 800; color: var(--primary); font-variant-numeric: tabular-nums;">
                    00:00:00</div>
            </div>
        </div>
    </div>

    <div class="attendance-container">
        <!-- Camera Section -->
        <div class="card" style="margin-bottom: 0;">
            <div class="card-header pb-0 border-bottom-0">
                <h5><i class="fas fa-camera"></i> Scan Foto Wajah (Selfie)</h5>
            </div>
            <div class="card-body">
                <div class="camera-container" id="camera-container">
                    <video id="webcam" autoplay playsinline></video>
                    <canvas id="canvas" style="display: none;"></canvas>
                    <div class="camera-overlay">
                        <button type="button" class="capture-btn" id="capture-btn" title="Ambil Foto"></button>
                    </div>
                </div>
                <div id="photo-preview-container" style="display: none; margin-top: 15px;">
                    <img id="photo-preview" src=""
                        style="width: 100%; border-radius: var(--radius-sm); border: 2px solid var(--border);">
                    <div style="display: flex; gap: 10px; margin-top: 10px;">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="retake-btn" style="flex: 1;"><i
                                class="fas fa-redo"></i> Foto Ulang</button>
                    </div>
                </div>
                <input type="hidden" id="photo_data" name="photo">
            </div>
        </div>

        <!-- Location Section -->
        <div class="card" style="margin-bottom: 0;">
            <div class="card-header pb-0 border-bottom-0">
                <h5><i class="fas fa-map-marker-alt"></i> Peta Lokasi Anda</h5>
            </div>
            <div class="card-body">
                <div id="map" class="map-container"></div>

                <div id="location-status" class="location-status loading">
                    <i class="fas fa-spinner fa-spin"></i> Mendapatkan koordinat lokasi Anda...
                </div>

                <input type="hidden" id="latitude" name="latitude">
                <input type="hidden" id="longitude" name="longitude">

                <!-- Actions -->
                <div class="attendance-actions">
                    @if(!$todayAttendance)
                        <!-- Belum absen masuk -->
                        <button type="button" class="btn btn-success" id="btn-clock-in" onclick="submitAttendance('in')"
                            disabled style="box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);">
                            <i class="fas fa-sign-in-alt"></i> Absen Masuk Sekarang
                        </button>
                        <button type="button" class="btn btn-outline-primary" style="display: none;" id="btn-clock-out"
                            disabled>
                            Tunggu...
                        </button>
                    @elseif(!$todayAttendance->clock_out)
                        <!-- Sudah masuk, belum pulang -->
                        <div
                            style="background: rgba(16, 185, 129, 0.1); color: var(--success); padding: 12px; border-radius: var(--radius); text-align: center; border: 1px dashed var(--success); margin-bottom: 0; width: 100%;">
                            <i class="fas fa-check-circle"></i> Sudah absen masuk pada
                            {{ $todayAttendance->clock_in->format('H:i') }}
                        </div>
                        <button type="button" class="btn btn-primary" id="btn-clock-out" onclick="submitAttendance('out')"
                            disabled style="box-shadow: 0 4px 15px rgba(79, 70, 229, 0.4); margin-top: 10px;">
                            <i class="fas fa-sign-out-alt"></i> Absen Pulang Sekarang
                        </button>
                    @else
                        <!-- Sudah masuk & pulang -->
                        <div
                            style="background: rgba(16, 185, 129, 0.1); color: var(--success); padding: 16px; border-radius: var(--radius); text-align: center; border: 1px dashed var(--success); width: 100%;">
                            <h4><i class="fas fa-check-circle"></i> Absensi Selesai</h4>
                            <p style="margin: 5px 0 0; font-size: 14px;">Terima kasih, Anda telah menyelesaikan absensi hari ini
                                (Pulang: {{ $todayAttendance->clock_out->format('H:i') }}).</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts for Camera and Geolocation are rendered down below -->
@endsection

@section('scripts')
    <script>
        // Live Clock
        setInterval(function () {
            var now = new Date();
            document.getElementById('live-clock').innerText = now.toLocaleTimeString('id-ID');
        }, 1000);

        // Initial Variables
        const locations = @json($locations);
        const video = document.getElementById('webcam');
        const canvas = document.getElementById('canvas');
        const captureBtn = document.getElementById('capture-btn');
        const retakeBtn = document.getElementById('retake-btn');
        const photoContainer = document.getElementById('camera-container');
        const previewContainer = document.getElementById('photo-preview-container');
        const photoPreview = document.getElementById('photo-preview');
        const photoData = document.getElementById('photo_data');

        let isPhotoCaptured = false;
        let isLocationValid = false;
        let userLat = null;
        let userLng = null;
        let map = null;
        let userMarker = null;

        // 1. Initialize Camera
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            // Preferred camera: front facing
            navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } })
                .then(function (stream) {
                    video.srcObject = stream;
                })
                .catch(function (err) {
                    console.error("Camera error:", err);
                    alert("Gagal mengakses kamera. Pastikan browser memiliki izin untuk mengakses kamera (Selfie).");
                });
        }

        // Capture logic
        captureBtn.addEventListener('click', function () {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

            let dataURL = canvas.toDataURL('image/jpeg', 0.8);
            photoPreview.src = dataURL;
            photoData.value = dataURL;

            photoContainer.style.display = 'none';
            previewContainer.style.display = 'block';
            isPhotoCaptured = true;

            checkButtonStatus();
        });

        retakeBtn.addEventListener('click', function () {
            photoContainer.style.display = 'block';
            previewContainer.style.display = 'none';
            photoData.value = '';
            isPhotoCaptured = false;

            checkButtonStatus();
        });

        // 2. Initialize Map & Geolocation
        function initMap() {
            // Default center to first valid location or Jakarta
            const defaultLat = locations.length > 0 ? locations[0].latitude : -6.200000;
            const defaultLng = locations.length > 0 ? locations[0].longitude : 106.845000;

            map = L.map('map').setView([defaultLat, defaultLng], 16);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            // Draw office radius circles
            locations.forEach(loc => {
                L.circle([loc.latitude, loc.longitude], {
                    color: '#4f46e5',
                    fillColor: '#4f46e5',
                    fillOpacity: 0.1,
                    radius: loc.radius
                }).addTo(map).bindPopup("<b>" + loc.name + "</b><br>Radius: " + loc.radius + "m");

                L.marker([loc.latitude, loc.longitude], {
                    icon: L.icon({
                        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-blue.png',
                        iconSize: [25, 41], iconAnchor: [12, 41]
                    })
                }).addTo(map);
            });

            // Get User Location
            if (navigator.geolocation) {
                navigator.geolocation.watchPosition(
                    function (position) {
                        updateUserLocation(position.coords.latitude, position.coords.longitude);
                    },
                    function (error) {
                        const statusEl = document.getElementById('location-status');
                        statusEl.className = 'location-status invalid';
                        statusEl.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Gagal mendapatkan lokasi GPS. Pastikan GPS aktif dan diizinkan browser.';
                    },
                    { enableHighAccuracy: true, maximumAge: 0 }
                );
            }
        }

        initMap();

        // Haversine formula to calculate distance in meters
        function getDistance(lat1, lon1, lat2, lon2) {
            const R = 6371e3; // metres
            const φ1 = lat1 * Math.PI / 180;
            const φ2 = lat2 * Math.PI / 180;
            const Δφ = (lat2 - lat1) * Math.PI / 180;
            const Δλ = (lon2 - lon1) * Math.PI / 180;

            const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
                Math.cos(φ1) * Math.cos(φ2) *
                Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

            return R * c;
        }

        function updateUserLocation(lat, lng) {
            userLat = lat;
            userLng = lng;

            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;

            // Update map marker
            if (!userMarker) {
                userMarker = L.marker([lat, lng], {
                    icon: L.icon({
                        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
                        iconSize: [25, 41], iconAnchor: [12, 41]
                    })
                }).addTo(map).bindPopup("Lokasi Anda Saat Ini").openPopup();
                map.flyTo([lat, lng], 18);
            } else {
                userMarker.setLatLng([lat, lng]);
            }

            // Check if within any radius
            isLocationValid = false;
            let minDistance = 999999;

            locations.forEach(loc => {
                const distance = getDistance(lat, lng, loc.latitude, loc.longitude);
                if (distance < minDistance) minDistance = distance;

                if (distance <= loc.radius) {
                    isLocationValid = true;
                }
            });

            const statusEl = document.getElementById('location-status');
            if (isLocationValid) {
                statusEl.className = 'location-status valid';
                statusEl.innerHTML = '<i class="fas fa-check-circle"></i> Lokasi Valid. Anda berada di area kantor.';
            } else {
                statusEl.className = 'location-status invalid';
                statusEl.innerHTML = `<i class="fas fa-times-circle"></i> Di luar jangkauan (Jarak: ${Math.round(minDistance)}m dari batas terdekat). Masuk ke area kantor untuk absen.`;
            }

            checkButtonStatus();
        }

        function checkButtonStatus() {
            const btnIn = document.getElementById('btn-clock-in');
            const btnOut = document.getElementById('btn-clock-out');

            const isReady = isPhotoCaptured && isLocationValid && userLat !== null;

            if (btnIn) btnIn.disabled = !isReady;
            if (btnOut) btnOut.disabled = !isReady;
        }

        // Submit Action via AJAX
        function submitAttendance(type) {
            if (!isPhotoCaptured || !isLocationValid) return;

            const btn = type === 'in' ? document.getElementById('btn-clock-in') : document.getElementById('btn-clock-out');
            const originalText = btn.innerHTML;

            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
            btn.disabled = true;

            const url = type === 'in' ? '/pegawai/attendance/clock-in' : '/pegawai/attendance/clock-out';
            const data = {
                _token: document.querySelector('meta[name="csrf-token"]').content,
                latitude: userLat,
                longitude: userLng,
                photo: photoData.value
            };

            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(data)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.reload(); // Reload to update UI
                    } else {
                        alert(data.error || 'Terjadi kesalahan.');
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan koneksi jaringan.');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
        }
    </script>
@endsection
