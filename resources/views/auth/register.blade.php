<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Register &mdash; Stisla</title>

  <!-- General CSS Files -->
  <link rel="stylesheet" href="{{asset('stisla/assets/modules/bootstrap/css/bootstrap.min.css')}}">
  <link rel="stylesheet" href="{{asset('stisla/assets/modules/fontawesome/css/all.min.css')}}">

  <!-- CSS Libraries -->
  <link rel="stylesheet" href="{{asset('stisla/assets/modules/bootstrap-social/bootstrap-social.css')}}">

  <!-- Template CSS -->
  <link rel="stylesheet" href="{{asset('stisla/assets/css/style.css')}}">
  <link rel="stylesheet" href="{{asset('stisla/assets/css/components.css')}}">
<!-- Start GA -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-94034622-3"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-94034622-3');
</script>
<!-- /END GA --></head>

<body>
  <div id="app">
    <section class="section">
      <div class="container mt-5">
        <div class="row">
          <div class="col-12 col-sm-10 offset-sm-1 col-md-8 offset-md-2 col-lg-8 offset-lg-2 col-xl-6 offset-xl-3">
            <div class="login-brand">
              <img src="{{asset('stisla/assets/img/logo.png')}}" alt="logo" width="100" class="shadow-light rounded-circle">
            </div>

            @if (session('success'))
                <div class="alert alert-success">{{session('success')}}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card card-primary">
              <div class="card-header"><h4>Daftar Akun Baru</h4></div>

              <div class="card-body">
                <form method="POST" action="/register" class="needs-validation" novalidate="" enctype="multipart/form-data">
                    @csrf
                  <div class="row">
                    <div class="form-group col-12">
                      <label for="name">Nama Lengkap</label>
                      <input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}" required autofocus>
                      <div class="invalid-feedback">
                        Please fill in your full name
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="form-group col-12">
                      <label for="email">Email</label>
                      <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required>
                      <div class="invalid-feedback">
                        Please fill in your email
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="form-group col-6">
                      <label for="phone">Nomor Telepon</label>
                      <input id="phone" type="tel" class="form-control" name="phone" value="{{ old('phone') }}" required>
                      <div class="invalid-feedback">
                        Please fill in your phone number
                      </div>
                    </div>
                    <div class="form-group col-6">
                      <label for="occupation">Pekerjaan</label>
                      <input id="occupation" type="text" class="form-control" name="occupation" value="{{ old('occupation') }}" required>
                      <div class="invalid-feedback">
                        Please fill in your occupation
                      </div>
                    </div>
                  </div>

                  <div class="form-group">
                    <label for="address">Alamat Lengkap</label>
                    <textarea id="address" class="form-control" name="address" rows="3" required>{{ old('address') }}</textarea>
                    <div class="invalid-feedback">
                      Please fill in your address
                    </div>
                  </div>

                  <div class="form-group">
                    <label for="ktp">Upload KTP</label>
                    <input id="ktp" type="file" class="form-control-file" name="ktp" accept="image/*,.pdf" required>
                    <small class="form-text text-muted">
                      Format yang diperbolehkan: JPG, JPEG, PNG, PDF (Maksimal 2MB)
                    </small>
                    <div class="invalid-feedback">
                      Please upload your KTP
                    </div>
                  </div>

                  <div class="row">
                    <div class="form-group col-6">
                      <label for="password">Password</label>
                      <input id="password" type="password" class="form-control" name="password" required>
                      <div class="invalid-feedback">
                        Please fill in your password
                      </div>
                    </div>
                    <div class="form-group col-6">
                      <label for="password_confirmation">Konfirmasi Password</label>
                      <input id="password_confirmation" type="password" class="form-control" name="password_confirmation" required>
                      <div class="invalid-feedback">
                        Please confirm your password
                      </div>
                    </div>
                  </div>

                  <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                      Daftar
                    </button>
                  </div>
                </form>

              </div>
            </div>
            <div class="mt-5 text-muted text-center">
              Sudah punya akun? <a href="/login">Login di sini</a>
            </div>
            <div class="mt-2 text-muted text-center">
              <a href="/forgot-password">Lupa password?</a>
            </div>
            <div class="simple-footer">
              Copyright &copy; iKostDev 2025
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <!-- General JS Scripts -->
  <script src="{{asset('stisla/assets/modules/jquery.min.js')}}"></script>
  <script src="{{asset('stisla/assets/modules/popper.js')}}"></script>
  <script src="{{asset('stisla/assets/modules/tooltip.js')}}"></script>
  <script src="{{asset('stisla/assets/modules/bootstrap/js/bootstrap.min.js')}}"></script>
  <script src="{{asset('stisla/assets/modules/nicescroll/jquery.nicescroll.min.js')}}"></script>
  <script src="{{asset('stisla/assets/modules/moment.min.js')}}"></script>
  <script src="{{asset('stisla/assets/js/stisla.js')}}"></script>

  <!-- JS Libraies -->

  <!-- Page Specific JS File -->

  <!-- Template JS File -->
  <script src="{{asset('stisla/assets/js/scripts.js')}}"></script>
  <script src="{{asset('stisla/assets/js/custom.js')}}"></script>
</body>
</html>
