@extends('layouts.app') 
@section('app')

<div class="modal-body">
    <!-- Form for adding category -->
    <form action="{{ route('store') }}" method="POST" enctype="multipart/form-data" class="category-form">
        @csrf
        <div class="mb-3">
            <label for="category-name" class="form-label">Category Name</label>
            <input type="text" class="form-control" id="category-name" name="name" required placeholder="Enter category name" value="{{ old('name') }}">
            @error('name')
                <div class="text-danger mt-2">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="category-image" class="form-label">Category Image</label>
            <input type="file" class="form-control" id="category-image" name="image" required>
            @error('image')
                <div class="text-danger mt-2">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="category-status" class="form-label">Status</label>
            <select class="form-control" id="category-status" name="status" required>
                <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
            </select>
            @error('status')
                <div class="text-danger mt-2">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary w-100">Save Category</button>
    </form>
</div>

<style>
    /* General form styling */
    .category-form {
        max-width: 600px;
        margin: 0 auto;
        padding: 20px;
        padding-top:5%;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    /* Label styling */
    .form-label {
        font-size: 1rem;
        font-weight: 600;
        color: #333;
    }

    /* Input and select box styling */
    .form-control {
        font-size: 1rem;
        padding: 0.8rem;
        border-radius: 5px;
        border: 1px solid #ddd;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
    }

    /* Button styling */
    .btn-primary {
        font-size: 1.1rem;
        padding: 0.8rem;
        background-color: #007bff;
        border-color: #007bff;
        width: 100%;
        border-radius: 5px;
    }

    .btn-primary:hover {
        background-color: #0056b3;
        border-color: #004085;
    }

    /* Add some space between the elements */
    .mb-3 {
        margin-bottom: 1.8rem;
    }

    /* Responsive design: make the form responsive */
    @media (max-width: 576px) {
        .category-form {
            padding: 20px;
        }

        .btn-primary {
            font-size: 1rem;
        }

        .form-control {
            font-size: 0.9rem;
        }
    }
</style>
