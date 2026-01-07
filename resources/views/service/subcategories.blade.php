@extends('layouts.app') 
@section('app')

<body>
    <div class="pagetitle">
        <h1>Subcategory Details</h1> 
    </div> 

    <section class="section">
        <div class="row">
            <div class="col-lg-12"> 
                <div class="card">
                    <div class="card-body">
                        <a href="{{route('subcreates')}}" class="btn btn-primary"  style="position:relative;float:right;left:5px;">
                          Add Sub-Category </a>
						<br>
						<br>
                        <table id="categoryTable" class="table table-bordered">
                            <thead>
                            <tr>
                                <th scope="col">SL</th>
								<th scope="col">Image</th>
								<th scope="col">Name</th>
                                 <th scope="col">Category Name</th>
                                
                                
                              
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subcategories as $subcategorie)
                            <tr>
								 <td>{{ $subcategorie->id}}</td>
								<td>
                                    <img src="{{ $subcategorie->image }}" alt="Image" width="50" height="50">
                                </td>
                                  <td>{{ $subcategorie->name }}</td>
                                  <td>{{ $subcategorie->category_name}}</td>
                               
                                
                               

                                <td> 
                            
                      <!-- Edit button -->
                           <a href="{{ route('subcategorie.edit', $subcategorie->id) }}" class="btn btn-sm btn-warning" title="Edit">
                           <i class="fas fa-edit"></i> Edit
                            </a>
									<a href="{{ route('subCategory_delete', $subcategorie->id) }}" 
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
                    <!-- Add Bootstrap pagination links here -->
                    <nav aria-label="Page navigation example">
                        <ul class="pagination justify-content-center">
                            <!-- Previous Page Link -->
                            @if ($subcategories->onFirstPage())
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $subcategories->previousPageUrl() }}" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            @endif

                            <!-- Page Number Links -->
                            @foreach ($subcategories->getUrlRange(1, $subcategories->lastPage()) as $page => $url)
                                <li class="page-item {{ $page == $subcategories->currentPage() ? 'active' : '' }}">
                                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endforeach

                            <!-- Next Page Link -->
                            @if ($subcategories->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $subcategories->nextPageUrl() }}" aria-label="Next">
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
            </table>
       </div>
    </div>
</div>
   </section>
   @endsection