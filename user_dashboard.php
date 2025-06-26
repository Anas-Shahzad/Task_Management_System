<?php
session_start();
require_once 'includes/connection.php';

// Get user stats
$stats_query = "SELECT 
    COUNT(ta.task_id) as total_tasks,
    SUM(t.status = 'completed') as completed_tasks,
    SUM(t.status = 'pending') as pending_tasks
    FROM task_assignments ta
    JOIN tasks t ON ta.task_id = t.id
    WHERE ta.user_id = ?";
    
$stats_stmt = mysqli_prepare($connection, $stats_query);
mysqli_stmt_bind_param($stats_stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stats_stmt);
$stats = mysqli_fetch_assoc(mysqli_stmt_get_result($stats_stmt));

// Calculate completion percentage
$completion_percentage = $stats['total_tasks'] > 0 ? 
    round(($stats['completed_tasks'] / $stats['total_tasks']) * 100) : 0;

// Get task list
$tasks_query = "SELECT t.* FROM tasks t
                JOIN task_assignments ta ON t.id = ta.task_id
                WHERE ta.user_id = ?
                ORDER BY t.due_date ASC";

$tasks_stmt = mysqli_prepare($connection, $tasks_query);
mysqli_stmt_bind_param($tasks_stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($tasks_stmt);
$tasks = mysqli_fetch_all(mysqli_stmt_get_result($tasks_stmt), MYSQLI_ASSOC);

// Get upcoming deadlines
$deadlines_query = "SELECT t.title, t.due_date 
                  FROM tasks t
                  JOIN task_assignments ta ON t.id = ta.task_id
                  WHERE ta.user_id = ? 
                  AND t.status != 'completed'
                  AND t.due_date >= CURDATE()
                  ORDER BY t.due_date ASC
                  LIMIT 4";
$deadlines_stmt = mysqli_prepare($connection, $deadlines_query);
mysqli_stmt_bind_param($deadlines_stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($deadlines_stmt);
$upcoming_deadlines = mysqli_fetch_all(mysqli_stmt_get_result($deadlines_stmt), MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Task Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --light-color: #f8f9fa;
            --success-color: #4cc9f0;
            --warning-color: #f8961e;
            --danger-color: #f94144;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7ff;
            color: #2b2d42;
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 4px 20px rgba(67, 97, 238, 0.15);
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-left: 4px solid var(--primary-color);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        
        .progress-container {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }
        
        .task-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            border-left: 3px solid var(--primary-color);
        }
        
        .task-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        }
        
        .status-badge {
            padding: 0.35rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .upcoming-item {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            transition: background-color 0.2s ease;
        }
        
        .upcoming-item:hover {
            background-color: #f8f9fa;
        }
        
        .logout-btn {
            background-color: transparent;
            border: 1px solid white;
            color: white;
            padding: 0.375rem 0.75rem;
            border-radius: 0.25rem;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }
        
        /* Responsive adjustments */
        @media (max-width: 992px) {
            .dashboard-header .row {
                flex-direction: column;
                text-align: center;
            }
            .dashboard-header .col-md-4 {
                margin-top: 1rem;
                justify-content: center;
            }
            .stat-card {
                padding: 1rem;
            }
        }
        
        @media (max-width: 768px) {
            .progress-container, .task-card {
                padding: 1rem;
            }
            .col-lg-8, .col-lg-4 {
                flex: 0 0 100%;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-0">Task Dashboard</h1>
                    <p class="mb-0">Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?>!</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <button class="logout-btn" onclick="confirmLogout()">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-4">
        <!-- Stats Row -->
        <div class="row">
            <div class="col-md-4">
                <div class="stat-card">
                    <h5>Total Tasks</h5>
                    <h3><?= $stats['total_tasks'] ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <h5>Completed</h5>
                    <h3><?= $stats['completed_tasks'] ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <h5>Pending</h5>
                    <h3><?= $stats['pending_tasks'] ?></h3>
                </div>
            </div>
        </div>

        <!-- Progress Section -->
        <div class="progress-container">
            <div class="d-flex justify-content-between mb-3">
                <h5 class="mb-0">Task Completion Progress</h5>
                <span class="text-primary fw-bold"><?= $completion_percentage ?>%</span>
            </div>
            <div class="progress">
                <div class="progress-bar" role="progressbar" 
                     style="width: <?= $completion_percentage ?>%" 
                     aria-valuenow="<?= $completion_percentage ?>" 
                     aria-valuemin="0" 
                     aria-valuemax="100"></div>
            </div>
            <div class="text-end mt-2">
                <small class="text-muted"><?= $stats['completed_tasks'] ?> of <?= $stats['total_tasks'] ?> tasks completed</small>
            </div>
        </div>

        <!-- Task List -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="mb-0">Your Tasks</h5>
                            <a href="#" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        
                        <?php if (empty($tasks)): ?>
                            <div class="text-center py-4">
                                <i class="bi bi-check-circle-fill text-success" style="font-size: 2rem;"></i>
                                <p class="mt-2">No tasks assigned yet!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($tasks as $task): ?>
                            <div class="task-card mb-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1"><?= htmlspecialchars($task['title']) ?></h6>
                                        <p class="small text-muted mb-2"><?= htmlspecialchars($task['description']) ?></p>
                                        <span class="due-date">
                                            <i class="bi bi-calendar me-1"></i>
                                            Due: <?= date('M j, Y', strtotime($task['due_date'])) ?>
                                        </span>
                                    </div>
                                    <div class="text-end">
                                        <span class="status-badge status-<?= str_replace(' ', '-', $task['status']) ?>">
                                            <?= ucfirst($task['status']) ?>
                                        </span>
                                        <div class="mt-2">
                                            <a href="update_task.php?id=<?= $task['id'] ?>" class="action-btn text-primary">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Upcoming Deadlines Section -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="mb-4">Upcoming Deadlines</h5>
                        
                        <?php if (empty($upcoming_deadlines)): ?>
                            <div class="text-center py-3">
                                <i class="bi bi-check-circle text-success"></i>
                                <p class="text-muted mt-2">No upcoming deadlines</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($upcoming_deadlines as $deadline): 
                                $due_date = new DateTime($deadline['due_date']);
                                $today = new DateTime();
                                $interval = $today->diff($due_date);
                                $days_diff = $interval->days;
                                
                                // Determine urgency
                                if ($days_diff == 0) {
                                    $urgency = 'Today';
                                    $badge_class = 'bg-danger';
                                } elseif ($days_diff == 1) {
                                    $urgency = 'Tomorrow';
                                    $badge_class = 'bg-warning';
                                } elseif ($days_diff <= 3) {
                                    $urgency = 'Soon';
                                    $badge_class = 'bg-info';
                                } else {
                                    $urgency = 'Upcoming';
                                    $badge_class = 'bg-secondary';
                                }
                            ?>
                                <div class="upcoming-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong style="color: var(--primary-color);"><?= htmlspecialchars($deadline['title']) ?></strong>
                                        <span class="badge <?= $badge_class ?>"><?= $urgency ?></span>
                                    </div>
                                    <p class="small text-muted mb-0">
                                        Due: <?= $due_date->format('M j, Y') ?>
                                        <?= ($days_diff == 0) ? '' : '('.$days_diff.' days)' ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmLogout() {
            if (confirm("Are you sure you want to logout?")) {
                window.location.href = "user_login.php";
            }
        }
    </script>
</body>
</html>