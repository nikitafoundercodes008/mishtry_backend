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

/* When the input is checked */
input:checked + .slider {
    background-color: #2196F3;
}

/* Move the slider when checked */
input:checked + .slider:before {
    transform: translateX(14px);
}

/* Add a transition effect to the background-color */
.slider.round {
    border-radius: 50px;
}
</style>
<body>
    <div class="pagetitle">
        <h1>Category List</h1> 
    </div> 

    <section class="section">
        <div class="row">
            <div class="col-lg-12"> 
                <div class="card">
                    <div class="card-body">
                        <a href="{{route('creates')}}" class="btn btn-primary"  style="position:relative;float:right;left:5px;">
                          Add Category </a>
                       <br>
						 <br>
						 
						
                        <table id="categoryTable" class="table table-bordered">
                            <thead>
                            <tr>
                                <th scope="col">SL</th>
                                
                                <th scope="col">Image</th>
								<th scope="col">Name</th>
                               
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $category)
                            <tr>
                                <td>{{ $category->id }}</td>
                               
                                <td>
                                    <img src="{{ asset($category->image) }}" alt="Category Image" width="50" height="50">
                                </td>
								 <td>{{ $category->name }}</td>
                              
                                <td>
                      <!-- Edit button -->
                           <a href="{{ route('category.edit', $category->id) }}" class="btn btn-sm btn-warning" title="Edit">
                           <i class="fas fa-edit"></i> Edit
                            </a>
							<a href="{{ route('Category_delete', $category->id) }}" 
                                           class="btn btn-sm btn-danger"
                                           title="Delete"
                                           onclick="return confirm('Are you sure you want to delete this category?');">
                                            <i class="fas fa-trash"></i>
                                        </a>		
                            </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Pagination Links -->
                    <nav aria-label="Page navigation example">
                        <ul class="pagination justify-content-center">
                            <!-- Previous Page Link -->
                            @if ($categories->onFirstPage())
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $categories->previousPageUrl() }}" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            @endif

                            <!-- Page Number Links -->
                            @for ($page = 1; $page <= $categories->lastPage(); $page++)
                                <li class="page-item {{ $page == $categories->currentPage() ? 'active' : '' }}">
                                    <a class="page-link" href="{{ $categories->url($page) }}">{{ $page }}</a>
                                </li>
                            @endfor

                            <!-- Next Page Link -->
                            @if ($categories->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $categories->nextPageUrl() }}" aria-label="Next">
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

@endsection

