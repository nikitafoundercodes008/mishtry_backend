@extends('layouts.app') 
@section('app')

<body>
    <div class="pagetitle">
        <h1>Bookings Details</h1> 
    </div> 

    <section class="section">
        <div class="row">
            <div class="col-lg-12"> 
                <div class="card">
                    <div class="card-body">
                        <table id="categoryTable" class="table table-bordered">
                            <thead>
                            <tr>
                                <th scope="col">booking_id</th>
                                <th scope="col">user_id</th>
                                <th scope="col">quantity</th>
                                <th scope="col">service_id</th>
                                <th scope="col">address</th>
                                <th scope="col">description</th>
                                <th scope="col">booking_date</th>
                                <th scope="col">price</th>
                                <th scope="col">discount%</th>
                                <th scope="col">sub_total</th>
                                <th scope="col">tax(GST18%)</th>
                                <th scope="col">total_amount</th>
                                <th scope="col">status</th>
                                <th scope="col">payment_through</th>
                                <th scope="col">handyman_id</th>
                                <th scope="col">transaction_id</th>
                                <th scope="col">transaction_status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bookings as $booking)
                            <tr>
                                <td>{{ $booking->id }}</td>
                                 <td>{{ $booking->user_name }}</td>
                                <td>{{ $booking->quantity }}</td>
                                <td>{{ $booking->service_name }}</td>
                                <td>{{ $booking->address}}</td> 
                                <td>{{ $booking->description}}</td>
                                <td>{{ $booking->booking_date}}</td>
                                <td>{{ '₹'. $booking->price }}</td>
                                <td>{{ $booking->discount}}</td>
                                <td>{{ $booking->sub_total }}</td>
                                <td>{{ $booking->tax}}</td>
                                <td>{{ '₹'. $booking->total_amount}}</td>
                                
                                <!-- Status Mapping (0: Pending, 1: Accept, 2: Assign, 3: Completed, 4: Decline) -->
                                <td>
                                    @switch($booking->status)
                                        @case(0)
                                            <span class="badge bg-primary">Pending</span>
                                            @break
                                        @case(1)
                                            <span class="badge bg-info">Accept</span>
                                            @break
                                        @case(2)
                                            <span class="badge bg-dark">Assign</span>
                                            @break
                                        @case(3)
                                            <span class="badge bg-success">Completed</span>
                                            @break
                                        @case(4)
                                            <span class="badge bg-danger">Decline</span>
                                           @break
                                        @default
                                            Unknown
                                    @endswitch
                                </td>
                                
                                <td> @switch($booking->payment_through)
                                      @case(1)
                                      <span class="badge bg-primary">INDIANPAY</span>
                                      @break
                                @case(2)
                                <span class="badge bg-primary">WALLET</SPAN>
                                 @break
                                        @default
                                            Unknown
                                    @endswitch
                                </td>
                                
                                <td>{{ $booking->handyman_name}}</td>
                                <td>{{ $booking->transaction_id}}</td>
                                <td>@switch($booking->transaction_status)
                                   @case(1)
                                   <span class="badge bg-primary">PENDING</span>
                                   @break
                                   @case(2)
                                   <span class="badge bg-primary">COMPLETED</span>
                                   @break
                                   @case(3)
                                   <span class="badge bg-primary">CANCEL</span>
                                   @break
                                        @default
                                            Unknown
                                    @endswitch
                                
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                     <!--Bootstrap pagination links -->
                    <nav aria-label="Page navigation example">
                        <ul class="pagination justify-content-center">
                             <!--Previous Page Link -->
                            @if ($bookings->onFirstPage())
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $bookings->previousPageUrl() }}" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            @endif

                             <!--Page Number Links -->
                            @foreach ($bookings->getUrlRange(1, $bookings->lastPage()) as $page => $url)
                                <li class="page-item {{ $page == $bookings->currentPage() ? 'active' : '' }}">
                                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endforeach

                             <!--Next Page Link -->
                            @if ($bookings->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $bookings->nextPageUrl() }}" aria-label="Next">
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
