<?php
session_start();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Strict admin validation
if (!isset($_SESSION['admin_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

require_once '../includes/connection.php';

// Initialize variables to preserve form data on error
$preserved_values = [
    'title' => '',
    'description' => '',
    'due_date' => '',
    'assign_to' => 'all'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $title = mysqli_real_escape_string($connection, $_POST['title'] ?? '');
    $description = mysqli_real_escape_string($connection, $_POST['description'] ?? '');
    $due_date = $_POST['due_date'] ?? '';
    $assign_to = $_POST['assign_to'] ?? '';
    
    // Preserve values for form repopulation
    $preserved_values = [
        'title' => htmlspecialchars($title),
        'description' => htmlspecialchars($description),
        'due_date' => htmlspecialchars($due_date),
        'assign_to' => htmlspecialchars($assign_to)
    ];
    
    // Enhanced validation
    $errors = [];
    if (empty($title)) {
        $errors[] = "Title is required";
    } elseif (strlen($title) > 255) {
        $errors[] = "Title must be less than 255 characters";
    }
    
    if (empty($due_date)) {
        $errors[] = "Due date is required";
    } elseif (strtotime($due_date) < strtotime('today')) {
        $errors[] = "Due date cannot be in the past";
    }
    
    if (!empty($errors)) {
        $_SESSION['error'] = implode("<br>", $errors);
        header("Location: create_task.php");
        exit();
    }

    // Insert task with transaction for data integrity
    mysqli_begin_transaction($connection);
    
    try {
        $query = "INSERT INTO tasks (
            title, description, status, due_date, 
            is_bulk_task, created_by_admin_name
        ) VALUES (?, ?, 'pending', ?, ?, ?)";
        
        $stmt = mysqli_prepare($connection, $query);
        $is_bulk = ($assign_to === 'all') ? 1 : 0;
        
        mysqli_stmt_bind_param(
            $stmt, 
            "sssis",
            $title,
            $description,
            $due_date,
            $is_bulk,
            $_SESSION['admin_name']
        );
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Task creation failed: " . mysqli_error($connection));
        }
        
        $task_id = mysqli_insert_id($connection);
        
        // ===== NOTIFICATION SYSTEM ADDITION START ===== //
        $notification_message = "New Task: $title (Due: $due_date)";
        $notif_stmt = mysqli_prepare($connection, "INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        // ===== NOTIFICATION SYSTEM ADDITION END ===== //
        
        // Handle assignments with prepared statements
        if ($is_bulk) {
            $users = mysqli_query($connection, "SELECT id FROM users");
            $assign_query = "INSERT INTO task_assignments (task_id, user_id) VALUES (?, ?)";
            $assign_stmt = mysqli_prepare($connection, $assign_query);
            
            while ($user = mysqli_fetch_assoc($users)) {
                mysqli_stmt_bind_param($assign_stmt, "ii", $task_id, $user['id']);
                if (!mysqli_stmt_execute($assign_stmt)) {
                    throw new Exception("Bulk assignment failed: " . mysqli_error($connection));
                }
                
                // ===== NOTIFICATION SYSTEM ADDITION START ===== //
                // Add notification for each user in bulk assignment
                mysqli_stmt_bind_param($notif_stmt, "is", $user['id'], $notification_message);
                mysqli_stmt_execute($notif_stmt);
                // ===== NOTIFICATION SYSTEM ADDITION END ===== //
            }
        } else {
            $assign_query = "INSERT INTO task_assignments (task_id, user_id) VALUES (?, ?)";
            $assign_stmt = mysqli_prepare($connection, $assign_query);
            mysqli_stmt_bind_param($assign_stmt, "ii", $task_id, $assign_to);
            if (!mysqli_stmt_execute($assign_stmt)) {
                throw new Exception("Assignment failed: " . mysqli_error($connection));
            }
            
            // ===== NOTIFICATION SYSTEM ADDITION START ===== //
            // Add notification for single user assignment
            mysqli_stmt_bind_param($notif_stmt, "is", $assign_to, $notification_message);
            mysqli_stmt_execute($notif_stmt);
            // ===== NOTIFICATION SYSTEM ADDITION END ===== //
        }
        
        mysqli_commit($connection);
        $_SESSION['success'] = "Task created and notifications sent successfully!";
        header("Location: admin_dashboard.php");
        exit();
    } catch (Exception $e) {
        mysqli_rollback($connection);
        $_SESSION['error'] = $e->getMessage();
        header("Location: create_task.php");
        exit();
    }
}

// Fetch users for dropdown
$users_result = mysqli_query($connection, "SELECT id, name FROM users ORDER BY name ASC");
$users = [];
while ($user = mysqli_fetch_assoc($users_result)) {
    $users[] = $user;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Task</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #f8f9fc;
            --success-color: #1cc88a;
            --danger-color: #e74a3b;
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
        
        .alert {
            border-left: 0.25rem solid;
        }
        
        .alert-danger {
            border-left-color: var(--danger-color);
        }
        
        .alert-success {
            border-left-color: var(--success-color);
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
                            <i class="bi bi-plus-circle-fill text-primary me-2"></i>
                            Create New Task
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
                    
                    <form method="POST" id="taskForm">
                        <div class="mb-4">
                            <label for="title" class="form-label">Title*</label>
                            <input type="text" name="title" id="title" class="form-control form-control-lg" 
                                   value="<?= $preserved_values['title'] ?>" required
                                   placeholder="Enter task title">
                            <div class="invalid-feedback">Please provide a valid title (max 255 characters).</div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control" rows="5"
                                      placeholder="Enter task description"><?= $preserved_values['description'] ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="assign_to" class="form-label">Assign To*</label>
                                <select name="assign_to" id="assign_to" class="form-select" required>
                                    <option value="all" <?= $preserved_values['assign_to'] === 'all' ? 'selected' : '' ?>>All Users</option>
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?= $user['id'] ?>" 
                                            <?= $preserved_values['assign_to'] == $user['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($user['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <label for="due_date" class="form-label">Due Date*</label>
                                <input type="date" name="due_date" id="due_date" class="form-control" 
                                       value="<?= $preserved_values['due_date'] ?>" required
                                       min="<?= date('Y-m-d') ?>">
                                <div class="invalid-feedback">Please select a valid future date.</div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="reset" class="btn btn-outline-secondary me-md-2">
                                <i class="bi bi-arrow-counterclockwise"></i> Reset
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save-fill"></i> Create Task
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Client-side validation
        document.getElementById('taskForm').addEventListener('submit', function(event) {
            let isValid = true;
            const title = document.getElementById('title');
            const dueDate = document.getElementById('due_date');
            
            // Validate title
            if (title.value.trim() === '' || title.value.length > 255) {
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
        
        // Initialize date picker to today's date if empty
        document.addEventListener('DOMContentLoaded', function() {
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
        });
    </script>
</body>
</html>