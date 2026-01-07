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
        
    </div> 

    <section class="section">
        <div class="row">
            <div class="col-lg-12"> 
                <div class="card">
                    <div class="card-body">
                        <table id="categoryTable" class="table table-bordered">
                            <thead>
                            <tr>
                                <th scope="col">SL</th>
                                <th scope="col">Name</th>
                                <th scope="col">Mobile</th>
                               <th scope="col">Wallet Amount</th>
                                <th scope="col">Payin</th>
								 <th scope="col">Payout</th>
                                 <th scope="col">Paymode</th>
                                <th scope="col">Date</th>
								 <th scope="col">Status</th>
                               
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
								<td class="justified">{{ $user->id }}</td>
                                <td class="justified">{{ $user->full_name }}</td>
								 <td class="justified">{{ $user->phone }}</td>
								 <td class="justified">{{ $user->wallet_amount }}</td>
								 <td class="justified">{{ $user->payin }}</td>
								 <td class="justified">{{ $user->payout }}</td>
								 <td class="justified">{{ $user->paymode }}</td>
								 <td class="justified">{{ $user->created_at }}</td>
								 <td class="justified">Success</td>
                                
                               

                                
                                
                                
                               
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    
                    <!-- Pagination Links -->
                    <nav aria-label="Page navigation example">
                        <ul class="pagination justify-content-center">
                       
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

                           
                            @foreach ($users->getUrlRange(1, $users->lastPage()) as $page => $url)
                                <li class="page-item {{ $page == $users->currentPage() ? 'active' : '' }}">
                                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endforeach

                           
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
