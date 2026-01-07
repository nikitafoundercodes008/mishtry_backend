@extends('layouts.app') 

@section('app')
<div class="category-form card-body"> 

    {{-- Success Message --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Error Message --}}
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Update Profile Form --}}
    <form method="POST" enctype="multipart/form-data" action="{{ route('admin.editprofile') }}">
        @csrf
        <h3 class="mb-4 text-center">Update Profile</h3>

        {{-- Email --}}
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
        </div>

        {{-- Password --}}
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" value="">
            <small class="text-muted">Leave blank if you don’t want to change password.</small>
        </div>

        {{-- Submit Button --}}
        <button type="submit" class="btn btn-primary w-100">Update Profile</button>
    </form>
</div>

{{-- Styles --}}
<style>
    .category-form {
        max-width: 600px;
        margin: 50px auto;
        padding: 30px;
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .form-label {
        font-size: 1rem;
        font-weight: 600;
        color: #333;
    }

    .form-control {
        font-size: 1rem;
        padding: 0.8rem;
        border-radius: 5px;
        border: 1px solid #ddd;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
    }

    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 5px rgba(0,123,255,0.3);
        outline: none;
    }

    .btn-primary {
        font-size: 1.1rem;
        padding: 0.8rem;
        background-color: #007bff;
        border-color: #007bff;
        border-radius: 5px;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background-color: #0056b3;
        border-color: #004085;
    }

    .mb-3 {
        margin-bottom: 1.5rem;
    }

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
@endsection
