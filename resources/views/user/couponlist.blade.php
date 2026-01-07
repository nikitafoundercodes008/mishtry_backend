@extends('layouts.app')

@section('app')

<style>
    .justified {
        text-align: justify;
    }
    .coupon-table {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .coupon-table th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 15px;
        font-weight: 600;
    }
    .coupon-table td {
        padding: 12px 15px;
        vertical-align: middle;
        border-bottom: 1px solid #f0f0f0;
    }
    .coupon-table tr:hover {
        background-color: rgba(103, 126, 234, 0.05);
    }
    .coupon-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .badge-percent {
        background: rgba(40, 167, 69, 0.15);
        color: #28a745;
        border: 1px solid rgba(40, 167, 69, 0.3);
    }
    .badge-fixed {
        background: rgba(0, 123, 255, 0.15);
        color: #007bff;
        border: 1px solid rgba(0, 123, 255, 0.3);
    }
    .action-buttons {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .btn-action {
        padding: 5px 12px;
        font-size: 12px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .date-badge {
        background: #f8f9fa;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        border: 1px solid #e9ecef;
    }
    .add-coupon-btn {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border: none;
        padding: 10px 25px;
        border-radius: 8px;
        color: white;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
    }
    .add-coupon-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
    }
    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }
    .active-status {
        background: rgba(40, 167, 69, 0.15);
        color: #28a745;
    }
    .inactive-status {
        background: rgba(108, 117, 125, 0.15);
        color: #6c757d;
    }
    .expired-status {
        background: rgba(220, 53, 69, 0.15);
        color: #dc3545;
    }
</style>

<div class="pagetitle">
    <h1>Coupons List</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Coupons</li>
        </ol>
    </nav>
</div>

<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0">All Coupons</h5>
                        <button type="button" class="add-coupon-btn" data-bs-toggle="modal" data-bs-target="#addCouponModal">
                            <i class="bi bi-plus-circle"></i> Add New Coupon
                        </button>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="coupon-table">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>SL</th>
                                    <th>Code</th>
                                    <th>Title</th>
                                    <th>Discount</th>
                                    <th>Min Order</th>
                                    <th>Max Discount</th>
                                    <th>Validity</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $key => $coupon)
                                <tr>
                                    <!-- Serial Number -->
                                    <td class="fw-bold">{{ $users->firstItem() + $key }}</td>

                                    <!-- Code -->
                                    <td>
                                        <div class="fw-bold text-primary">{{ $coupon->code }}</div>
                                    </td>

                                    <!-- Title -->
                                    <td>
                                        <div class="fw-semibold">{{ $coupon->title }}</div>
                                        @if($coupon->description)
                                        <small class="text-muted">{{ Str::limit($coupon->description, 30) }}</small>
                                        @endif
                                    </td>

                                    <!-- Discount -->
                                    <td>
                                        <span class="coupon-badge {{ $coupon->discount_type == 'percent' ? 'badge-percent' : 'badge-fixed' }}">
                                            {{ $coupon->discount_value }}
                                            {{ $coupon->discount_type == 'percent' ? '%' : '₹' }}
                                        </span>
                                    </td>

                                    <!-- Min Order -->
                                    <td>
                                        @if($coupon->min_order_amount > 0)
                                            <span class="fw-semibold">₹{{ number_format($coupon->min_order_amount, 2) }}</span>
                                        @else
                                            <span class="text-muted">No minimum</span>
                                        @endif
                                    </td>

                                    <!-- Max Discount -->
                                   

                                    <!-- Validity -->
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            <span class="date-badge">
                                                <i class="bi bi-calendar-plus me-1"></i> {{ date('d M Y', strtotime($coupon->start_date)) }}
                                            </span>
                                            <span class="date-badge">
                                                <i class="bi bi-calendar-minus me-1"></i> {{ date('d M Y', strtotime($coupon->end_date)) }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Status -->
                                    <td>
                                        @php
                                            $status = 'active';
                                            $today = date('Y-m-d');
                                            if($coupon->end_date < $today) {
                                                $status = 'expired';
                                            } elseif($coupon->status == 'inactive') {
                                                $status = 'inactive';
                                            }
                                        @endphp
                                        <span class="status-badge {{ $status == 'active' ? 'active-status' : ($status == 'inactive' ? 'inactive-status' : 'expired-status') }}">
                                            {{ ucfirst($status) }}
                                        </span>
                                    </td>

                                    <!-- Actions -->
                                    <td>
                                        <div class="action-buttons">
                                            <!-- Quick Edit Button -->
                                            <button type="button" class="btn btn-sm btn-outline-primary btn-action" 
                                                    data-bs-toggle="modal" data-bs-target="#editCouponModal"
                                                    data-id="{{ $coupon->id }}"
                                                    data-code="{{ $coupon->code }}"
                                                    data-title="{{ $coupon->title }}"
                                                    data-description="{{ $coupon->description }}"
                                                    data-discount_type="{{ $coupon->discount_type }}"
                                                    data-discount_value="{{ $coupon->discount_value }}"
                                                    data-min_order_amount="{{ $coupon->min_order_amount }}"
                                                    data-max_discount="{{ $coupon->max_discount }}"
                                                    data-start_date="{{ $coupon->start_date }}"
                                                    data-end_date="{{ $coupon->end_date }}"
                                                    data-status="{{ $coupon->status }}">
                                                <i class="bi bi-pencil"></i> Edit
                                            </button>

                                            <!-- View Details Button -->
                                            <button type="button" class="btn btn-sm btn-outline-info btn-action"
                                                    data-bs-toggle="modal" data-bs-target="#viewCouponModal"
                                                    data-id="{{ $coupon->id }}"
                                                    data-code="{{ $coupon->code }}"
                                                    data-title="{{ $coupon->title }}"
                                                    data-description="{{ $coupon->description }}"
                                                    data-discount_type="{{ $coupon->discount_type }}"
                                                    data-discount_value="{{ $coupon->discount_value }}"
                                                    data-min_order_amount="{{ $coupon->min_order_amount }}"
                                                    data-max_discount="{{ $coupon->max_discount }}"
                                                    data-start_date="{{ $coupon->start_date }}"
                                                    data-end_date="{{ $coupon->end_date }}"
                                                    data-created="{{ $coupon->created_at }}">
                                                <i class="bi bi-eye"></i> View
                                            </button>

                                            <!-- Delete Button -->
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-action"
                                                    onclick="confirmDelete({{ $coupon->id }}, '{{ $coupon->code }}')">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-tag display-4 d-block mb-3"></i>
                                            <h5>No coupons found</h5>
                                            <p>Click "Add New Coupon" to create your first coupon</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($users->hasPages())
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            {{-- Previous Page --}}
                            @if ($users->onFirstPage())
                                <li class="page-item disabled">
                                    <a class="page-link"><i class="bi bi-chevron-left"></i></a>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $users->previousPageUrl() }}"><i class="bi bi-chevron-left"></i></a>
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
                                    <a class="page-link" href="{{ $users->nextPageUrl() }}"><i class="bi bi-chevron-right"></i></a>
                                </li>
                            @else
                                <li class="page-item disabled">
                                    <a class="page-link"><i class="bi bi-chevron-right"></i></a>
                                </li>
                            @endif
                        </ul>
                    </nav>
                    @endif

                </div>
            </div>
        </div>
    </div>
</section>

<!-- Add Coupon Modal -->
<div class="modal fade" id="addCouponModal" tabindex="-1" aria-labelledby="addCouponModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('coupon.store') }}" method="POST" id="addCouponForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addCouponModalLabel">Add New Coupon</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="add_code" class="form-label">Coupon Code *</label>
                            <input type="text" class="form-control" id="add_code" name="code" required>
                            <div class="form-text">Enter unique code (e.g., SAVE20)</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="add_title" class="form-label">Coupon Title *</label>
                            <input type="text" class="form-control" id="add_title" name="title" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="add_description" class="form-label">Description</label>
                        <textarea class="form-control" id="add_description" name="description" rows="2"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="add_discount_type" class="form-label">Discount Type *</label>
                            <select class="form-select" id="add_discount_type" name="discount_type" required>
                                <option value="">Select Type</option>
                                <option value="percent">Percentage (%)</option>
                                <option value="fixed">Fixed Amount (₹)</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="add_discount_value" class="form-label">Discount Value *</label>
                            <input type="number" class="form-control" id="add_discount_value" name="discount_value" step="0.01" min="0" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="add_min_order_amount" class="form-label">Minimum Order Amount</label>
                            <input type="number" class="form-control" id="add_min_order_amount" name="min_order_amount" step="0.01" min="0" value="0">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="add_max_discount" class="form-label">Maximum Discount</label>
                            <input type="number" class="form-control" id="add_max_discount" name="max_discount" step="0.01" min="0" value="0">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="add_start_date" class="form-label">Start Date *</label>
                            <input type="date" class="form-control" id="add_start_date" name="start_date" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="add_end_date" class="form-label">End Date *</label>
                            <input type="date" class="form-control" id="add_end_date" name="end_date" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="add_status" class="form-label">Status *</label>
                        <select class="form-select" id="add_status" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Coupon</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Coupon Modal -->
<div class="modal fade" id="editCouponModal" tabindex="-1" aria-labelledby="editCouponModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('coupon.update', '') }}" method="POST" id="editCouponForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_id" name="id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCouponModalLabel">Edit Coupon</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_code" class="form-label">Coupon Code *</label>
                            <input type="text" class="form-control" id="edit_code" name="code" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="edit_title" class="form-label">Coupon Title *</label>
                            <input type="text" class="form-control" id="edit_title" name="title" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="2"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_discount_type" class="form-label">Discount Type *</label>
                            <select class="form-select" id="edit_discount_type" name="discount_type" required>
                                <option value="percent">Percentage (%)</option>
                                <option value="fixed">Fixed Amount (₹)</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="edit_discount_value" class="form-label">Discount Value *</label>
                            <input type="number" class="form-control" id="edit_discount_value" name="discount_value" step="0.01" min="0" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_min_order_amount" class="form-label">Minimum Order Amount</label>
                            <input type="number" class="form-control" id="edit_min_order_amount" name="min_order_amount" step="0.01" min="0">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="edit_max_discount" class="form-label">Maximum Discount</label>
                            <input type="number" class="form-control" id="edit_max_discount" name="max_discount" step="0.01" min="0">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_start_date" class="form-label">Start Date *</label>
                            <input type="date" class="form-control" id="edit_start_date" name="start_date" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="edit_end_date" class="form-label">End Date *</label>
                            <input type="date" class="form-control" id="edit_end_date" name="end_date" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Status *</label>
                        <select class="form-select" id="edit_status" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Coupon</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Coupon Details Modal -->
<div class="modal fade" id="viewCouponModal" tabindex="-1" aria-labelledby="viewCouponModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewCouponModalLabel">Coupon Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="coupon-details">
                    <div class="text-center mb-4">
                        <div class="display-4 text-primary mb-2" id="view_code"></div>
                        <h4 id="view_title" class="mb-3"></h4>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted">Discount Type:</label>
                            <div id="view_discount_type" class="fw-bold"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted">Discount Value:</label>
                            <div id="view_discount_value" class="fw-bold"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted">Min Order:</label>
                            <div id="view_min_order_amount" class="fw-bold"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted">Max Discount:</label>
                            <div id="view_max_discount" class="fw-bold"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted">Start Date:</label>
                            <div id="view_start_date" class="fw-bold"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted">End Date:</label>
                            <div id="view_end_date" class="fw-bold"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted">Description:</label>
                        <div id="view_description" class="fw-bold"></div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted">Created On:</label>
                        <div id="view_created" class="fw-bold"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set today's date as min for start date
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('add_start_date').min = today;
    document.getElementById('add_end_date').min = today;

    // Edit Coupon Modal Handler
    const editModal = document.getElementById('editCouponModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            
            // Get data from button
            const id = button.getAttribute('data-id');
            const code = button.getAttribute('data-code');
            const title = button.getAttribute('data-title');
            const description = button.getAttribute('data-description');
            const discount_type = button.getAttribute('data-discount_type');
            const discount_value = button.getAttribute('data-discount_value');
            const min_order_amount = button.getAttribute('data-min_order_amount');
            const max_discount = button.getAttribute('data-max_discount');
            const start_date = button.getAttribute('data-start_date');
            const end_date = button.getAttribute('data-end_date');
            const status = button.getAttribute('data-status');
            
            // Update form
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_code').value = code;
            document.getElementById('edit_title').value = title;
            document.getElementById('edit_description').value = description;
            document.getElementById('edit_discount_type').value = discount_type;
            document.getElementById('edit_discount_value').value = discount_value;
            document.getElementById('edit_min_order_amount').value = min_order_amount || 0;
            document.getElementById('edit_max_discount').value = max_discount || 0;
            document.getElementById('edit_start_date').value = start_date;
            document.getElementById('edit_end_date').value = end_date;
            document.getElementById('edit_status').value = status;
            
            // Update form action
            document.getElementById('editCouponForm').action = '{{ url("coupon/update") }}/' + id;
        });
    }

    // View Coupon Modal Handler
    const viewModal = document.getElementById('viewCouponModal');
    if (viewModal) {
        viewModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            
            // Get data from button
            const code = button.getAttribute('data-code');
            const title = button.getAttribute('data-title');
            const description = button.getAttribute('data-description');
            const discount_type = button.getAttribute('data-discount_type');
            const discount_value = button.getAttribute('data-discount_value');
            const min_order_amount = button.getAttribute('data-min_order_amount');
            const max_discount = button.getAttribute('data-max_discount');
            const start_date = button.getAttribute('data-start_date');
            const end_date = button.getAttribute('data-end_date');
            const created = button.getAttribute('data-created');
            
            // Format dates
            const formatDate = (dateString) => {
                const date = new Date(dateString);
                return date.toLocaleDateString('en-IN', {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric'
                });
            };
            
            // Update modal content
            document.getElementById('view_code').textContent = code;
            document.getElementById('view_title').textContent = title;
            document.getElementById('view_discount_type').textContent = discount_type === 'percent' ? 'Percentage' : 'Fixed Amount';
            document.getElementById('view_discount_value').textContent = 
                discount_type === 'percent' ? `${discount_value}%` : `₹${discount_value}`;
            document.getElementById('view_min_order_amount').textContent = 
                min_order_amount > 0 ? `₹${parseFloat(min_order_amount).toFixed(2)}` : 'No minimum';
            document.getElementById('view_max_discount').textContent = 
                max_discount > 0 ? `₹${parseFloat(max_discount).toFixed(2)}` : 'No limit';
            document.getElementById('view_start_date').textContent = formatDate(start_date);
            document.getElementById('view_end_date').textContent = formatDate(end_date);
            document.getElementById('view_description').textContent = description || 'No description';
            document.getElementById('view_created').textContent = formatDate(created);
        });
    }

    // Form validation for add modal
    document.getElementById('addCouponForm').addEventListener('submit', function(e) {
        const startDate = document.getElementById('add_start_date').value;
        const endDate = document.getElementById('add_end_date').value;
        
        if (startDate && endDate && startDate > endDate) {
            e.preventDefault();
            alert('End date must be after start date!');
            return false;
        }
        
        const discountValue = parseFloat(document.getElementById('add_discount_value').value);
        const discountType = document.getElementById('add_discount_type').value;
        
        if (discountType === 'percent' && discountValue > 100) {
            e.preventDefault();
            alert('Percentage discount cannot exceed 100%!');
            return false;
        }
        
        return true;
    });

    // Form validation for edit modal
    document.getElementById('editCouponForm').addEventListener('submit', function(e) {
        const startDate = document.getElementById('edit_start_date').value;
        const endDate = document.getElementById('edit_end_date').value;
        
        if (startDate && endDate && startDate > endDate) {
            e.preventDefault();
            alert('End date must be after start date!');
            return false;
        }
        
        const discountValue = parseFloat(document.getElementById('edit_discount_value').value);
        const discountType = document.getElementById('edit_discount_type').value;
        
        if (discountType === 'percent' && discountValue > 100) {
            e.preventDefault();
            alert('Percentage discount cannot exceed 100%!');
            return false;
        }
        
        return true;
    });
});

// Delete confirmation function
function confirmDelete(id, code) {
    if (confirm(`Are you sure you want to delete coupon "${code}"? This action cannot be undone.`)) {
        window.location.href = `{{ url('coupon/delete') }}/${id}`;
    }
}
</script>

@endsection