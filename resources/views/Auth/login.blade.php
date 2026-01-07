<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>PAGE LOGIN - HANDYMAN ADMIN</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="{{asset('public/assets/vendor/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">
  <link href="{{asset('public/assets/vendor/bootstrap-icons/bootstrap-icons.css')}}" rel="stylesheet">
  <link href="{{asset('public/assets/vendor/boxicons/css/boxicons.min.css')}}" rel="stylesheet">
  
  <!-- Template Main CSS File -->
  <link href="{{asset('public/assets/css/style.css')}}" rel="stylesheet">

  <!-- Custom CSS -->
  <style>
  body {
    font-family: 'Poppins', sans-serif;
    background: url('{{ asset('public/assets/img/rb_2149644201.png') }}') no-repeat center center fixed;
    background-size: cover;
    color: #fff;
    padding: 0;
    margin: 0;
  }

  .section.register {
    padding-top: 10vh;
    padding-bottom: 10vh;
  }

  .card {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 15px;
    box-shadow: 0 10px 15px rgba(0, 0, 0, 0.2);
    padding: 30px;
    margin-top: 30px;
  }

  .card-title {
    font-weight: 600;
    font-size: 24px;
    margin-bottom: 20px;
    color: white;
  }

  .form-label {
    font-size: 14px;
    color: #f1f1f1;
  }

  .form-control {
    background-color: #333;
    border: 1px solid #555;
    color: #f1f1f1;
    border-radius: 8px;
    font-size: 14px;
    padding: 12px;
  }

  .form-control:focus {
    background-color: #444;
    border-color: #4e73df;
    box-shadow: 0 0 10px rgba(78, 115, 223, 0.6);
  }

  .btn-primary {
    background-color: #4e73df;
    border: none;
    padding: 12px;
    font-weight: 600;
    font-size: 16px;
    border-radius: 8px;
    width: 100%;
    cursor: pointer;
    transition: background-color 0.3s ease;
  }

  .btn-primary:hover {
    background-color: #3c58c4;
  }

  .back-to-top {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background-color: #4e73df;
    padding: 12px;
    border-radius: 50%;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
  }

  .back-to-top i {
    font-size: 18px;
  }

  .logo span {
    font-weight: 700;
    font-size: 28px;
    color: #fff;
  }

  .alert-danger {
    color: #f8d7da;
    background-color: #f1b0b7;
    border: 1px solid #f5c2c7;
    border-radius: 8px;
    padding: 10px;
  }
</style>


</head>

<body>

  <main>
    <div class="container">

      <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">

              <div class="d-flex justify-content-center py-4">
                <a href="#" class="logo d-flex align-items-center w-auto">
                  <span>SUPER ADMIN👩‍💻</span>
                </a>
              </div><!-- End Logo -->

              <div class="card mb-3">

                <div class="card-body">

                  <div class="pt-4 pb-2">
                    <h5 class="card-title text-center pb-0 fs-4">Login to Your Account</h5>
                  </div>

                  <form action="{{route('Auth.login')}}" class="row g-3 needs-validation" method="post">
                    @csrf
                    <div class="col-12">
                      <label for="email" class="form-label">Email</label>
                      <div class="input-group has-validation">
                        <span class="input-group-text" id="email">@</span>
                        <input type="email" name="email" class="form-control" id="email" required>
                        @error('email')
                          <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <div class="invalid-feedback">Please enter your email.</div>
                      </div>
                    </div>

                    <div class="col-12">
                      <label for="yourPassword" class="form-label">Password</label>
                      <input type="password" name="password" class="form-control" id="password" required>
                      <div class="invalid-feedback">Please enter your password!</div>
                    </div>
                    <div class="col-12">
                      <button class="btn btn-primary w-100" type="submit" name="button" id="button">Login</button>
                    </div>

                  </form>

                </div>
              </div>
            </div>
          </div>
        </div>

      </section>

    </div>
  </main><!-- End #main -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="{{asset('public/assets/vendor/apexcharts/apexcharts.min.js')}}"></script>
  <script src="{{asset('public/assets/vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
  <script src="{{asset('public/assets/vendor/chart.js/chart.umd.js')}}"></script>
  <script src="{{asset('public/assets/vendor/echarts/echarts.min.js')}}"></script>
  <script src="{{asset('public/assets/vendor/quill/quill.js')}}"></script>
  <script src="{{asset('public/assets/vendor/simple-datatables/simple-datatables.js')}}"></script>
  <script src="{{asset('public/assets/vendor/tinymce/tinymce.min.js')}}"></script>
  <script src="{{asset('public/assets/vendor/php-email-form/validate.js')}}"></script>

  <!-- Template Main JS File -->
  <script src="{{asset('public/assets/js/main.js')}}"></script>

</body>

</html>
