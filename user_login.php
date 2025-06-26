<?php
session_start();
include('includes/connection.php');

if(isset($_POST['userlogin'])) {
    // Sanitize inputs
    $email = mysqli_real_escape_string($connection, $_POST['email']);
    $password = mysqli_real_escape_string($connection, $_POST['password']);

    // Query to check if user exists
    $query = "SELECT id, name, password FROM users WHERE email='$email'";
    $result = mysqli_query($connection, $query);

    if(mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Verify password
        if(password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['logged_in'] = true;

            // Redirect to dashboard or home page
            echo "<script>
                alert('Login successful!');
                window.location.href = 'user_dashboard.php';
                </script>";
        } else {
            echo "<script>
                alert('Incorrect password!');
                window.location.href = 'user_login.php';
                </script>";
        }
    } else {
        echo "<script>
            alert('Email not registered! Please Register Your Email');
            window.location.href = 'register.php';
            </script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    
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
            padding: 30px 40px;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.5s ease forwards;
        }
        
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .wrapper h1 {
            font-size: 2rem;
            text-align: center;
            margin-bottom: 30px;
            text-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }
        
        .input-box {
            position: relative;
            width: 100%;
            height: 50px;
            margin: 25px 0;
        }
        
        .input-box input {
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 40px;
            font-size: 16px;
            color: #fff;
            padding: 20px 45px 20px 20px;
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
            color: rgba(255, 255, 255, 0.8);
        }
        
        .toggle-password {
            position: absolute;
            right: 45px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: rgba(255, 255, 255, 0.8);
            font-size: 16px;
        }
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 14px;
            margin: 20px 0;
        }
        
        .remember-forgot label {
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
        }
        
        .remember-forgot input {
            accent-color: #fff;
            cursor: pointer;
        }
        
        .remember-forgot a {
            color: #fff;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .remember-forgot a:hover {
            text-decoration: underline;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .btn {
            width: 100%;
            height: 45px;
            background: #fff;
            border: none;
            outline: none;
            border-radius: 40px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            font-size: 16px;
            color: #333;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            background: rgba(255, 255, 255, 0.9);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .register-link {
            text-align: center;
            font-size: 14px;
            margin: 25px 0 15px;
        }
        
        .register-link p {
            color: rgba(255, 255, 255, 0.8);
        }
        
        .register-link a {
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .register-link a:hover {
            text-decoration: underline;
            color: rgba(255, 255, 255, 0.8);
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .wrapper {
                padding: 30px;
            }
            
            .wrapper h1 {
                font-size: 1.8rem;
            }
        }
        
        @media (max-width: 576px) {
            .wrapper {
                padding: 25px;
                border-radius: 10px;
            }
            
            .wrapper h1 {
                font-size: 1.6rem;
                margin-bottom: 25px;
            }
            
            .input-box {
                height: 45px;
                margin: 20px 0;
            }
            
            .remember-forgot {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
        }
        
        @media (max-width: 400px) {
            .wrapper {
                padding: 20px;
            }
            
            .wrapper h1 {
                font-size: 1.4rem;
            }
            
            .btn {
                height: 42px;
                font-size: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <form method="POST" action="">
            <h1>User Login</h1>

            <div class="input-box">
                <input type="email" name="email" placeholder="Email" required>
                <i class="fas fa-envelope"></i>
            </div>

            <div class="input-box">
                <input type="password" name="password" placeholder="Password" required>
                <i class="fas fa-lock"></i>
                <span class="toggle-password" onclick="togglePassword()">
                    
                </span>
            </div>
            
            <div class="remember-forgot">
                <label>
                    <input type="checkbox"> Remember Me
                </label>
                <a href="forgot_password.php">Forgot Password?</a>
            </div>

            <button type="submit" name="userlogin" class="btn btn-success">Login</button>
            
            <div class="register-link">
                <p>Don't have an account? <a href="register.php">Register</a></p>
            </div>
        </form>
    </div>

    <script>
        function togglePassword() {
            const passwordField = document.querySelector('input[name="password"]');
            const eyeIcon = document.querySelector('.toggle-password i');
            
            if (passwordField.type === "password") {
                passwordField.type = "text";
                eyeIcon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passwordField.type = "password";
                eyeIcon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
        
        // Focus animation for inputs
        document.querySelectorAll('.input-box input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.querySelector('i').style.color = '#fff';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.querySelector('i').style.color = 'rgba(255, 255, 255, 0.8)';
            });
        });
    </script>
</body>
</html>