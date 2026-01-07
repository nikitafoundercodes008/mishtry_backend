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
        <h1>Settings List</h1> 

        <!-- ⭐ Add Slider Button -->
        
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
                                    <th scope="col">Image</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sliders as $side)
                                <tr>
                                    <td>{{$side->id}}</td>
                                    <td>
                                        <img src="{{ asset($side->image) }}" alt="slider Image" width="50" height="50">
                                    </td>

                                    <td>
                                        <a href="{{ route('slider.edit', $side->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>

                                        <a href="{{ route('sliders.toggleStatus', $side->id) }}" 
                                           class="btn btn-sm btn-danger"
                                           title="Delete"
                                           onclick="return confirm('Are you sure you want to delete this slider?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
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
    <div class="modal fade" id="" tabindex="-1">
        <div class="modal-dialog">
            <form action="" method="POST" enctype="multipart/form-data" class="modal-content">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Add New Slider</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <label>Image</label>
                    <input type="file" name="image" class="form-control" required>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>

@endsection
