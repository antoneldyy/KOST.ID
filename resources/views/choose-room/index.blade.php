@extends('user_layout.master')

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card shadow-lg border-0 rounded-4 p-4" style="max-width: 500px; width: 100%;">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-primary mb-2">Pilih Kamar Anda</h3>
            <p class="text-muted mb-0">Silakan pilih kamar sebelum masuk ke dashboard</p>
        </div>

        {{-- Pesan sukses atau error --}}
        @if(session('success'))
            <div class="alert alert-success mt-3">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger mt-3">{{ session('error') }}</div>
        @endif

        <form action="{{ route('pilih.kamar.store') }}" method="POST" class="mt-4">
            @csrf

            <div class="mb-4">
                <label for="room" class="form-label fw-semibold text-secondary">Nomor Kamar</label>
                <div class="input-group shadow-sm rounded-3">
                    <span class="input-group-text bg-primary text-white fw-semibold px-3">🏠</span>
                    <select name="id" id="room" class="form-select border-0 rounded-end" required>
                        <option value="" selected disabled>-- Pilih Nomor Kamar --</option>
                        @forelse($rooms as $room)
                            <option value="{{ $room->id }}">Kamar No. {{ $room->number }}</option>
                        @empty
                            <option value="">Tidak ada kamar tersedia</option>
                        @endforelse
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 rounded-3 py-2 mt-3 fw-semibold shadow-sm">
                Simpan
            </button>
        </form>
    </div>
</div>

@push('styles')
<style>
    select.form-select {
        background-color: #f8f9fa;
        transition: all 0.2s ease;
    }

    select.form-select:focus {
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        background-color: #fff;
        border-color: #86b7fe;
    }

    .input-group-text {
        border: none;
    }

    .card {
        animation: fadeInUp 0.6s ease-in-out;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endpush
@endsection
