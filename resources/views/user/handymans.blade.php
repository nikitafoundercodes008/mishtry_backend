@extends('layouts.app') 
@section('app')

<body>
    <div class="pagetitle">
        <h1>Handymans List</h1> 
    </div> 

    <section class="section">
        <div class="row">
            <div class="col-lg-12"> 
                <div class="card">
                    <div class="card-body">
                        <table id="categoryTable" class="table table-bordered">
                            <thead>
<tr>
    <th>SL</th>
    <th>Image</th>
    <th>Name</th>
    <th>Email</th>
    <th>Phone</th>
    <th>Address</th>
    <th>Zone</th>
    <th>Provider Name</th>
    <th>Provider Mobile</th>

    <th>Bookings</th>
    <th>Commission</th>
    <th>Transactions</th>
    <th>Documents</th>
    <th>Status</th>
</tr>
</thead>

                        <tbody>
@foreach($handymans as $handyman)
<tr>
    <td>{{ $handyman->id }}</td>

    <td>
        <img src="{{ $handyman->image ? asset($handyman->image) : 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQTUNJcNcqAWSnqD-bAyvNm2fZwerWacGslJDIN7Ec6UjOCqQD2zukRsys&s' }}"
             class="rounded-circle" width="50" height="50">
    </td>

    <td>{{ $handyman->full_name }}</td>
    <td>{{ $handyman->email }}</td>
    <td>{{ $handyman->phone }}</td>
    <td>{{ $handyman->address }}</td>
    <td>{{ $handyman->zone_management }}</td>
    <td>{{ $handyman->provider_name }}</td>
    <td>{{ $handyman->provider_mobile }}</td>

    {{-- BOOKINGS COUNT --}}
    <td>
        <a href="{{ route('showBookings', $handyman->id) }}"
           class="badge bg-dark">
            {{ $handyman->booking_count ?? 0 }}
        </a>
    </td>

    {{-- COMMISSION --}}
    <td>
    @php
    $count = DB::table('transaction_details')
        ->where('user_id', $handyman->id)
        ->count();
@endphp
        <a href="{{ route('transaction_details_users', $handyman->id) }}"
           class="badge bg-primary">
              {{ $count }}
        </a>
    </td>

  
    <td>
     @php
    $count = DB::table('transaction_details')
        ->where('user_id', $handyman->id)
        ->count();
@endphp
        <a href="{{ route('transaction_details_users', $handyman->id) }}"
           class="badge bg-info">
              {{ $count }}
        </a>
    </td>

    {{-- DOCUMENTS --}}
    <td>
        <form action="{{ route('providerdocview', $handyman->id) }}" method="POST">
            @csrf
            <button class="btn btn-sm btn-warning"
                {{ $handyman->verification_status == '4' ? 'disabled' : '' }}>
                DocView
            </button>
        </form>
    </td>

    {{-- STATUS --}}
    <td>
        <form action="{{ route('userstoggleStatus', $handyman->id) }}" method="POST">
            @csrf
            <button class="btn btn-sm {{ $handyman->status ? 'btn-success' : 'btn-danger' }}">
                {{ $handyman->status ? 'Active' : 'Inactive' }}
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
                            @if ($handymans->onFirstPage())
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $handymans->previousPageUrl() }}" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            @endif

                            <!-- Page Number Links -->
                            @foreach ($handymans->getUrlRange(1, $handymans->lastPage()) as $page => $url)
                                <li class="page-item {{ $page == $handymans->currentPage() ? 'active' : '' }}">
                                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endforeach

                            <!-- Next Page Link -->
                            @if ($handymans->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $handymans->nextPageUrl() }}" aria-label="Next">
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
   @endsection


