@extends('layouts.app') 
@section('app')

<body>
    <div class="pagetitle">
        <h1>Document List</h1>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12"> 
                <div class="card">
                    <div class="card-body">

                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Aadhar Front</th>
                                    <th>Aadhar Back</th>
                                    <th>Aadhar No</th>
                                    <th>PAN Number</th>
                                    <th>PAN Card Image</th>
                                    <th>Passport Image</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($provider_docs as $index => $side)
                                <tr>
                                    <td>{{ $provider_docs->firstItem() + $index }}</td>

                                    <td>
                                        <img src="{{ asset(''.$side->aadhar_front) }}" 
                                             width="50" height="50">
                                    </td>

                                    <td>
                                        <img src="{{ asset(''.$side->aadhar_back) }}" 
                                             width="50" height="50">
                                    </td>

                                    <td>{{ $side->aadhar_no }}</td>

                                    <td>{{ $side->pan_number }}</td>

                                    <td>
                                        <img src="{{ asset(''.$side->pan_cart_image) }}" 
                                             width="50" height="50">
                                    </td>

                                    <td>
                                        <img src="{{ asset(''.$side->passpost_image) }}" 
                                             width="50" height="50">
                                    </td>

                                    <td>{{ $side->created_at }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="d-flex justify-content-center">
                            {{ $provider_docs->links('pagination::bootstrap-5') }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
