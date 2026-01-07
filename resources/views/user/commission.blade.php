@extends('layouts.app')

@section('app')

<style>
    .status-active { color: green; font-weight: bold; }
    .status-inactive { color: red; font-weight: bold; }
</style>

<div class="pagetitle">
    <h1>Commission Settings</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Commission</li>
        </ol>
    </nav>
</div>

<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Commission Rates</h5>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>SL</th>
                                <th>Service Providers Commission (%)</th>
                                <th>Handymans Commission (%)</th>
                                <th>Admin Commission (%)</th>
                                <th>Status</th>
                                <th>Last Updated</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $key => $commission)
                            <tr>
                                <td>{{ $users->firstItem() + $key }}</td>
                                <td>{{ $commission->commission_providers }}%</td>
                                <td>{{ $commission->commission_handymans }}%</td>
                                <td>{{ $commission->commission_admin }}%</td>
                                <td>
                                    <span class="{{ $commission->status == 1 ? 'status-active' : 'status-inactive' }}">
                                        {{ $commission->status == 1 ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>{{ date('d M Y, h:i A', strtotime($commission->updated_at)) }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary edit-commission" 
                                            data-id="{{ $commission->id }}"
                                            data-providers="{{ $commission->commission_providers }}"
                                            data-handymans="{{ $commission->commission_handymans }}"
                                            data-admin="{{ $commission->commission_admin }}"
                                            data-status="{{ $commission->status }}">
                                        Edit
                                    </button>
                                    
                                   
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">No commission settings found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            {{-- Previous Page --}}
                            @if ($users->onFirstPage())
                                <li class="page-item disabled">
                                    <a class="page-link">&laquo;</a>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $users->previousPageUrl() }}">&laquo;</a>
                                </li>
                            @endif

                            {{-- Page Numbers --}}
                            @php
                                $start = max(1, $users->currentPage() - 2);
                                $end = min($users->lastPage(), $users->currentPage() + 2);
                            @endphp
                            
                            @for ($page = $start; $page <= $end; $page++)
                                <li class="page-item {{ $page == $users->currentPage() ? 'active' : '' }}">
                                    <a class="page-link" href="{{ $users->url($page) }}">{{ $page }}</a>
                                </li>
                            @endfor

                            {{-- Next Page --}}
                            @if ($users->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $users->nextPageUrl() }}">&raquo;</a>
                                </li>
                            @else
                                <li class="page-item disabled">
                                    <a class="page-link">&raquo;</a>
                                </li>
                            @endif
                        </ul>
                    </nav>

                    <!-- Add New Commission Button -->
                    
                </div>
            </div>
        </div>
    </div>
</section>



<!-- Edit Commission Modal -->
<div class="modal fade" id="editCommissionModal" tabindex="-1" aria-labelledby="editCommissionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editCommissionForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editCommissionModalLabel">Edit Commission Setting</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_id" name="id">
                    
                    <div class="mb-3">
                        <label for="edit_commission_providers" class="form-label">Service Providers Commission (%)</label>
                        <input type="number" class="form-control" id="edit_commission_providers" 
                               name="commission_providers" step="0.01" min="0" max="100" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_commission_handymans" class="form-label">Handymans Commission (%)</label>
                        <input type="number" class="form-control" id="edit_commission_handymans" 
                               name="commission_handymans" step="0.01" min="0" max="100" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_commission_admin" class="form-label">Admin Commission (%)</label>
                        <input type="number" class="form-control" id="edit_commission_admin" 
                               name="commission_admin" step="0.01" min="0" max="100" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Status</label>
                        <select class="form-select" id="edit_status" name="status" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Edit button click handler
        document.querySelectorAll('.edit-commission').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const providers = this.getAttribute('data-providers');
                const handymans = this.getAttribute('data-handymans');
                const admin = this.getAttribute('data-admin');
                const status = this.getAttribute('data-status');
                
                // Set values in the edit form
                document.getElementById('edit_id').value = id;
                document.getElementById('edit_commission_providers').value = providers;
                document.getElementById('edit_commission_handymans').value = handymans;
                document.getElementById('edit_commission_admin').value = admin;
                document.getElementById('edit_status').value = status;
                
                // Set form action
                document.getElementById('editCommissionForm').action = '/commission/update/' + id;
                
                // Show modal
                const editModal = new bootstrap.Modal(document.getElementById('editCommissionModal'));
                editModal.show();
            });
        });

        // Form validation
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const providers = this.querySelector('[name="commission_providers"]');
                const handymans = this.querySelector('[name="commission_handymans"]');
                const admin = this.querySelector('[name="commission_admin"]');
                
                if (providers && handymans && admin) {
                    const total = parseFloat(providers.value) + parseFloat(handymans.value) + parseFloat(admin.value);
                    
                    if (total > 100) {
                        e.preventDefault();
                        alert('Total commission cannot exceed 100%');
                    }
                }
            });
        });
    });
</script>

@endsection