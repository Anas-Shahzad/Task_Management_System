<?php
session_start();
require_once '../includes/connection.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if(isset($_POST['adminlogin'])) {
    // Sanitize inputs
    $email = mysqli_real_escape_string($connection, $_POST['email']);
    $password = $_POST['password'];

    // Query to check if admin exists
    $query = "SELECT id, name, password FROM admins WHERE email=?";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if(mysqli_num_rows($result) == 1) {
        $admin = mysqli_fetch_assoc($result);
        
        // Verify password (plain text comparison - UNSECURE, see note below)
        if($password === $admin['password']) {
            // Set admin session variables
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['user_type'] = 'admin';
            $_SESSION['logged_in'] = true;

            // Redirect to dashboard
            header("Location: admin_dashboard.php");
            exit();
        }
    }
    
    // Login failed
    $_SESSION['error'] = "Invalid email or password";
    header("Location: admin_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <style>
        /* Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }
        
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #355764, #5A8F7B);
            padding: 20px;
        }
        
        .wrapper {
            width: 100%;
            max-width: 420px;
            background-color: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(15px);
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            color: #fff;
            border-radius: 15px;
            padding: 30px;
            transition: all 0.3s ease;
        }
        
        .wrapper h1 {
            font-size: 2rem;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .input-box {
            position: relative;
            width: 100%;
            height: 50px;
            margin: 20px 0;
        }
        
        .input-box input {
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 40px;
            font-size: 16px;
            color: #fff;
            padding: 15px 45px 15px 20px;
            outline: none;
            transition: all 0.3s ease;
        }
        
        .input-box input:focus {
            border-color: #fff;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
        }
        
        .input-box input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }
        
        .input-box i {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 18px;
        }
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 14px;
            margin: 15px 0;
        }
        
        .remember-forgot label {
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
        }
        
        .remember-forgot input {
            accent-color: #fff;
        }
        
        .remember-forgot a {
            color: #fff;
            text-decoration: none;
        }
        
        .remember-forgot a:hover {
            text-decoration: underline;
        }
        
        .btn {
            width: 100%;
            height: 45px;
            background: #fff;
            border: none;
            border-radius: 40px;
            font-size: 16px;
            font-weight: 600;
            color: #333;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            background: rgba(255, 255, 255, 0.9);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .register-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        
        .register-link a {
            color: #fff;
            text-decoration: none;
            font-weight: 600;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
        
        .alert {
            margin-bottom: 20px;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Responsive Adjustments */
        @media (max-width: 576px) {
            .wrapper {
                padding: 25px;
            }
            
            .wrapper h1 {
                font-size: 1.8rem;
            }
            
            .input-box {
                height: 45px;
                margin: 15px 0;
            }
            
            .remember-forgot {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
        
        @media (max-width: 400px) {
            .wrapper {
                padding: 20px;
            }
            
            .wrapper h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <form method="post" action="">
            <h1>Admin Login</h1>
            
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <div class="input-box">
                <input type="text" name="email" placeholder="Email" required>
                <i class="fas fa-envelope"></i>
            </div>

            <div class="input-box">
                <input type="password" name="password" placeholder="Password" required>
                <i class="fas fa-lock"></i>
                <span style="position: absolute; right: 45px; top: 50%; transform: translateY(-50%); cursor: pointer;" onclick="togglePassword()">
                </span>
            </div>
            
            <div class="remember-forgot">
                <label>
                    <input type="checkbox"> Remember Me
                </label>
                <a href="../forgot_password.php">Forgot Password?</a>
            </div>

            <button type="submit" name="adminlogin" class="btn btn-success">Login</button>
            
            <div class="register-link">
                <p>Don't have an account? <a href="admin_register_info.php">Register</a></p>
            </div>
        </form>
    </div>

    <script>
        function togglePassword() {
            const passwordField = document.querySelector('input[name="password"]');
            const eyeIcon = document.querySelector('.fa-eye');
            
            if (passwordField.type === "password") {
                passwordField.type = "text";
                eyeIcon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passwordField.type = "password";
                eyeIcon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
        
        // Animation on load
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('.wrapper').style.opacity = '1';
            document.querySelector('.wrapper').style.transform = 'translateY(0)';
        });
    </script>
</body>
</html>