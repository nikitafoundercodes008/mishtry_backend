
@extends('layouts.app')

@section('app')
<div class="pagetitle">
    <h1>Edit Category</h1>
</div>

<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('category.update', $category->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="name">Category Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ $category->name }}" required>
                        </div>

                        <div class="form-group mt-3">
                            <label for="image">Category Image</label>
                            <input type="file" class="form-control" id="image" name="image">
                            @if($category->image)
                                <img src="{{ asset($category->image) }}" alt="Category Image" width="100" height="100" class="mt-2">
                            @endif
                        </div>

                        <button type="submit" class="btn btn-success mt-3">Update Category</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
