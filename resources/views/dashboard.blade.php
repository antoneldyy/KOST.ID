@extends('layout.master')

@section('content')
    <div class="main-content">
        <section class="section">
          <div class="section-header">
            <h1>Halo, {{ auth()->user()->name }}!</h1>
            <div class="section-header-breadcrumb ml-auto">
              <form method="GET" action="{{ route('dashboard') }}" class="form-inline">
                <select name="month" class="form-control mr-2">
                  @for($m=1;$m<=12;$m++)
                    <option value="{{ $m }}" {{ (isset($month) && $month==$m)?'selected':'' }}>{{ $m }}</option>
                  @endfor
                </select>
                <select name="year" class="form-control mr-2">
                  @for($y=now()->year-3;$y<=now()->year+1;$y++)
                    <option value="{{ $y }}" {{ (isset($year) && $year==$y)?'selected':'' }}>{{ $y }}</option>
                  @endfor
                </select>
                <button class="btn btn-primary mr-2">Filter</button>
                <a class="btn btn-success" href="{{ route('dashboard.export',[ 'month'=>request('month', $month ?? now()->month), 'year'=>request('year', $year ?? now()->year) ]) }}">Export CSV</a>
              </form>
            </div>
          </div>
          <div class="row">
            <div class="col-lg-4 col-md-4 col-sm-12">
              <div class="card card-statistic-2">
                <div class="card-icon shadow-primary bg-primary">
                  <i class="fas fa-door-open"></i>
                </div>
                <div class="card-wrap">
                  <div class="card-header">
                    <h4>Kamar</h4>
                  </div>
                  <div class="card-body">
                    Total: {{ $totalRooms ?? 0 }} | Terisi: {{ $occupiedRooms ?? 0 }} | Kosong: {{ $emptyRooms ?? 0 }}
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-12">
              <div class="card card-statistic-2">
                <div class="card-icon shadow-primary bg-success">
                  <i class="fas fa-money-bill"></i>
                </div>
                <div class="card-wrap">
                  <div class="card-header">
                    <h4>Pendapatan Bulan Ini</h4>
                  </div>
                  <div class="card-body">
                    Rp {{ number_format($monthlyRevenue ?? 0,0,',','.') }}
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-12">
              <div class="card card-statistic-2">
                <div class="card-icon shadow-primary bg-warning">
                  <i class="fas fa-coins"></i>
                </div>
                <div class="card-wrap">
                  <div class="card-header">
                    <h4>Status Pembayaran</h4>
                  </div>
                  <div class="card-body">
                    Lunas: {{ $paidCount ?? 0 }} | Belum: {{ $unpaidCount ?? 0 }}
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header">
                  <h4>Last Transactions</h4>
                  <div class="card-header-action">
                    <a href="{{ route('activities.index') }}" class="btn btn-primary">View All <i class="fas fa-chevron-right"></i></a>
                  </div>
                </div>
                <div class="card-body p-0">
                  <div class="table-responsive">
                    <table class="table table-striped">
                      <thead>
                        <tr>
                          <th>Waktu</th>
                          <th>Admin</th>
                          <th>Aktivitas</th>
                          <th>Detail</th>
                      </tr>
                      </thead>
                      <tbody>
                        @forelse($recentActivities as $activity)
                        <tr>
                          <td>{{ $activity->created_at->format('d/m/Y H:i') }}</td>
                          <td class="font-weight-600">{{ $activity->user->name }}</td>
                          <td>
                            @switch($activity->action)
                              @case('approve_payment')
                                <span class="badge badge-success">Approve Pembayaran</span>
                                @break
                              @case('create_room')
                                <span class="badge badge-info">Tambah Kamar</span>
                                @break
                              @case('update_room')
                                <span class="badge badge-warning">Update Kamar</span>
                                @break
                              @case('assign_tenant')
                                <span class="badge badge-primary">Assign Penghuni</span>
                                @break
                              @default
                                <span class="badge badge-secondary">{{ $activity->action }}</span>
                            @endswitch
                        </td>
                          <td>
                            @if($activity->meta)
                              @if(isset($activity->meta['room_number']))
                                Kamar {{ $activity->meta['room_number'] }}
                              @endif
                              @if(isset($activity->meta['tenant_name']))
                                - {{ $activity->meta['tenant_name'] }}
                              @endif
                              @if(isset($activity->meta['month']) && isset($activity->meta['year']))
                                ({{ $activity->meta['month'] }}/{{ $activity->meta['year'] }})
                              @endif
                            @else
                              -
                            @endif
                        </td>
                      </tr>
                        @empty
                        <tr>
                          <td colspan="4" class="text-center">Belum ada aktivitas bulan ini</td>
                      </tr>
                        @endforelse
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
@endsection

@section('js')
    <script src="{{asset('stisla/assets/modules/jquery.sparkline.min.js')}}"></script>
  <script src="{{asset('stisla/assets/modules/chart.min.js')}}"></script>
  <script src="{{asset('stisla/assets/modules/owlcarousel2/dist/owl.carousel.min.js')}}"></script>
  <script src="{{asset('stisla/assets/modules/summernote/summernote-bs4.js')}}"></script>
  <script src="{{asset('stisla/assets/modules/chocolat/dist/js/jquery.chocolat.min.js')}}"></script>
@endsection
