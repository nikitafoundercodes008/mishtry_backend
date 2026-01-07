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
        <h1>Reviews List</h1> 
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
                                <th scope="col">Services Rating</th>
                                <th scope="col">Comment</th>
                               <th scope="col">Services Name</th>
                                <th scope="col">Handyman Name</th>
								 <th scope="col">Users Name</th>
                                
                                <th scope="col">Services Image</th>
                               
								 
                                <th scope="col">Date</th>
								 <th scope="col">Action</th>
                               
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
                                <td class="justified">{{ $user->id }}</td>
                                <td class="justified">
    @php
        $filledStars = $user->rate; // kitne filled ⭐
        $emptyStars = 5 - $filledStars; // remaining ☆
    @endphp

    {!! str_repeat('⭐', $filledStars) !!}{!! str_repeat('☆', $emptyStars) !!}
</td>
                                <td class="justified">{{ $user->comment }}</td>
								<td class="justified">{{ $user->service_name }}</td>
                                <td class="justified">{{ $user->handyman_full_name }}</td>
								<td class="justified">{{ $user->user_full_name }}</td>
								<td>
                                    <img src="{{ asset($user->service_image) }}" alt="user Image" width="50" height="50">
                                </td>
								<td class="justified">{{ $user->created_at }}</td>
								  <td>
    <div class="form-button-action">
        <a href="{{ url('/reviews_delete/'.$user->id ) }}" 
           class="btn btn-danger delete-btn" 
           data-id="{{ $user->id }}" 
           style="margin-left:10px;">
            <i class="fa fa-trash"></i>
        </a>
    </div>
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
