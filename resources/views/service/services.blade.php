@extends('layouts.app')
@section('app')

<body>

    <div class="pagetitle d-flex justify-content-between">
        <h1>Services List</h1>

        <!-- Add Button -->
		
       <div class="d-flex justify-content-end gap-2">
    
    

    <!-- Add Service Modal Button -->
    <button  type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
        + Add Service
    </button>
</div>

    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">

                <div class="card">
                    <div class="card-body">

                        <div class="table-responsive" style="overflow-x: auto;">
                            <table id="categoryTable" class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>SL</th>
                                      
                                        <th>Services Name</th>
                                        <th>Description</th>
                                        <th>Image</th>
                                        <th>Price</th>
                                        <th>Discount</th>
                                        <th>Duration</th>
                                        <th>Provider Name</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach($services as $service)
                                    <tr>
                                        <td>{{ $service->id }}</td>
                                       
                                        <td>{{ $service->name }}</td>
                                        <td>
                                            @php
                                                $short = Str::limit($service->description, 20);
                                            @endphp

                                            <span class="short-desc">{{ $short }}</span>
                                            <span class="full-desc d-none">{{ $service->description }}</span>

                                            @if(strlen($service->description) > 40)
                                                <a href="javascript:void(0)" class="text-primary read-more">More</a>
                                                <a href="javascript:void(0)" class="text-primary read-less d-none">Less</a>
                                            @endif
                                        </td>

                                        <td><img src="{{ $service->image }}" width="50" height="50"></td>

                                        <td>{{ '₹'.$service->price }}</td>
                                        <td>{{ '%'.$service->discount }}</td>
                                        <td>{{ $service->duration }}</td>

                                        <td>Admin</td>

                                        <td>
                                            <!-- Edit Button -->
                                           
                                            <form action="{{ route('services.toggleStatus', $service->id) }}" 
                                                  method="POST" style="display:inline-block;">
                                                @csrf
                                                <button type="submit" 
                                                    class="btn btn-sm {{ $service->status ? 'btn-success' : 'btn-danger' }}">
                                                    {{ $service->status ? 'active' : 'inactive' }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <nav aria-label="Page navigation example">
                            <ul class="pagination justify-content-center">
                                @if ($services->onFirstPage())
                                    <li class="page-item disabled"><a class="page-link">&laquo;</a></li>
                                @else
                                    <li class="page-item"><a class="page-link" href="{{ $services->previousPageUrl() }}">&laquo;</a></li>
                                @endif

                                @foreach ($services->getUrlRange(1, $services->lastPage()) as $page => $url)
                                    <li class="page-item {{ $page == $services->currentPage() ? 'active' : '' }}">
                                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                    </li>
                                @endforeach

                                @if ($services->hasMorePages())
                                    <li class="page-item"><a class="page-link" href="{{ $services->nextPageUrl() }}">&raquo;</a></li>
                                @else
                                    <li class="page-item disabled"><a class="page-link">&raquo;</a></li>
                                @endif

                            </ul>
                        </nav>

                    </div>
                </div>

            </div>
        </div>
    </section>

</body>

<!-- ADD SERVICE POPUP -->
<div class="modal fade" id="addServiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Add New Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('storeservices') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="modal-body">
                    <div class="mb-3">
    <label class="form-label">Select Subcategory</label>
    <select class="form-control" name="subcategory_id" required>
        <option value="">Select Subcategory</option>

        @foreach($services as $category)
            <option value="{{ $category->id }}"
                data-category="{{ $category->subcategory_id }}">
                {{ $category->name }}
            </option>
        @endforeach

    </select>
</div>

                      <div class="col-md-12 mb-3">
                        <label>Zone</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <!-- Service Name -->
                    <div class="col-md-12 mb-3">
                        <label>Service Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <!-- Description -->
                    <div class="col-md-12 mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3" required></textarea>
                    </div>

                    <div class="row">

                        <!-- Price -->
                        <div class="col-md-4 mb-3">
                            <label>Price</label>
                            <input type="number" name="price" class="form-control" required>
                        </div>

                        <!-- Discount -->
                        <div class="col-md-4 mb-3">
                            <label>Discount (%)</label>
                            <input type="number" name="discount" class="form-control">
                        </div>

                        <!-- Duration -->
                        <div class="col-md-4 mb-3">
                            <label>Duration</label>
                            <input type="text" name="duration" class="form-control" required>
                        </div>

                    </div>

                    <!-- Tax -->
                    <div class="col-md-12 mb-3">
                        <label>Tax (%)</label>
                        <input type="number" name="tax" class="form-control" required>
                    </div>

                    <!-- Image -->
                    <div class="col-md-12 mb-3">
                        <label>Image</label>
                        <input type="file" name="image" class="form-control" required>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary">Save</button>
                </div>

            </form>

        </div>
    </div>
</div>

<script>
document.querySelector("select[name='subcategory_id']").addEventListener("change", function () {
    let categoryId = this.options[this.selectedIndex].getAttribute("data-category");
    document.getElementById("category_id").value = categoryId;
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".read-more").forEach(btn => {
        btn.addEventListener("click", function () {
            let td = this.closest("td");
            td.querySelector(".short-desc").classList.add("d-none");
            td.querySelector(".full-desc").classList.remove("d-none");
            td.querySelector(".read-more").classList.add("d-none");
            td.querySelector(".read-less").classList.remove("d-none");
        });
    });

    document.querySelectorAll(".read-less").forEach(btn => {
        btn.addEventListener("click", function () {
            let td = this.closest("td");
            td.querySelector(".short-desc").classList.remove("d-none");
            td.querySelector(".full-desc").classList.add("d-none");
            td.querySelector(".read-less").classList.add("d-none");
            td.querySelector(".read-more").classList.remove("d-none");
        });
    });
});
</script>

@endsection
