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

// Check database connection
if (!$connection || mysqli_connect_errno()) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Fetch tasks with search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$query = "SELECT t.*, 
                 GROUP_CONCAT(u.name SEPARATOR ', ') as assigned_users,
                 t.created_by_admin_name as creator
          FROM tasks t
          LEFT JOIN task_assignments ta ON t.id = ta.task_id
          LEFT JOIN users u ON ta.user_id = u.id";

$params = [];
$types = "";

if (!empty($search)) {
    $query .= " WHERE (t.title LIKE ? OR t.description LIKE ? OR t.created_by_admin_name LIKE ?)";
    $params = array_fill(0, 3, "%$search%");
    $types = "sss";
}

$query .= " GROUP BY t.id ORDER BY t.due_date ASC";

$stmt = mysqli_prepare($connection, $query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

if (!mysqli_stmt_execute($stmt)) {
    die("Query failed: " . mysqli_error($connection));
}

$tasks = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- <script>
        $(document).ready(function(){
            $("#create_task").click(function(e){
                e.preventDefault();
                $("#right_sidebar").load("create_task.php");
            });
        });
    </script> -->
    <style>
        .task-card {
            transition: transform 0.2s;
            margin-bottom: 15px;
        }
        .task-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .status-badge {
            font-size: 0.8rem;
        }
        .search-box {
            max-width: 500px;
        }
        .admin-actions {
            display: flex;
            gap: 5px;
        }
        .welcome-message {
            font-size: 1.1rem;
            color: #4361ee;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1>Admin Dashboard</h1>
                <p class="welcome-message">Welcome, <?= htmlspecialchars($_SESSION['admin_name']) ?></p>
            </div>
            <div id="right_sidebar">
                <a href="create_task.php" class="btn btn-success me-2" id="create_task">Create Task</a>
            </div>
        </div>

        <form method="GET" class="search-box mb-4">
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Search tasks..." 
                       value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-primary" type="submit">Search</button>
            </div>
        </form>

        <?php if (empty($tasks)): ?>
            <div class="alert alert-info">No tasks found</div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($tasks as $task): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card task-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <h5 class="card-title"><?= htmlspecialchars($task['title']) ?></h5>
                                <span class="badge bg-<?= 
                                    $task['status'] === 'completed' ? 'success' : 
                                    ($task['status'] === 'in_progress' ? 'warning' : 'secondary')
                                ?> status-badge">
                                    <?= ucfirst(str_replace('_', ' ', $task['status'])) ?>
                                </span>
                            </div>
                            <p class="card-text"><?= htmlspecialchars($task['description']) ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted">Assigned to: <?= htmlspecialchars($task['assigned_users'] ?? 'Unassigned') ?></small><br>
                                    <small class="text-muted">Due: <?= date('M d, Y', strtotime($task['due_date'])) ?></small>
                                </div>
                                <div class="admin-actions">
                                    <a href="edit_task.php?id=<?= $task['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <a href="delete_task.php?id=<?= $task['id'] ?>" class="btn btn-sm btn-outline-danger" 
                                       onclick="return confirm('Are you sure?')">Delete</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>