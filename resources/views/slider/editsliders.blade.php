
@extends('layouts.app')

@section('app')
<div class="pagetitle">
    <h1>Update Slider </h1>
</div>

<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('slider.update', $slider->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group mt-3">
                            <label for="image">slider Image</label>
                            <input type="file" class="form-control" id="image" name="image">
                            @if($slider->image)
                                <img src="{{ asset($slider->image) }}" alt="slider Image" width="100" height="100" class="mt-2">
                            @endif
                        </div>

                        <button type="submit" class="btn btn-success mt-3">Update slider</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
