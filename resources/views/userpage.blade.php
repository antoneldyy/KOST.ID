@extends('user_layout.master')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Riwayat Transaksi Terakhir</h4>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive table-invoice">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Bulan</th>
                                        <th>Tahun</th>
                                        <th>Jumlah</th>
                                        <th>Tanggal Bayar</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($payments as $index => $payment)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ \Carbon\Carbon::create()->month($payment->month)->translatedFormat('F') }}</td>
                                            <td>{{ $payment->year }}</td>
                                            <td>Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                            <td>{{ $payment->paid_at ? $payment->paid_at->format('d M Y') : '-' }}</td>
                                            <td>
                                                @if($payment->approved_at)
                                                    <div class="badge badge-success">Disetujui</div>
                                                @else
                                                    <div class="badge badge-warning">Menunggu</div>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">Belum ada transaksi</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grafik Pembayaran -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h4>Grafik Pembayaran Bulanan</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="paymentChart" height="180"></canvas>
                    </div>
                </div>

                <!-- Kalender -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h4>Kalender Pembayaran</h4>
                    </div>
                    <div class="card-body p-2">
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('js')
<!-- Chart.js -->
<script src="{{ asset('stisla/assets/modules/chart.min.js') }}"></script>

<!-- FullCalendar -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

<script>
    // === Grafik Pembayaran ===
    const ctx = document.getElementById('paymentChart').getContext('2d');
    const paymentChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($labels) !!},
            datasets: [{
                label: 'Total Pembayaran (Rp)',
                data: {!! json_encode($totals) !!},
                backgroundColor: '#6777ef',
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });

    // === Kalender Pembayaran ===
    document.addEventListener('DOMContentLoaded', function () {
        const calendarEl = document.getElementById('calendar');
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'id',
            height: 400,
            events: [
                @foreach($payments as $p)
                    {
                        title: 'Bayar Rp {{ number_format($p->amount, 0, ',', '.') }}',
                        start: '{{ $p->paid_at ? $p->paid_at->toDateString() : now()->toDateString() }}',
                        color: '{{ $p->approved_at ? "#28a745" : "#ffc107" }}'
                    },
                @endforeach
            ],
            eventDidMount: function(info) {
                info.el.style.borderRadius = '6px';
                info.el.style.padding = '2px 6px';
                info.el.style.fontSize = '13px';
            }
        });
        calendar.render();
    });
</script>
@endsection
