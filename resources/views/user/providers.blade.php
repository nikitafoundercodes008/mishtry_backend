@extends('layouts.app')

@section('app')

<body>
    <div class="pagetitle">
        <h1>Providers List</h1>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="categoryTable" class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>SL</th>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Zone</th>
                                        <th>Shop</th>
                                        <th>Status</th>
                                        <th>Bookings</th>
                                        <th>Handymans</th>
                                        <th>Commission</th>
                                        <th>Transaction</th>
                                        <th>DocView</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($providers as $provider)
                                    <tr>
                                        <td>{{ $provider->id }}</td>
                                        <td>
                                            <img src="{{ $provider->image ? asset($provider->image) : 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQTUNJcNcqAWSnqD-bAyvNm2fZwerWacGslJDIN7Ec6UjOCqQD2zukRsys&s' }}" class="rounded-circle" width="50" height="50">
                                        </td>
                                        <td>{{ $provider->full_name }}</td>
                                        <td>{{ $provider->email }}</td>
                                        <td>{{ $provider->phone }}</td>
                                        <td>{{ $provider->zone_management }}</td>
                                        <td>No Shop</td>

                                        <!-- Status -->
                                        <td>
                                            <form action="{{ route('userstoggleStatus', $provider->id) }}" method="POST">
                                                @csrf
                                                <button class="btn btn-sm {{ $provider->status ? 'btn-success' : 'btn-danger' }}">
                                                    {{ $provider->status ? 'Active' : 'Inactive' }}
                                                </button>
                                            </form>
                                        </td>

                                        <!-- Bookings (click count) -->
                       
 <td>
@php
    $count = DB::table('bookings')
        ->where('provideo_id', $provider->id)
        ->count();
@endphp
<a href="{{ route('showBookings_users_provideo', $provider->id) }}" class="badge bg-info">
    {{ $count }}
</a>
</td>
										


                                        <!-- Handymans -->
                                        <td>
											@php
    $count = DB::table('user_details')
        ->where('provideo_id', $provider->id)
        ->count();
@endphp
                                            <a href="{{ route('handymanslist', $provider->id) }}"
                                               class="badge bg-secondary">
                                               {{ $count }}
                                            </a>
                                        </td>

                                        <!-- Commission -->
                                        <td>
                                         	@php
    $count = DB::table('transaction_details')
        ->where('user_id', $provider->id)
        ->count();
@endphp
                                            <a href="{{ route('transaction_details_users', $provider->id) }}"
                                               class="badge bg-warning text-dark">
                                                 {{ $count }}
                                            </a>
                                        </td>

                                        <!-- Transaction -->
                                        <td>
                                        	@php
    $count = DB::table('transaction_details')
        ->where('user_id', $provider->id)
        ->count();
@endphp
                                            <a href="{{ route('transaction_details_users', $provider->id) }}"
                                               class="badge bg-info">
                                                {{ $count }}
                                            </a>
                                        </td>

                                        <!-- DocView -->
                                        <td>
                                            <form action="{{ route('providerdocview', $provider->id) }}" method="POST">
                                                @csrf
                                                <button class="btn btn-sm btn-primary"
                                                    {{ $provider->verification_status == '4' ? 'disabled' : '' }}>
                                                    View
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <!-- Pagination Links -->
                            <nav aria-label="Page navigation example">
                                <ul class="pagination justify-content-center">
                                    <!-- Previous Page Link -->
                                    @if ($providers->onFirstPage())
                                    <li class="page-item disabled">
                                        <a class="page-link" href="#" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                    @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $providers->previousPageUrl() }}" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                    @endif

                                    <!-- Page Number Links -->
                                    @foreach ($providers->getUrlRange(1, $providers->lastPage()) as $page => $url)
                                    <li class="page-item {{ $page == $providers->currentPage() ? 'active' : '' }}">
                                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                    </li>
                                    @endforeach

                                    <!-- Next Page Link -->
                                    @if ($providers->hasMorePages())
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $providers->nextPageUrl() }}" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                    @else
                                    <li class="page-item disabled">
                                        <a class="page-link" href="#" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                    @endif
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- JavaScript को अलग script टैग में रखें -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // सभी booking-count elements को fetch करें
        document.querySelectorAll('.booking-count').forEach(element => {
            const providerId = element.dataset.providerId;
            
            // AJAX request भेजें
            fetch(`/booking_count/${providerId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    // Count display करें
                    element.textContent = data.count;
                })
                .catch(error => {
                    console.error('Error fetching booking count:', error);
                    element.textContent = '0'; // Error होने पर 0 show करें
                });
        });
    });
    </script>

    <style>
    .commission-box {
        background: #0d6efd;
        color: #fff;
        border: none;
        border-radius: 6px;
        padding: 8px 14px;
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        position: relative;
        font-size: 13px;
    }

    .commission-box:hover {
        background: #0b5ed7;
    }

    .commission-count {
        background: #dc3545;
        color: #fff;
        border-radius: 50%;
        width: 22px;
        height: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: bold;
    }
    </style>
</body>

@endsection