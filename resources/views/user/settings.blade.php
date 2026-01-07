@extends('layouts.app')

@section('app')

<style>
    .status-active { color: green; font-weight: bold; }
    .status-inactive { color: red; font-weight: bold; }
    .setting-card {
        transition: all 0.3s ease;
    }
    .setting-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .html-content {
        max-height: 100px;
        overflow: hidden;
        position: relative;
    }
    .html-content::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 30px;
        background: linear-gradient(transparent, white);
    }
    .full-view-modal .modal-dialog {
        max-width: 90%;
        max-height: 90vh;
    }
    .full-view-content {
        height: 70vh;
        overflow-y: auto;
        border: 1px solid #dee2e6;
        padding: 15px;
        background: #f8f9fa;
    }
</style>

<!-- CKEditor CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.0.0/classic/ckeditor.js"></script>

<div class="pagetitle">
    <h1>System Settings</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Settings</li>
        </ol>
    </nav>
</div>

<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">All Settings</h5>

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

                    <!-- Settings in Card View -->
                    <div class="row">
                        @forelse($settings as $key => $setting)
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card setting-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="card-title mb-0 text-primary">{{ $setting->name }}</h6>
                                        <span class="badge bg-{{ $setting->type == 'text' ? 'info' : ($setting->type == 'html' ? 'primary' : ($setting->type == 'number' ? 'warning' : 'success')) }}">
                                            {{ ucfirst($setting->type) }}
                                        </span>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Value:</small>
                                        @if($setting->type == 'html' || strpos($setting->value, '<') !== false)
                                            <div class="html-content mb-2">
                                                {!! $setting->value !!}
                                            </div>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-primary btn-view-html"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#fullViewModal"
                                                    data-name="{{ $setting->name }}"
                                                    data-content="{{ htmlspecialchars($setting->value) }}">
                                                <i class="bi bi-eye"></i> View Full Content
                                            </button>
                                        @elseif($setting->type == 'boolean')
                                            <p class="mb-1 fw-bold">
                                                {{ $setting->value ? 'Yes' : 'No' }}
                                            </p>
                                        @else
                                            <p class="mb-1 fw-bold text-truncate">
                                                {{ $setting->value }}
                                            </p>
                                        @endif
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <small class="text-muted d-block">Status:</small>
                                            <span class="{{ $setting->status == 1 ? 'status-active' : 'status-inactive' }}">
                                                {{ $setting->status == 1 ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted d-block">Modal Type:</small>
                                            <span>{{ ucfirst($setting->modal_type ?? 'N/A') }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3 d-flex justify-content-between">
                                        <button type="button" 
                                                class="btn btn-sm btn-primary edit-setting"
                                                data-id="{{ $setting->id }}"
                                                data-name="{{ $setting->name }}"
                                                data-value="{{ htmlspecialchars($setting->value) }}"
                                                data-status="{{ $setting->status }}"
                                                data-type="{{ $setting->type }}"
                                                data-modal_type="{{ $setting->modal_type }}">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        
                                        <button type="button" 
                                                class="btn btn-sm btn-info btn-more"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#moreInfoModal"
                                                data-id="{{ $setting->id }}"
                                                data-name="{{ $setting->name }}"
                                                data-value="{{ htmlspecialchars($setting->value) }}"
                                                data-status="{{ $setting->status }}"
                                                data-type="{{ $setting->type }}"
                                                data-modal_type="{{ $setting->modal_type }}"
                                                data-created="{{ $setting->created_at }}">
                                            <i class="bi bi-info-circle"></i> More
                                        </button>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-top-0">
                                    <small class="text-muted">
                                        
                                    </small>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <div class="alert alert-info">
                                No settings found. Add your first setting!
                            </div>
                        </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    @if($settings->hasPages())
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            {{-- Previous Page --}}
                            @if ($settings->onFirstPage())
                                <li class="page-item disabled">
                                    <a class="page-link">&laquo;</a>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $settings->previousPageUrl() }}">&laquo;</a>
                                </li>
                            @endif

                            {{-- Page Numbers --}}
                            @php
                                $start = max(1, $settings->currentPage() - 2);
                                $end = min($settings->lastPage(), $settings->currentPage() + 2);
                            @endphp
                            
                            @for ($page = $start; $page <= $end; $page++)
                                <li class="page-item {{ $page == $settings->currentPage() ? 'active' : '' }}">
                                    <a class="page-link" href="{{ $settings->url($page) }}">{{ $page }}</a>
                                </li>
                            @endfor

                            {{-- Next Page --}}
                            @if ($settings->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $settings->nextPageUrl() }}">&raquo;</a>
                                </li>
                            @else
                                <li class="page-item disabled">
                                    <a class="page-link">&raquo;</a>
                                </li>
                            @endif
                        </ul>
                    </nav>
                    @endif

                    <!-- Add New Setting Button -->
                    <div class="mt-4 text-center">
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addSettingModal">
                           
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Add Setting Modal -->
<div class="modal fade" id="addSettingModal" tabindex="-1" aria-labelledby="addSettingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addSettingModalLabel">Add New Setting</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Setting Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="value" class="form-label">Value</label>
                        <textarea class="form-control" id="value" name="value" rows="3" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label">Type</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="text">Text</option>
                                <option value="html">HTML/Content</option>
                                <option value="number">Number</option>
                                <option value="boolean">Boolean</option>
                                <option value="json">JSON</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="modal_type" class="form-label">Modal Type</label>
                            <input type="text" class="form-control" id="modal_type" name="modal_type">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Setting</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Setting Modal with CKEditor -->
<div class="modal fade full-view-modal" id="editSettingModal" tabindex="-1" aria-labelledby="editSettingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="editSettingForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editSettingModalLabel">Edit Setting: <span id="settingName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_id" name="id">
                    
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Setting Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_type" class="form-label">Type</label>
                        <input type="text" class="form-control" id="edit_type" name="type" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_modal_type" class="form-label">Modal Type</label>
                        <input type="text" class="form-control" id="edit_modal_type" name="modal_type" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_value" class="form-label">Content</label>
                        
                        <!-- CKEditor Container (for HTML content) -->
                        <div id="editorContainer" style="display: none;">
                            <textarea class="form-control" id="ckeditor" name="value" style="display: none;"></textarea>
                            <div id="ckeditor-content" style="min-height: 300px; border: 1px solid #dee2e6; border-radius: 4px;"></div>
                        </div>
                        
                        <!-- Simple Textarea (for non-HTML content) -->
                        <div id="simpleContainer">
                            <textarea class="form-control" id="edit_value_simple" name="value" rows="5"></textarea>
                        </div>
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
                    <button type="submit" class="btn btn-primary">Update Setting</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Full View Modal for HTML Content -->
<div class="modal fade full-view-modal" id="fullViewModal" tabindex="-1" aria-labelledby="fullViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fullViewModalLabel">Full Content: <span id="viewName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="full-view-content" id="fullViewContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- More Info Modal -->
<div class="modal fade" id="moreInfoModal" tabindex="-1" aria-labelledby="moreInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="moreInfoModalLabel">Setting Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Setting Name:</th>
                                <td id="more_name" class="fw-bold"></td>
                            </tr>
                            <tr>
                                <th>Type:</th>
                                <td id="more_type"></td>
                            </tr>
                            <tr>
                                <th>Value Preview:</th>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;" id="more_value_preview"></div>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Modal Type:</th>
                                <td id="more_modal_type"></td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td id="more_status"></td>
                            </tr>
                            <tr>
                                <th>Created:</th>
                                <td id="more_created"></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="editFromMoreBtn">Edit This Setting</button>
            </div>
        </div>
    </div>
</div>

<script>
// Global CKEditor instance
let ckeditorInstance = null;

// CKEditor Configuration
const ckeditorConfig = {
    toolbar: {
        items: [
            'heading', '|',
            'bold', 'italic', 'underline', 'strikethrough', '|',
            'link', 'bulletedList', 'numberedList', '|',
            'alignment', 'indent', 'outdent', '|',
            'fontSize', 'fontColor', 'fontBackgroundColor', '|',
            'code', 'codeBlock', '|',
            'insertTable', '|',
            'imageUpload', '|',
            'undo', 'redo'
        ]
    },
    language: 'en',
    licenseKey: '',
    height: '400px',
    simpleUpload: {
        uploadUrl: '', // Agar image upload chahiye
        withCredentials: true,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    }
};

// Initialize CKEditor
function initializeCKEditor(content = '') {
    // Destroy previous instance
    if (ckeditorInstance) {
        ckeditorInstance.destroy()
            .then(() => {
                console.log('Previous CKEditor destroyed');
                ckeditorInstance = null;
            })
            .catch(error => {
                console.error('Error destroying CKEditor:', error);
            });
    }
    
    // Create new instance
    ClassicEditor
        .create(document.querySelector('#ckeditor-content'), ckeditorConfig)
        .then(editor => {
            ckeditorInstance = editor;
            
            // Set content
            if (content) {
                editor.setData(content);
            }
            
            // Update hidden textarea on change
            editor.model.document.on('change:data', () => {
                document.getElementById('ckeditor').value = editor.getData();
            });
            
            console.log('CKEditor initialized');
        })
        .catch(error => {
            console.error('CKEditor initialization error:', error);
            // Fallback to textarea
            document.getElementById('editorContainer').style.display = 'none';
            document.getElementById('simpleContainer').style.display = 'block';
            document.getElementById('edit_value_simple').value = content;
        });
}

// Save CKEditor content before form submit
function saveCKEditorContent() {
    if (ckeditorInstance) {
        const content = ckeditorInstance.getData();
        document.getElementById('ckeditor').value = content;
    }
}

// Helper function to decode HTML
function decodeHtml(html) {
    const txt = document.createElement("textarea");
    txt.innerHTML = html;
    return txt.value;
}

// Main JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // View HTML content
    document.querySelectorAll('.btn-view-html').forEach(button => {
        button.addEventListener('click', function() {
            const name = this.getAttribute('data-name');
            const content = this.getAttribute('data-content');
            
            document.getElementById('viewName').textContent = name;
            document.getElementById('fullViewContent').innerHTML = decodeHtml(content);
        });
    });

    // Edit button
    document.querySelectorAll('.edit-setting').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const value = this.getAttribute('data-value');
            const status = this.getAttribute('data-status');
            const type = this.getAttribute('data-type');
            const modal_type = this.getAttribute('data-modal_type');
            
            // Set form values
            document.getElementById('edit_id').value = id;
            document.getElementById('settingName').textContent = name;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_type').value = type;
            document.getElementById('edit_modal_type').value = modal_type;
            document.getElementById('edit_status').value = status;
            
            // Decode value
            const decodedValue = decodeHtml(value);
            
            // Check if HTML editor needed
            const isHtmlContent = type === 'html' || 
                                 value.includes('<') || 
                                 name.toLowerCase().includes('policy') || 
                                 name.toLowerCase().includes('term') ||
                                 name.toLowerCase().includes('privacy') ||
                                 name.toLowerCase().includes('condition');
            
            if (isHtmlContent) {
                document.getElementById('editorContainer').style.display = 'block';
                document.getElementById('simpleContainer').style.display = 'none';
                
                // Initialize CKEditor after a small delay
                setTimeout(() => {
                    initializeCKEditor(decodedValue);
                    document.getElementById('ckeditor').value = decodedValue;
                }, 100);
            } else {
                document.getElementById('editorContainer').style.display = 'none';
                document.getElementById('simpleContainer').style.display = 'block';
                document.getElementById('edit_value_simple').value = decodedValue;
            }
            
            // Set form action
            document.getElementById('editSettingForm').action = '/updateSetting/' + id;
            
            // Show modal
            const editModal = new bootstrap.Modal(document.getElementById('editSettingModal'));
            editModal.show();
            
            // Handle modal close
            document.getElementById('editSettingModal').addEventListener('hidden.bs.modal', function () {
                if (ckeditorInstance) {
                    ckeditorInstance.destroy()
                        .then(() => {
                            ckeditorInstance = null;
                        })
                        .catch(error => {
                            console.error('Error:', error);
                        });
                }
            });
        });
    });

    // Form submission
    document.getElementById('editSettingForm').addEventListener('submit', function(e) {
        saveCKEditorContent();
        
        const formData = new FormData(this);
        const value = formData.get('value');
        
        if (!value || value.trim() === '') {
            e.preventDefault();
            alert('Content cannot be empty!');
            return false;
        }
        
        return true;
    });

    // More info button
    document.querySelectorAll('.btn-more').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const value = this.getAttribute('data-value');
            const status = this.getAttribute('data-status');
            const type = this.getAttribute('data-type');
            const modal_type = this.getAttribute('data-modal_type');
            const created = this.getAttribute('data-created');
            
            // Format date
            const createdDate = new Date(created);
            const formattedDate = createdDate.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            
            // Set values
            document.getElementById('more_name').textContent = name;
            document.getElementById('more_type').textContent = type;
            document.getElementById('more_modal_type').textContent = modal_type || 'N/A';
            document.getElementById('more_status').innerHTML = status == 1 
                ? '<span class="status-active">Active</span>' 
                : '<span class="status-inactive">Inactive</span>';
            document.getElementById('more_created').textContent = formattedDate;
            
            // Show preview
            const decodedValue = decodeHtml(value);
            const preview = decodedValue.length > 50 ? decodedValue.substring(0, 50) + '...' : decodedValue;
            document.getElementById('more_value_preview').textContent = preview.replace(/<[^>]*>/g, '');
            
            // Set edit button data
            document.getElementById('editFromMoreBtn').setAttribute('data-id', id);
            document.getElementById('editFromMoreBtn').setAttribute('data-name', name);
            document.getElementById('editFromMoreBtn').setAttribute('data-value', value);
            document.getElementById('editFromMoreBtn').setAttribute('data-status', status);
            document.getElementById('editFromMoreBtn').setAttribute('data-type', type);
            document.getElementById('editFromMoreBtn').setAttribute('data-modal_type', modal_type);
        });
    });

    // Edit from more info modal
    document.getElementById('editFromMoreBtn').addEventListener('click', function() {
        const moreModal = bootstrap.Modal.getInstance(document.getElementById('moreInfoModal'));
        moreModal.hide();
        
        // Get data
        const id = this.getAttribute('data-id');
        const name = this.getAttribute('data-name');
        const value = this.getAttribute('data-value');
        const status = this.getAttribute('data-status');
        const type = this.getAttribute('data-type');
        const modal_type = this.getAttribute('data-modal_type');
        
        // Set edit modal values
        document.getElementById('edit_id').value = id;
        document.getElementById('settingName').textContent = name;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_type').value = type;
        document.getElementById('edit_modal_type').value = modal_type;
        document.getElementById('edit_status').value = status;
        
        // Decode value
        const decodedValue = decodeHtml(value);
        
        // Check if HTML editor needed
        const isHtmlContent = type === 'html' || 
                             value.includes('<') || 
                             name.toLowerCase().includes('policy') || 
                             name.toLowerCase().includes('term') ||
                             name.toLowerCase().includes('privacy') ||
                             name.toLowerCase().includes('condition');
        
        if (isHtmlContent) {
            document.getElementById('editorContainer').style.display = 'block';
            document.getElementById('simpleContainer').style.display = 'none';
            
            // Initialize CKEditor
            setTimeout(() => {
                initializeCKEditor(decodedValue);
                document.getElementById('ckeditor').value = decodedValue;
            }, 100);
        } else {
            document.getElementById('editorContainer').style.display = 'none';
            document.getElementById('simpleContainer').style.display = 'block';
            document.getElementById('edit_value_simple').value = decodedValue;
        }
        
        // Set form action
        document.getElementById('editSettingForm').action = '/updateSetting/' + id;
        
        // Show edit modal
        setTimeout(() => {
            const editModal = new bootstrap.Modal(document.getElementById('editSettingModal'));
            editModal.show();
        }, 300);
    });

    // Type change in add modal
    const typeSelect = document.getElementById('type');
    const valueContainer = document.getElementById('value') ? document.getElementById('value').parentNode : null;
    
    if(typeSelect && valueContainer) {
        typeSelect.addEventListener('change', function() {
            const currentElement = document.getElementById('value');
            const currentValue = currentElement ? (currentElement.value || '') : '';
            
            // Remove current element
            if (currentElement) {
                currentElement.remove();
            }
            
            if(this.value === 'number') {
                // Number input
                const numberInput = document.createElement('input');
                numberInput.type = 'number';
                numberInput.className = 'form-control';
                numberInput.name = 'value';
                numberInput.id = 'value';
                numberInput.value = currentValue;
                numberInput.step = 'any';
                valueContainer.appendChild(numberInput);
            } else if(this.value === 'boolean') {
                // Select for boolean
                const booleanSelect = document.createElement('select');
                booleanSelect.className = 'form-control';
                booleanSelect.name = 'value';
                booleanSelect.id = 'value';
                booleanSelect.innerHTML = `
                    <option value="1" ${currentValue == 1 || currentValue === 'true' ? 'selected' : ''}>Yes/True</option>
                    <option value="0" ${currentValue == 0 || currentValue === 'false' ? 'selected' : ''}>No/False</option>
                `;
                valueContainer.appendChild(booleanSelect);
            } else {
                // Textarea for text/html
                const textarea = document.createElement('textarea');
                textarea.className = 'form-control';
                textarea.name = 'value';
                textarea.id = 'value';
                textarea.rows = this.value === 'html' ? 6 : 3;
                textarea.value = currentValue;
                valueContainer.appendChild(textarea);
            }
        });
    }
});

// Add CSS for CKEditor
const ckeditorStyle = document.createElement('style');
ckeditorStyle.textContent = `
    .ck-editor__editable {
        min-height: 300px;
        max-height: 500px;
        overflow-y: auto;
    }
    .ck-content {
        font-family: Arial, sans-serif;
        line-height: 1.6;
        padding: 15px;
    }
    .ck.ck-toolbar {
        border-radius: 4px 4px 0 0 !important;
        background: #f8f9fa !important;
        border-bottom: 1px solid #dee2e6 !important;
    }
    .ck.ck-editor__main>.ck-editor__editable {
        border-radius: 0 0 4px 4px !important;
        border-top: none !important;
    }
    .ck.ck-button:hover:not(.ck-disabled) {
        background: #e9ecef !important;
    }
    .ck.ck-dropdown__panel {
        border-radius: 4px !important;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1) !important;
    }
`;
document.head.appendChild(ckeditorStyle);
</script>

@endsection