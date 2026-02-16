<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@yield('title', 'Purple Admin')</title>
    
    <!-- plugins:css -->
    <link rel="stylesheet" href="{{ asset('vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/ti-icons/css/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/font-awesome/css/font-awesome.min.css') }}">
    <!-- endinject -->
    
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="{{ asset('vendors/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
    <!-- End plugin css for this page -->
    
    <!-- inject:css -->
    <!-- endinject -->
    
    <!-- Layout styles -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <!-- End layout styles -->
    
    <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}" />
    
    @yield('extra-css')
</head>

<body>
    <div class="container-scroller">
        <!-- Pro Banner -->
        <div class="row p-0 m-0 proBanner" id="proBanner" style="display:none;">
            <div class="col-md-12 p-0 m-0">
                <div class="card-body card-body-padding d-flex align-items-center justify-content-between">
                    <div class="ps-lg-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <p class="mb-0 font-weight-medium me-3 buy-now-text">Welcome to Purple Admin Dashboard</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <button id="bannerClose" class="btn border-0 p-0">
                            <i class="mdi mdi-close text-white mr-0"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Navbar -->
        @include('layouts.partials.navbar')
        
        <!-- Main Content -->
        <div class="container-fluid page-body-wrapper">
            
            <!-- Sidebar -->
            @include('layouts.partials.sidebar')
            
            <!-- Content Wrapper -->
            <div class="main-panel">
                <div class="content-wrapper">
                    @yield('content')
                </div>
                
                <!-- Footer -->
                @include('layouts.partials.footer')
            </div>
            <!-- main-panel ends -->
        </div>
        <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->
    
    <!-- plugins:js -->
    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
    <!-- endinject -->
    
    <!-- Plugin js for this page -->
    <script src="{{ asset('vendors/chart.js/chart.umd.js') }}"></script>
    <script src="{{ asset('vendors/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
    <!-- End plugin js for this page -->
    
    <!-- inject:js -->
    <script src="{{ asset('js/off-canvas.js') }}"></script>
    <script src="{{ asset('js/misc.js') }}"></script>
    <script src="{{ asset('js/settings.js') }}"></script>
    <script src="{{ asset('js/todolist.js') }}"></script>
    <script src="{{ asset('js/jquery.cookie.js') }}"></script>
    <!-- endinject -->
    
    @yield('extra-js')
</body>
</html>
