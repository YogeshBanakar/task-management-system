@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <div class="d-flex align-items-center">
                            <label for="filterStatus" class="form-label mb-0 me-2 fw-semibold text-dark">Status:</label>
                            <select id="filterStatus" class="form-select form-select-sm w-auto select2">
                                <option value="">All</option>
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>

                        <div class="d-flex align-items-center">
                            <label for="filterPriority" class="form-label mb-0 me-2 fw-semibold text-dark">Priority:</label>
                            <select id="filterPriority" class="form-select form-select-sm w-auto select2">
                                <option value="">All</option>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>

                    @can('create', App\Models\Task::class)
                    <button class="btn btn-primary btn-sm d-flex align-items-center gap-2" id="create-task-btn">
                        <i class="fas fa-plus"></i> <span>Create Task</span>
                    </button>
                    @endcan
                </div>
            </div>

            <div class="card-body">
                <table id="tasks-table" class="table table-bordered table-hover w-100">
                    <thead class="table-light">
                        <tr>
                            <th>Sr No</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Created By</th>
                            <th>Assigned To</th>
                            <th>Due Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="taskModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="taskModalLabel">Create Task</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="taskForm">
                <div class="modal-body">
                    <input type="hidden" id="task_id" name="task_id">

                    <div class="mb-3">
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select select2" id="status" name="status" required>
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                            <select class="form-select select2" id="priority" name="priority" required>
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="assigned_to" class="form-label">Assign To</label>
                            <select class="form-select select2" id="assigned_to" name="assigned_to">
                                <option value="">Unassigned</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="due_date" class="form-label">Due Date</label>
                            <input type="date" class="form-control" id="due_date" name="due_date">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Close
                    </button>
                    <button type="submit" class="btn btn-primary" id="saveTaskBtn">
                        <i class="fas fa-save"></i> Save Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="viewTaskModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Task Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewTaskContent"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function getStatusBadgeClass(status) {
    return {
        pending: 'secondary',
        in_progress: 'primary',
        completed: 'success',
        cancelled: 'danger'
    }[status] || 'secondary';
}

function getPriorityBadgeClass(priority) {
    return {
        low: 'info',
        medium: 'warning',
        high: 'danger',
        urgent: 'danger'
    }[priority] || 'secondary';
}

const table = $('#tasks-table').DataTable({
    processing: true,
    serverSide: true,
    responsive: true,
    autoWidth: false,
    ajax: {
        url: '{{ route("tasks.index") }}',
        data: function (d) {
            d.status = $('#filterStatus').val();
            d.priority = $('#filterPriority').val();
        }
    },
    columns: [
        {
            data: null,
            orderable: false,
            searchable: false,
            render: function (data, type, row, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;
            }
        },
        { data: 'title', name: 'title' },
        {
            data: 'status',
            name: 'status',
            render: function (data, type, row) {
                if (row.can_update_status) {
                    let options = '<select class="form-select form-select-sm update-status" data-id="' + row.id + '">';
                    options += '<option value="pending"' + (data === 'pending' ? ' selected' : '') + '>PENDING</option>';
                    options += '<option value="in_progress"' + (data === 'in_progress' ? ' selected' : '') + '>IN PROGRESS</option>';
                    options += '<option value="completed"' + (data === 'completed' ? ' selected' : '') + '>COMPLETED</option>';
                    options += '<option value="cancelled"' + (data === 'cancelled' ? ' selected' : '') + '>CANCELLED</option>';
                    options += '</select>';
                    return options;
                }
                return '<span class="badge bg-' + getStatusBadgeClass(data) + '">' + data.replace('_', ' ').toUpperCase() + '</span>';
            }
        },
        { data: 'priority_badge', name: 'priority' },
        { data: 'creator_name', name: 'creator.name' },
        { data: 'assignee_name', name: 'assignee.name' },
        { data: 'due_date', name: 'due_date' },
        { data: 'action', name: 'action', orderable: false, searchable: false }
    ],
    order: [[1, 'desc']],
    columnDefs: [
        { targets: [2, 3, 7], className: 'text-center' }
    ],
    createdRow: function (row, data) {
        $('td:eq(2)', row).html(data.status_badge);
        $('td:eq(3)', row).html(data.priority_badge);
    }
});

$('#filterStatus, #filterPriority').on('change', function() {
    table.ajax.reload();
});

$('#create-task-btn').click(function() {
    $('#taskForm')[0].reset();
    $('#task_id').val('');
    $('#taskModalLabel').text('Create Task');
    loadUsers();
    $('#taskModal').modal('show');
});

$('#taskForm').submit(function(e) {
    e.preventDefault();
    $('.invalid-feedback').text('');
    $('.is-invalid').removeClass('is-invalid');

    const taskId = $('#task_id').val();
    const url = taskId ? `/tasks/${taskId}` : '{{ route('tasks.store') }}';
    const method = taskId ? 'PUT' : 'POST';

    $.ajax({
        url: url,
        method: method,
        data: $(this).serialize(),
        success: function(response) {
            $('#taskModal').modal('hide');
            table.ajax.reload();
            toastr.success(response.message);
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                Object.keys(errors).forEach(function(field) {
                    const el = $('#' + field);
                    el.addClass('is-invalid');
                    el.siblings('.invalid-feedback').text(errors[field][0]);
                });
            } else {
                toastr.error('An error occurred. Please try again.');
            }
        }
    });
});

$(document).on('click', '.edit-task', function() {
    const taskId = $(this).data('id');
    $.get(`/tasks/${taskId}/edit`, function(response) {
        const task = response.task;
        $('#taskModalLabel').text('Edit Task');
        $('#task_id').val(task.id);
        $('#title').val(task.title);
        $('#description').val(task.description);
        $('#status').val(task.status).trigger('change');
        $('#priority').val(task.priority).trigger('change');
        const date = task.due_date ? task.due_date.split('T')[0] : '';
        $('#due_date').val(date);
        loadUsers(task.assigned_to);
        $('#taskModal').modal('show');
    });
});

$(document).on('click', '.delete-task', function() {
    const id = $(this).data('id');
    Swal.fire({
        title: 'Are you sure?',
        text: "This will be permanently deleted.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it'
    }).then((res) => {
        if (res.isConfirmed) {
            $.ajax({
                url: `/tasks/${id}`,
                method: 'DELETE',
                success: function(response) {
                    toastr.success(response.message);
                    table.ajax.reload();
                },
                error: function() {
                    toastr.error('An error occurred.');
                }
            });
        }
    });
});

$(document).on('click', '.view-task', function() {
    const id = $(this).data('id');
    $.get(`/tasks/${id}`, function(response) {
        const task = response.task;
        const created = task.created_at ? task.created_at.split('T')[0] : 'Not set';
        const updated = task.updated_at ? task.updated_at.split('T')[0] : null;
        const due = task.due_date ? task.due_date.split('T')[0] : 'Not set';

        let updatedSection = '';
        if (updated && updated !== created) {
            updatedSection = '<p class="mb-1"><strong>Last Updated:</strong><br>' + updated + '</p>';
        }

        const content = `
            <div class="task-view p-3">
                <div class="mb-3 pb-2 border-bottom">
                    <h4 class="fw-bold text-primary mb-1"><i class="fas fa-clipboard-list me-2"></i>${task.title}</h4>
                    <p class="text-muted small mb-0">${task.description || '<em>No description provided.</em>'}</p>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Created By:</strong><br>${task.creator ? task.creator.name : 'N/A'}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Assigned To:</strong><br>${task.assignee ? task.assignee.name : '<em>Unassigned</em>'}</p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6"><p class="mb-1"><strong>Created On:</strong><br>${created}</p></div>
                    <div class="col-md-6">${updatedSection}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Status:</strong><br><span class="badge bg-${getStatusBadgeClass(task.status)} px-3 py-2">${task.status.replace('_',' ').toUpperCase()}</span></p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Priority:</strong><br><span class="badge bg-${getPriorityBadgeClass(task.priority)} px-3 py-2">${task.priority.toUpperCase()}</span></p>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-12"><p class="mb-0"><strong>Due Date:</strong> ${due}</p></div>
                </div>
            </div>`;
        $('#viewTaskContent').html(content);
        $('#viewTaskModal').modal('show');
    });
});

function loadUsers(selectedUserId = null) {
    $.get('{{ route("tasks.assignable-users") }}', function(response) {
        if (!response.success) return;
        const $select = $('#assigned_to');
        $select.empty().append('<option value="">Unassigned</option>');
        const loggedInUserId = {{ auth()->id() }};
        response.users.forEach(function(user) {
            const label = Number(user.id) === Number(loggedInUserId) ? 'Self' : user.name;
            const selected = selectedUserId && selectedUserId == user.id ? ' selected' : '';
            $select.append('<option value="'+user.id+'"'+selected+'>'+label+'</option>');
        });
        $select.val(selectedUserId).trigger('change');
    });
}

$(document).on('change', '.update-status', function() {
    const id = $(this).data('id');
    const status = $(this).val();
    $.ajax({
        url: `/tasks/${id}/status`,
        method: 'PUT',
        data: { status: status, _token: '{{ csrf_token() }}' },
        success: function(res) {
            toastr.success(res.message);
            table.ajax.reload(null, false);
        },
        error: function() {
            toastr.error('Could not update status.');
        }
    });
});
</script>
@endpush
