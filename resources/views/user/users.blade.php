@extends('layouts.app') 
@section('app')

<head>
    <style>
        .justified {
            text-align: justify;
        }
    </style>
</head>

<body>
    <div class="pagetitle">
        <h1>Users List</h1> 
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
    <th>Wallet Amount</th>

    <th>Bookings</th>
    <th>Transactions</th>
    <th>Status</th>
</tr>
</thead>

                       <tbody>
@foreach($users as $user)
<tr>
    <td>{{ $user->id }}</td>

    <td>
        <img src="{{ $user->image ? asset($user->image) : 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQTUNJcNcqAWSnqD-bAyvNm2fZwerWacGslJDIN7Ec6UjOCqQD2zukRsys&s' }}"
             width="50" height="50">
    </td>

    <td>{{ $user->full_name }}</td>
    <td>{{ $user->email }}</td>
    <td>{{ $user->phone }}</td>
    <td>{{ $user->address }}</td>
    <td>₹ {{ $user->wallet_amount }}</td>

    {{-- BOOKINGS COUNT --}}
   <td>
@php
    $count = DB::table('bookings')
        ->where('user_id', $user->id)
        ->count();
@endphp

<a href="{{ route('showBookings_users', $user->id) }}" class="badge bg-info">
    {{ $count }}
</a>
</td>


    {{-- TRANSACTIONS COUNT --}}
   <td>
@php
    $count = DB::table('transaction_details')
        ->where('user_id', $user->id)
        ->count();
@endphp

<a href="{{ route('transaction_details_users', $user->id) }}"
   class="badge bg-primary">
    {{ $count }}
</a>
</td>


    {{-- STATUS --}}
    <td>
        <form action="{{ route('userstoggleStatus', $user->id) }}" method="POST">
            @csrf
            <button class="btn btn-sm {{ $user->status ? 'btn-success' : 'btn-danger' }}">
                {{ $user->status ? 'Active' : 'Inactive' }}
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
                            @if ($users->onFirstPage())
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $users->previousPageUrl() }}" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            @endif

                            <!-- Page Number Links -->
                            @foreach ($users->getUrlRange(1, $users->lastPage()) as $page => $url)
                                <li class="page-item {{ $page == $users->currentPage() ? 'active' : '' }}">
                                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endforeach

                            <!-- Next Page Link -->
                            @if ($users->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $users->nextPageUrl() }}" aria-label="Next">
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
