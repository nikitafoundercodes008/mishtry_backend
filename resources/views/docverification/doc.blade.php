@extends('layouts.app') 
@section('app')

<body>
    <div class="pagetitle">
        <h1>Document verification</h1> 
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
                                <th scope="col">User_id</th>
                                <th scope="col">User_name</th>
                                <th scope="col">Doc Type</th>
                                <th scope="col">Document</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($papers as $doc)
                            <tr>
                                <td>{{ $doc->id }}</td>
                                <td>{{ $doc->user_id }}</td>
                                <td>{{ $doc->full_name }}</td> 
                                <td>{{ $doc->type }}</td>
                                <td>
                                    <!-- Image with a click event to open modal -->
                                    <img src="{{ $doc->image }}" alt="Image" width="50" height="50" 
                                         data-bs-toggle="modal" data-bs-target="#imageModal" data-bs-image="{{ $doc->image }}">
                                </td>
                                <td>
                                    <!-- Separate Approve and Reject buttons -->
                                    <form action="{{ route('documents.approve', $doc->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" 
                                            {{ $doc->status == 2 ? 'disabled' : '' }}>
                                            Approve
                                        </button>
                                    </form>

                                    <form action="{{ route('documents.reject', $doc->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger" 
                                            {{ $doc->status == 3 ? 'disabled' : '' }}>
                                            Reject
                                        </button>
                                    </form>

                                    <!-- Status Display -->
                                    <span>
                                        {{ $doc->status == 1 ? 'Pending !' : ($doc->status == 2 ? 'approved ✔' : 'rejected ×' ) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <!-- Add Bootstrap pagination links here -->
                    <nav aria-label="Page navigation example">
                        <ul class="pagination justify-content-center">
                            <!-- Previous Page Link -->
                            @if ($papers->onFirstPage())
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $papers->previousPageUrl() }}" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            @endif

                            <!-- Page Number Links -->
                            @foreach ($papers->getUrlRange(1, $papers->lastPage()) as $page => $url)
                                <li class="page-item {{ $page == $papers->currentPage() ? 'active' : '' }}">
                                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endforeach

                            <!-- Next Page Link -->
                            @if ($papers->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $papers->nextPageUrl() }}" aria-label="Next">
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

   <!-- Modal for Image Preview -->
   <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
       <div class="modal-dialog modal-dialog-centered">
           <div class="modal-content">
               <div class="modal-header">
                   <h5 class="modal-title" id="imageModalLabel">Document Image</h5>
                   <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
               </div>
               <div class="modal-body">
                   <!-- Image in Modal -->
                   <img id="modalImage" src="" alt="Document Image" class="img-fluid w-100">
               </div>
           </div>
       </div>
   </div>
</body>

<script>
    // Bootstrap modal image update
    const imageModal = document.getElementById('imageModal');
    imageModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget; // Button that triggered the modal
        const imageUrl = button.getAttribute('data-bs-image'); // Extract image URL from data-bs-image
        const modalImage = document.getElementById('modalImage');
        modalImage.src = imageUrl; // Set the image source in the modal
    });
</script>

@endsection
