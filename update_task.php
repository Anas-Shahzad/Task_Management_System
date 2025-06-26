<?php
session_start();
require_once 'includes/connection.php';

// Verify user session
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$task_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Verify task belongs to user
$task_check = $connection->prepare("SELECT t.* FROM tasks t
                                  JOIN task_assignments ta ON t.id = ta.task_id
                                  WHERE t.id = ? AND ta.user_id = ?");
$task_check->bind_param("ii", $task_id, $user_id);
$task_check->execute();
$task = $task_check->get_result()->fetch_assoc();

if (!$task) {
    $_SESSION['error'] = "Task not found or access denied";
    header("Location: user_dashboard.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate file upload
    $submission_file = null;
    if (isset($_FILES['submission_file']) && $_FILES['submission_file']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['application/pdf', 'image/jpeg', 'image/png'];
        $file_type = $_FILES['submission_file']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            $_SESSION['error'] = "Only PDF, JPG, and PNG files are allowed";
            header("Location: update_task.php?id=$task_id");
            exit();
        }

        // Create uploads directory if not exists
        if (!is_dir('../submissions')) {
            mkdir('../submissions', 0755, true);
        }

        // Generate unique filename
        $file_ext = pathinfo($_FILES['submission_file']['name'], PATHINFO_EXTENSION);
        $submission_file = "submission_{$task_id}_{$user_id}_" . time() . ".$file_ext";
        move_uploaded_file($_FILES['submission_file']['tmp_name'], "../submissions/$submission_file");
    }

    // Update task status
    $update = $connection->prepare("UPDATE tasks SET 
                                  status = 'completed',
                                  completed_at = NOW(),
                                  submission_file = ?
                                  WHERE id = ?");
    $update->bind_param("si", $submission_file, $task_id);
    
    if ($update->execute()) {
        $_SESSION['success'] = "Task submitted successfully!";
        
        // Optional: Send notification to admin
        $admin_query = $connection->query("SELECT email FROM admins LIMIT 1");
        if ($admin_query->num_rows > 0) {
            $admin_email = $admin_query->fetch_assoc()['email'];
            $subject = "Task Completed: {$task['title']}";
            $message = "User {$_SESSION['user_name']} has completed task: {$task['title']}";
            mail($admin_email, $subject, $message);
        }
        
        header("Location: user_dashboard.php");
        exit();
    } else {
        $_SESSION['error'] = "Error submitting task: " . $connection->error;
        header("Location: update_task.php?id=$task_id");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Task</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .upload-area {
            border: 2px dashed #ccc;
            padding: 2rem;
            text-align: center;
            margin-bottom: 1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .upload-area:hover {
            border-color: #4361ee;
            background: #f8f9fa;
        }
        .file-info {
            margin-top: 1rem;
            font-size: 0.9rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Complete Task</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>
                        
                        <h5 class="card-title"><?= htmlspecialchars($task['title']) ?></h5>
                        <p class="card-text"><?= htmlspecialchars($task['description']) ?></p>
                        <p class="text-muted">Due: <?= date('M j, Y', strtotime($task['due_date'])) ?></p>
                        
                        <hr>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-4">
                                <label class="form-label fw-bold">Submit Your Work</label>
                                <div class="upload-area" id="uploadArea">
                                    <i class="bi bi-cloud-arrow-up fs-1 text-primary"></i>
                                    <p class="mt-2">Drag & drop files here or click to browse</p>
                                    <div class="file-info" id="fileInfo">No file selected</div>
                                    <input type="file" name="submission_file" id="fileInput" class="d-none" accept=".pdf,.jpg,.jpeg,.png">
                                </div>
                                <small class="text-muted">Accepted formats: PDF, JPG, PNG (Max 5MB)</small>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="bi bi-check-circle"></i> Mark as Completed
                                </button>
                                <a href="user_dashboard.php" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    
    <script>
        // File upload UI interactions
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');
        const fileInfo = document.getElementById('fileInfo');
        
        uploadArea.addEventListener('click', () => fileInput.click());
        
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                const file = this.files[0];
                fileInfo.innerHTML = `
                    <strong>${file.name}</strong>
                    <span class="d-block">${(file.size / 1024 / 1024).toFixed(2)} MB</span>
                `;
                uploadArea.style.borderColor = '#28a745';
                uploadArea.style.backgroundColor = 'rgba(40, 167, 69, 0.05)';
            }
        });
        
        // Drag and drop functionality
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.style.borderColor = '#28a745';
            uploadArea.style.backgroundColor = 'rgba(40, 167, 69, 0.1)';
        });
        
        uploadArea.addEventListener('dragleave', () => {
            uploadArea.style.borderColor = fileInput.files.length ? '#28a745' : '#ccc';
            uploadArea.style.backgroundColor = fileInput.files.length 
                ? 'rgba(40, 167, 69, 0.05)' 
                : '#fff';
        });
        
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            fileInput.files = e.dataTransfer.files;
            const event = new Event('change');
            fileInput.dispatchEvent(event);
        });
    </script>
</body>
</html>