@extends('layouts.app')

@section('app')

<!-- Custom Styles -->
<style>
    body {
        font-family: 'Roboto', sans-serif;
    }

    .pagetitle h1 {
        font-size: 36px;
        font-weight: bold;
        color: #333;
        margin-bottom: 20px;
    }
    
    .card {
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease-in-out;
    }
    
    .card:hover {
        transform: translateY(-10px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    }
    
    .card-icon {
        background-color: #f1f1f1;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        font-size: 22px;
        color: #333;
    }
    
    .card-body {
        padding: 5px;
        text-align: center;
    }
    
    .card-title {
        font-size: 18px;
        font-weight: 600;
        color: #5f6368;
        margin-bottom: 15px;
    }
    
    .card-body h6 {
        font-size: 30px;
        font-weight: 700;
        color: #333;
    }
    
    .section.dashboard .row {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }
    
    .col-xxl-3, .col-md-3, .col-sm-6 {
        flex: 1 1 23%;
    }
    
    .col-xxl-3 {
        flex: 1 1 23%;
    }

    /* Responsive tweaks */
    @media (max-width: 768px) {
        .col-xxl-3 {
            flex: 1 1 48%;
        }
    }

    @media (max-width: 576px) {
        .col-xxl-3 {
            flex: 1 1 100%;
        }
    }
    
    
</style>

<!-- Page Title -->
<div class="pagetitle">
   <h2 style="color:red;">Dashboard</h2>
</div>
<!-- End Page Title -->

<section class="section dashboard">
    <div class="row">
        <!-- Left Side Columns -->
        <div class="col-lg-12">
            <div class="row">
                <!-- Users Card -->
                <div class="col-xxl-3 col-md-3 col-sm-6 mb-4">
                    <div class="card info-card sales-card">
                        <a href="https://admin.mishtiry.com/users" style="text-decoration:none; color:inherit;">
                         <div class="card-body">
                         <h5 class="card-title">Users</h5>
                         <div class="d-flex align-items-center">
                         <div class="card-icon d-flex align-items-center justify-content-center">
                         <img src="{{ asset('public/gif/management-consulting.gif') }}" alt="" style="max-width: 40px; height: auto;">
                     </div>
                  <div class="ps-3">
                <h6>{{ $userCount }}</h6>
            </div>
        </div>
    </div>
</a>

                    </div>
                </div>

                                <!-- Services Card -->
                                
                <div class="col-xxl-3 col-md-3 col-sm-6 mb-4">
    <a href="https://admin.mishtiry.com/services" style="text-decoration:none; color:inherit;">
        <div class="card info-card revenue-card" style="cursor:pointer;">
            <div class="card-body">
                <h5 class="card-title">Services</h5>
                <div class="d-flex align-items-center">
                    <div class="card-icon d-flex align-items-center justify-content-center">
                        <img src="{{ asset('public/gif/courier.gif') }}" alt="" style="max-width: 40px; height: auto;">
                    </div>
                    <div class="ps-3">
                        <h6>{{ $serviceCount }}</h6>
                    </div>
                </div>
            </div>
        </div>
    </a>
</div>



                <!-- Bookings Card -->
                <div class="col-xxl-3 col-md-3 col-sm-6 mb-4">
    <a href="https://admin.mishtiry.com/bookings" style="text-decoration:none; color:inherit;">
        <div class="card info-card sales-card" style="cursor:pointer;">
            <div class="card-body">
                <h5 class="card-title">Bookings</h5>
                <div class="d-flex align-items-center">
                    <div class="card-icon d-flex align-items-center justify-content-center">
                        <img src="{{ asset('public/gif/box.gif') }}" alt="" style="max-width: 40px; height: auto;">
                    </div>
                    <div class="ps-3">
                        <h6>{{ $bookingCount }}</h6>
                    </div>
                </div>
            </div>
        </div>
    </a>
</div>


                <!-- Providers Card -->
               <div class="col-xxl-3 col-md-3 col-sm-6 mb-4">
    <a href="https://admin.mishtiry.com/providers" style="text-decoration:none; color:inherit;">
        <div class="card info-card revenue-card" style="cursor:pointer;">
            <div class="card-body">
                <h5 class="card-title">Providers</h5>
                <div class="d-flex align-items-center">
                    <div class="card-icon d-flex align-items-center justify-content-center">
                        <img src="{{ asset('public/gif/engineer.gif') }}" alt="" style="max-width: 40px; height: auto;">
                    </div>
                    <div class="ps-3">
                        <h6>{{ $providerCount }}</h6>
                    </div>
                </div>
            </div>
        </div>
    </a>
</div>


                <!-- Handymans Card -->
               <div class="col-xxl-3 col-md-3 col-sm-6 mb-4">
    <a href="https://admin.mishtiry.com/handymans" style="text-decoration:none; color:inherit;">
        <div class="card info-card customers-card" style="cursor:pointer;">
            <div class="card-body">
                <h5 class="card-title">Handymans</h5>
                <div class="d-flex align-items-center">
                    <div class="card-icon d-flex align-items-center justify-content-center">
                        <img src="{{ asset('public/gif/work.gif') }}" alt="" style="max-width: 40px; height: auto;">
                    </div>
                    <div class="ps-3">
                        <h6>{{ $handymanCount }}</h6>
                    </div>
                </div>
            </div>
        </div>
    </a>
</div>


                <!-- Pending Verification Card -->
               <div class="col-xxl-3 col-md-3 col-sm-6 mb-4">
    <a href="" style="text-decoration:none; color:inherit;">
        <div class="card info-card customers-card" style="cursor:pointer;">
            <div class="card-body">
                <h5 class="card-title">Pending Verification</h5>
                <div class="d-flex align-items-center">
                    <div class="card-icon d-flex align-items-center justify-content-center">
                        <img src="{{ asset('public/gif/clipboard.gif') }}" alt="" style="max-width: 40px; height: auto;">
                    </div>
                    <div class="ps-3">
                        <h6>{{ $providerPendingCount }}</h6>
                    </div>
                </div>
            </div>
        </div>
    </a>
</div>


                <!-- Tax Collection Card -->
              <div class="col-xxl-3 col-md-3 col-sm-6 mb-4">
    <a href="" style="text-decoration:none; color:inherit;">
        <div class="card info-card customers-card" style="cursor:pointer;">
            <div class="card-body">
                <h5 class="card-title">Total Tax Collection</h5>
                <div class="d-flex align-items-center">
                    <div class="card-icon d-flex align-items-center justify-content-center">
                        <img src="{{ asset('public/gif/profit.gif') }}" alt="" style="max-width: 40px; height: auto;">
                    </div>
                    <div class="ps-3">
                        <h6>{{ $taxSum }}₹</h6>
                    </div>
                </div>
            </div>
        </div>
    </a>
</div>


                <!-- Total Income Card -->
                <div class="col-xxl-3 col-md-3 col-sm-6 mb-4">
    <a href="https://admin.mishtiry.com/transaction_details" style="text-decoration:none; color:inherit;">
        <div class="card info-card customers-card" style="cursor:pointer;">
            <div class="card-body">
                <h5 class="card-title">Total Income</h5>
                <div class="d-flex align-items-center">
                    <div class="card-icon d-flex align-items-center justify-content-center">
                        <img src="{{ asset('public/gif/bank.gif') }}" alt="" style="max-width: 40px; height: auto;">
                    </div>
                    <div class="ps-3">
                        <h6>{{ $totalAmount }}₹</h6>
                    </div>
                </div>
            </div>
        </div>
    </a>
</div>


            </div>
        </div>
        <!-- End Left Side Columns -->
    </div>
</section>

<!-- Include Bootstrap JS (via CDN) -->

@endsection
