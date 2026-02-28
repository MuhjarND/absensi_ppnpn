@extends('layouts.app')

@section('title', 'Tambah Lokasi - Absensi PPNPN')
@section('page-title', 'Tambah Lokasi Kantor')

@section('sidebar-menu')
    <li class="menu-label">Menu Utama</li>
    <li class="menu-item"><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i> Dashboard</a></li>
    <li class="menu-label">Master Data</li>
    <li class="menu-item"><a href="{{ route('admin.employees.index') }}"><i class="fas fa-users"></i> Data Pegawai</a></li>
    <li class="menu-item"><a href="{{ route('admin.locations.index') }}" class="active"><i
                class="fas fa-map-marker-alt"></i> Lokasi Kantor</a></li>
    <li class="menu-item"><a href="{{ route('admin.shifts.index') }}"><i class="fas fa-clock"></i> Data Shift</a></li>
@endsection

@section('content')
    <div class="card" style="max-width: 900px;">
        <div class="card-header">
            <h5>Form Data Lokasi</h5>
            <a href="{{ route('admin.locations.index') }}" class="btn btn-sm btn-outline-primary"><i
                    class="fas fa-arrow-left"></i> Kembali</a>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.locations.store') }}" method="POST">
                @csrf

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                    <!-- Kiri: Form -->
                    <div>
                        <div class="form-group">
                            <label>Nama Lokasi <span style="color: var(--danger)">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required
                                placeholder="Contoh: Kantor Pusat">
                        </div>

                        <div class="form-group">
                            <label>Alamat Lengkap <span style="color: var(--danger)">*</span></label>
                            <textarea name="address" class="form-control" required rows="3">{{ old('address') }}</textarea>
                        </div>

                        <div class="form-group">
                            <label>Radius Validasi (Meter) <span style="color: var(--danger)">*</span></label>
                            <input type="number" name="radius" class="form-control" value="{{ old('radius', 100) }}"
                                required min="10" max="5000">
                            <small style="color: var(--text-secondary); display: block; margin-top: 4px;">Jarak maksimal
                                pegawai bisa melakukan absensi dari titik pusat.</small>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group">
                                <label>Latitude <span style="color: var(--danger)">*</span></label>
                                <input type="text" id="latitude" name="latitude" class="form-control"
                                    value="{{ old('latitude', -6.200000) }}" required readonly>
                            </div>
                            <div class="form-group">
                                <label>Longitude <span style="color: var(--danger)">*</span></label>
                                <input type="text" id="longitude" name="longitude" class="form-control"
                                    value="{{ old('longitude', 106.845000) }}" required readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Kanan: Peta -->
                    <div>
                        <label style="font-size: 13px; font-weight: 600; margin-bottom: 6px; display: block;">Tentukan Titik
                            Koordinat <span style="color: var(--danger)">*</span></label>
                        <div id="map"
                            style="height: 350px; border-radius: var(--radius); border: 2px solid var(--border); z-index: 1;">
                        </div>
                        <small style="color: var(--text-secondary); display: block; margin-top: 8px;"><i
                                class="fas fa-info-circle"></i> Geser marker merah atau klik pada peta untuk menentukan
                            koordinat.</small>
                    </div>
                </div>

                <div style="margin-top: 30px; text-align: right; border-top: 1px solid var(--border); padding-top: 20px;">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Lokasi</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initial coordinates (Jakarta)
            var initLat = {{ old('latitude', '-6.200000') }};
            var initLng = {{ old('longitude', '106.845000') }};
            var currentRadius = {{ old('radius', 100) }};

            // Initialize map
            var map = L.map('map').setView([initLat, initLng], 15);

            // Add tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Add marker
            var marker = L.marker([initLat, initLng], {
                draggable: true
            }).addTo(map);

            // Add circle for radius
            var circle = L.circle([initLat, initLng], {
                color: '#4f46e5',
                fillColor: '#4f46e5',
                fillOpacity: 0.2,
                radius: currentRadius
            }).addTo(map);

            // Update inputs when marker is dragged
            marker.on('dragend', function (e) {
                var position = marker.getLatLng();
                updateCoordinates(position.lat, position.lng);
            });

            // Update marker when map is clicked
            map.on('click', function (e) {
                marker.setLatLng(e.latlng);
                updateCoordinates(e.latlng.lat, e.latlng.lng);
            });

            // Update circle when radius input changes
            document.querySelector('input[name="radius"]').addEventListener('input', function (e) {
                var radius = parseInt(e.target.value) || 0;
                circle.setRadius(radius);
            });

            function updateCoordinates(lat, lng) {
                document.getElementById('latitude').value = lat.toFixed(8);
                document.getElementById('longitude').value = lng.toFixed(8);
                circle.setLatLng([lat, lng]);
                map.panTo([lat, lng]);
            }
        });
    </script>
@endsection