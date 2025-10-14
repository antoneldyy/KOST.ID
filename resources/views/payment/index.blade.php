@extends('user_layout.master')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading text-primary fw-bold">Pembayaran Bulanan</h3>
        </div>

        <div class="section-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @elseif(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-light">
                    <h4 class="fw-semibold mb-0">Daftar Pembayaran</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead class="table-primary">
                                <tr class="text-center">
                                    <th>No</th>
                                    <th>Bulan</th>
                                    <th>Tahun</th>
                                    <th>Jumlah</th>
                                    <th>Tanggal Bayar</th>
                                    <th>Bukti</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments as $index => $payment)
                                    <tr class="text-center">
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ \Carbon\Carbon::create()->month($payment->month)->translatedFormat('F') }}</td>
                                        <td>{{ $payment->year }}</td>
                                        <td>Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                        <td>{{ $payment->paid_at ? $payment->paid_at->format('d M Y') : '-' }}</td>

                                        <!-- Bukti pembayaran -->
                                        <td>
                                            @if($payment->proof_path)
                                                <a href="{{ asset('storage/' . $payment->proof_path) }}" target="_blank" class="btn btn-sm btn-success">
                                                    Lihat Bukti
                                                </a>
                                            @else
                                                <span class="text-muted">Belum Diupload</span>
                                            @endif
                                        </td>

                                        <!-- Status -->
                                        <td>
                                            @if($payment->approved_at)
                                                <span class="badge bg-success">Disetujui</span>
                                            @elseif($payment->proof_path)
                                                <span class="badge bg-warning text-dark">Menunggu</span>
                                            @else
                                                <span class="badge bg-secondary">Belum Bayar</span>
                                            @endif
                                        </td>

                                        <!-- Tombol aksi -->
                                        <td>
                                            @if(!$payment->proof_path)
                                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadModal{{ $payment->id }}">
                                                    Upload Bukti
                                                </button>
                                            @else
                                                <button class="btn btn-outline-secondary btn-sm" disabled>
                                                    Sudah Upload
                                                </button>
                                            @endif
                                        </td>
                                    </tr>

                                    <!-- Modal Upload -->
                                    <div class="modal fade" id="uploadModal{{ $payment->id }}" tabindex="-1" aria-labelledby="uploadModalLabel{{ $payment->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content rounded-4 shadow">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold" id="uploadModalLabel{{ $payment->id }}">Upload Bukti Pembayaran</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="{{ route('payment.upload', $payment->id) }}" method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <div class="mb-3 text-start">
                                                            <label class="form-label">Unggah Bukti (jpg/png/pdf)</label>
                                                            <input type="file" name="proof" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                                                        </div>
                                                        <p class="text-muted small mb-0">Pastikan file jelas dan ukuran maksimal 2MB.</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-primary">Kirim Bukti</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            Belum ada tagihan pembayaran bulan ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
