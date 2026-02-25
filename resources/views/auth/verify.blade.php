<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Verify OTP</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="{{ asset('vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/ti-icons/css/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/font-awesome/css/font-awesome.min.css') }}">
    <!-- endinject -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <!-- End layout styles -->
    <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}" />
    <style>
      .otp-input-group {
        display: flex;
        gap: 10px;
        justify-content: center;
        margin: 20px 0;
      }
      .otp-input {
        width: 50px;
        height: 50px;
        text-align: center;
        font-size: 24px;
        font-weight: bold;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        transition: border-color 0.3s;
      }
      .otp-input:focus {
        outline: none;
        border-color: #7c3aed;
        box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
      }
      .resend-section {
        text-align: center;
        margin-top: 20px;
      }
      .resend-link {
        color: #7c3aed;
        text-decoration: none;
        font-weight: 500;
      }
      .resend-link:hover {
        text-decoration: underline;
      }
      .resend-timer {
        color: #999;
        font-size: 14px;
      }
    </style>
  </head>
  <body>
    <div class="container-scroller">
      <div class="container-fluid page-body-wrapper full-page-wrapper">
        <div class="content-wrapper d-flex align-items-center auth">
          <div class="row flex-grow">
            <div class="col-lg-4 mx-auto">
              <div class="auth-form-light text-left p-5">
                <div class="brand-logo">
                  <img src="{{ asset('images/logo.svg') }}" alt="Logo">
                </div>
                <h4>Verifikasi OTP</h4>
                <h6 class="font-weight-light">Masukkan kode OTP yang telah dikirim ke email Anda</h6>
                
                @if (session('error'))
                  <div class="alert alert-danger mt-3" role="alert">
                    {{ session('error') }}
                  </div>
                @endif

                @if (session('success'))
                  <div class="alert alert-success mt-3" role="alert">
                    {{ session('success') }}
                  </div>
                @endif

                <form method="POST" action="{{ route('check-verify') }}" class="pt-3" id="otp-form">
                  @csrf
                  
                  <div class="form-group">
                    <label for="otp" class="mb-2">Kode OTP</label>
                    <div class="otp-input-group" id="otp-inputs">
                      <input type="text" class="otp-input" maxlength="1" inputmode="numeric" placeholder="0" autocomplete="off">
                      <input type="text" class="otp-input" maxlength="1" inputmode="numeric" placeholder="0" autocomplete="off">
                      <input type="text" class="otp-input" maxlength="1" inputmode="numeric" placeholder="0" autocomplete="off">
                      <input type="text" class="otp-input" maxlength="1" inputmode="numeric" placeholder="0" autocomplete="off">
                      <input type="text" class="otp-input" maxlength="1" inputmode="numeric" placeholder="0" autocomplete="off">
                      <input type="text" class="otp-input" maxlength="1" inputmode="numeric" placeholder="0" autocomplete="off">
                    </div>
                    <input type="hidden" name="otp" id="otp-value">
                  </div>

                  <div class="mt-3 d-grid gap-2">
                    <button type="submit" class="btn btn-block btn-gradient-primary btn-lg font-weight-medium auth-form-btn">VERIFIKASI</button>
                  </div>
                </form>

                <div class="resend-section">
                  <p class="text-muted mb-2">Belum menerima kode?</p>
                  <form method="POST" action="{{ route('resend-verify') }}" class="d-inline" id="resend-form">
                    @csrf
                    <button type="submit" class="btn btn-link p-0 resend-link">Kirim ulang OTP</button>
                  </form>
                  <div class="resend-timer mt-2" id="resend-timer"></div>
                </div>

                <div class="text-center mt-4 font-weight-light">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-link p-0 text-primary">Kembali ke halaman login</button>
                    </form>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- content-wrapper ends -->
      </div>
      <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->
    <!-- plugins:js -->
    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
    <!-- endinject -->
    <!-- inject:js -->
    <script src="{{ asset('js/off-canvas.js') }}"></script>
    <script src="{{ asset('js/misc.js') }}"></script>
    <script src="{{ asset('js/settings.js') }}"></script>
    <script src="{{ asset('js/jquery.cookie.js') }}"></script>
    <!-- endinject -->

    <script>
      // Handle OTP input
      const otpInputs = document.querySelectorAll('.otp-input');
      const otpValue = document.getElementById('otp-value');
      const otpForm = document.getElementById('otp-form');

      otpInputs.forEach((input, index) => {
        input.addEventListener('input', (e) => {
          // Only allow numbers
          if (!/^\d$/.test(e.target.value)) {
            e.target.value = '';
            return;
          }

          // Combine all OTP values
          const otp = Array.from(otpInputs).map(i => i.value).join('');
          otpValue.value = otp;

          // Move to next input
          if (e.target.value && index < otpInputs.length - 1) {
            otpInputs[index + 1].focus();
          }
        });

        input.addEventListener('keydown', (e) => {
          // Backspace
          if (e.key === 'Backspace') {
            e.target.value = '';
            if (index > 0) {
              otpInputs[index - 1].focus();
            }
          }
          // Arrow keys
          if (e.key === 'ArrowLeft' && index > 0) {
            otpInputs[index - 1].focus();
          }
          if (e.key === 'ArrowRight' && index < otpInputs.length - 1) {
            otpInputs[index + 1].focus();
          }
        });

        input.addEventListener('paste', (e) => {
          e.preventDefault();
          const pastedData = (e.clipboardData || window.clipboardData).getData('text');
          const digits = pastedData.replace(/\D/g, '').split('');
          
          digits.forEach((digit, i) => {
            if (i < otpInputs.length) {
              otpInputs[i].value = digit;
            }
          });

          const otp = Array.from(otpInputs).map(i => i.value).join('');
          otpValue.value = otp;
          
          if (digits.length === otpInputs.length) {
            otpForm.submit();
          } else {
            otpInputs[Math.min(digits.length, otpInputs.length - 1)].focus();
          }
        });
      });

      // Resend timer
      function startResendTimer() {
        const resendBtn = document.querySelector('.resend-link');
        const timerDiv = document.getElementById('resend-timer');
        let timeLeft = 60;

        resendBtn.disabled = true;
        resendBtn.style.opacity = '0.5';
        resendBtn.style.pointerEvents = 'none';

        const interval = setInterval(() => {
          timeLeft--;
          if (timeLeft > 0) {
            timerDiv.textContent = `Coba lagi dalam ${timeLeft} detik`;
          } else {
            clearInterval(interval);
            resendBtn.disabled = false;
            resendBtn.style.opacity = '1';
            resendBtn.style.pointerEvents = 'auto';
            timerDiv.textContent = '';
          }
        }, 1000);
      }

      // Start timer on page load if needed
      if (document.querySelector('.resend-link')) {
        // Uncomment the line below to start timer automatically on page load
        // startResendTimer();
      }

      // Handle resend form submission
      document.getElementById('resend-form').addEventListener('submit', (e) => {
        e.preventDefault();
        // Add your resend OTP logic here
        startResendTimer();
      });
    </script>
  </body>
</html>
