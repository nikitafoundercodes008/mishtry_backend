@extends('layouts.app') 
@section('app')
<style>
    /* Style the switch */
    .switch {
        position: relative;
        display: inline-block;
        width: 34px;
        height: 20px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 50px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 12px;
        width: 12px;
        border-radius: 50px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
    }

    input:checked + .slider {
        background-color: #2196F3;
    }

    input:checked + .slider:before {
        transform: translateX(14px);
    }

    .slider.round {
        border-radius: 50px;
    }
</style>

<body>
    <div class="pagetitle">
        <h1>Zone List</h1> 

        <!-- ⭐ Add Slider Button -->
       <a href="https://admin.mishtiry.com/zones/create" class="btn btn-primary" style="float:right;">
    + Add Zone
</a>

    </div> 

    <section class="section">
        <div class="row">
            <div class="col-lg-12"> 
                <div class="card">
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Zone Name</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sliders as $side)
                                <tr>
                                    <td>{{$side->id}}</td>
                                    <td>{{$side->zone_name}}</td>

                                    <td>
    <form action="" method="POST" onsubmit="return confirm('Are you sure you want to delete this zone?');">
        @csrf
        @method('DELETE')
        <button class="btn btn-danger btn-sm">
            Delete
        </button>
    </form>
</td>

                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <nav aria-label="Page navigation example">
                            <ul class="pagination justify-content-center">
                                @if ($sliders->onFirstPage())
                                    <li class="page-item disabled">
                                        <a class="page-link">&laquo;</a>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="">&laquo;</a>
                                    </li>
                                @endif

                                @for ($page = 1; $page <= $sliders->lastPage(); $page++)
                                    <li class="page-item {{ $page == $sliders->currentPage() ? 'active' : '' }}">
                                        <a class="page-link" href="{{ $sliders->url($page) }}">{{ $page }}</a>
                                    </li>
                                @endfor

                                @if ($sliders->hasMorePages())
                                    <li class="page-item">
                                        <a class="page-link" href="">&raquo;</a>
                                    </li>
                                @else
                                    <li class="page-item disabled">
                                        <a class="page-link">&raquo;</a>
                                    </li>
                                @endif
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ⭐ Add Slider Popup Modal -->
   
@endsection