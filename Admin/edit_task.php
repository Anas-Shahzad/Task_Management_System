<?php
session_start();
require_once '../includes/connection.php';

// Strict admin validation
if (!isset($_SESSION['admin_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

// Check if task ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid task ID";
    header("Location: admin_dashboard.php");
    exit();
}

$task_id = intval($_GET['id']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $task_id = intval($_POST['task_id']);
    $title = mysqli_real_escape_string($connection, $_POST['title'] ?? '');
    $description = mysqli_real_escape_string($connection, $_POST['description'] ?? '');
    $due_date = $_POST['due_date'] ?? '';
    $status = in_array($_POST['status'] ?? '', ['pending', 'in_progress', 'completed']) 
              ? $_POST['status'] : 'pending';
    $assigned_users = $_POST['assigned_users'] ?? [];

    // Validate required fields
    $errors = [];
    if (empty($title)) {
        $errors[] = "Title is required";
    }
    if (empty($due_date)) {
        $errors[] = "Due date is required";
    } elseif (strtotime($due_date) < strtotime('today')) {
        $errors[] = "Due date cannot be in the past";
    }

    if (!empty($errors)) {
        $_SESSION['error'] = implode("<br>", $errors);
        header("Location: edit_task.php?id=$task_id");
        exit();
    }

    // Begin transaction
    mysqli_begin_transaction($connection);

    try {
        // Update task
        $query = "UPDATE tasks SET 
                  title = ?, 
                  description = ?, 
                  due_date = ?, 
                  status = ?,
                  updated_at = NOW()
                  WHERE id = ?";
        
        $stmt = mysqli_prepare($connection, $query);
        mysqli_stmt_bind_param($stmt, "ssssi", $title, $description, $due_date, $status, $task_id);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Task update failed: " . mysqli_error($connection));
        }

        // Update assignments
        // First remove existing assignments
        $delete_query = "DELETE FROM task_assignments WHERE task_id = ?";
        $delete_stmt = mysqli_prepare($connection, $delete_query);
        mysqli_stmt_bind_param($delete_stmt, "i", $task_id);
        
        if (!mysqli_stmt_execute($delete_stmt)) {
            throw new Exception("Failed to remove existing assignments: " . mysqli_error($connection));
        }

        // Add new assignments
        if (!empty($assigned_users)) {
            $assign_query = "INSERT INTO task_assignments (task_id, user_id) VALUES (?, ?)";
            $assign_stmt = mysqli_prepare($connection, $assign_query);
            
            foreach ($assigned_users as $user_id) {
                $user_id = intval($user_id);
                mysqli_stmt_bind_param($assign_stmt, "ii", $task_id, $user_id);
                if (!mysqli_stmt_execute($assign_stmt)) {
                    throw new Exception("Assignment failed for user $user_id: " . mysqli_error($connection));
                }
            }
        }

        mysqli_commit($connection);
        $_SESSION['success'] = "Task updated successfully!";
        header("Location: admin_dashboard.php");
        exit();
    } catch (Exception $e) {
        mysqli_rollback($connection);
        $_SESSION['error'] = $e->getMessage();
        header("Location: edit_task.php?id=$task_id");
        exit();
    }
}

// Fetch task data with prepared statement
$task_query = "SELECT * FROM tasks WHERE id = ?";
$task_stmt = mysqli_prepare($connection, $task_query);
mysqli_stmt_bind_param($task_stmt, "i", $task_id);
mysqli_stmt_execute($task_stmt);
$task_result = mysqli_stmt_get_result($task_stmt);
$task = mysqli_fetch_assoc($task_result);

if (!$task) {
    $_SESSION['error'] = "Task not found";
    header("Location: admin_dashboard.php");
    exit();
}

// Fetch all users and assigned users
$users_result = mysqli_query($connection, "SELECT id, name FROM users ORDER BY name ASC");
$users = [];
while ($user = mysqli_fetch_assoc($users_result)) {
    $users[] = $user;
}

$assigned_result = mysqli_query($connection, 
    "SELECT user_id FROM task_assignments WHERE task_id = $task_id");
$assigned_ids = array_column(mysqli_fetch_all($assigned_result, MYSQLI_ASSOC), 'user_id');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #f8f9fc;
            --success-color: #1cc88a;
            --danger-color: #e74a3b;
            --warning-color: #f6c23e;
        }
        
        body {
            background-color: var(--secondary-color);
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .task-card {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            padding: 2rem;
            margin-top: 2rem;
        }
        
        .form-label {
            font-weight: 600;
            color: #5a5c69;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2653d4;
        }
        
        .status-badge {
            padding: 0.35em 0.65em;
            font-weight: 600;
            border-radius: 0.25rem;
        }
        
        .status-pending {
            background-color: #f8f9fc;
            color: #5a5c69;
        }
        
        .status-in_progress {
            background-color: var(--warning-color);
            color: #000;
        }
        
        .status-completed {
            background-color: var(--success-color);
            color: white;
        }
        
        .select2-container--default .select2-selection--multiple {
            min-height: 38px;
            border: 1px solid #ced4da;
        }
        
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }
        
        @media (max-width: 768px) {
            .task-card {
                padding: 1.5rem;
                margin-top: 1rem;
            }
            
            h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="task-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="mb-0">
                            <i class="bi bi-pencil-square text-primary me-2"></i>
                            Edit Task
                        </h2>
                        <a href="admin_dashboard.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong><i class="bi bi-exclamation-triangle-fill"></i> Error:</strong> <?= $_SESSION['error'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>
                    
                    <form method="POST" id="editTaskForm">
                        <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                        
                        <div class="mb-4">
                            <label for="title" class="form-label">Title*</label>
                            <input type="text" name="title" id="title" class="form-control form-control-lg" 
                                   value="<?= htmlspecialchars($task['title']) ?>" required
                                   placeholder="Enter task title">
                            <div class="invalid-feedback">Please provide a valid title.</div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control" rows="5"
                                      placeholder="Enter task description"><?= htmlspecialchars($task['description']) ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="due_date" class="form-label">Due Date*</label>
                                <input type="date" name="due_date" id="due_date" class="form-control" 
                                       value="<?= htmlspecialchars($task['due_date']) ?>" required
                                       min="<?= date('Y-m-d') ?>">
                                <div class="invalid-feedback">Please select a valid future date.</div>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <label for="status" class="form-label">Status*</label>
                                <select name="status" id="status" class="form-select" required>
                                    <option value="pending" <?= $task['status'] === 'pending' ? 'selected' : '' ?>>
                                        <span class="status-badge status-pending">Pending</span>
                                    </option>
                                    <option value="in_progress" <?= $task['status'] === 'in_progress' ? 'selected' : '' ?>>
                                        <span class="status-badge status-in_progress">In Progress</span>
                                    </option>
                                    <option value="completed" <?= $task['status'] === 'completed' ? 'selected' : '' ?>>
                                        <span class="status-badge status-completed">Completed</span>
                                    </option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="assigned_users" class="form-label">Assign To</label>
                            <select name="assigned_users[]" id="assigned_users" class="form-select" multiple>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= $user['id'] ?>" 
                                        <?= in_array($user['id'], $assigned_ids) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($user['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Select one or more users</small>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="admin_dashboard.php" class="btn btn-outline-secondary me-md-2">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save-fill"></i> Update Task
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        // Initialize Select2 for user assignment
        $(document).ready(function() {
            $('#assigned_users').select2({
                placeholder: "Select users",
                allowClear: true,
                width: '100%'
            });
            
            // Style status options
            $('#status').on('change', function() {
                $(this).find('option').each(function() {
                    const status = $(this).val();
                    $(this).html(`<span class="status-badge status-${status.replace('_', '')}">${$(this).text()}</span>`);
                });
            }).trigger('change');
            
            // Set minimum date to today if empty
            const dueDate = document.getElementById('due_date');
            if (!dueDate.value) {
                const today = new Date();
                const tomorrow = new Date(today);
                tomorrow.setDate(tomorrow.getDate() + 1);
                
                const yyyy = tomorrow.getFullYear();
                const mm = String(tomorrow.getMonth() + 1).padStart(2, '0');
                const dd = String(tomorrow.getDate()).padStart(2, '0');
                
                dueDate.value = `${yyyy}-${mm}-${dd}`;
            }
            
            // Client-side validation
            document.getElementById('editTaskForm').addEventListener('submit', function(event) {
                let isValid = true;
                const title = document.getElementById('title');
                const dueDate = document.getElementById('due_date');
                
                // Validate title
                if (title.value.trim() === '') {
                    title.classList.add('is-invalid');
                    isValid = false;
                } else {
                    title.classList.remove('is-invalid');
                }
                
                // Validate due date
                const selectedDate = new Date(dueDate.value);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                if (!dueDate.value || selectedDate < today) {
                    dueDate.classList.add('is-invalid');
                    isValid = false;
                } else {
                    dueDate.classList.remove('is-invalid');
                }
                
                if (!isValid) {
                    event.preventDefault();
                    event.stopPropagation();
                    
                    // Scroll to first invalid field
                    const firstInvalid = document.querySelector('.is-invalid');
                    if (firstInvalid) {
                        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });
        });
    </script>
</body>
</html>