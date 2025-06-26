<?php
include('includes/connection.php');

if(isset($_POST['userregistration'])) {
    // Sanitize inputs
    $name = mysqli_real_escape_string($connection, $_POST['name']);
    $email = mysqli_real_escape_string($connection, $_POST['email']);
    $password = mysqli_real_escape_string($connection, $_POST['password']);
    $mobile = mysqli_real_escape_string($connection, $_POST['mobile']);
    
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $query = "INSERT INTO users (name, email, password, mobile) 
              VALUES ('$name', '$email', '$hashed_password', '$mobile')";
    
    $query_run = mysqli_query($connection, $query);
    
    if($query_run) {
        echo "<script type='text/javascript'>
            alert('User registered successfully...');
            window.location.href = 'index.php';
            </script>";
    }
    else {
        echo "<script type='text/javascript'>
            alert('Error occurred: ".mysqli_error($connection)."');
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
    <title>User Registration</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
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
            background-size: cover;
            background-position: center;
            padding: 20px;
        }
        
        .registration-container {
            width: 100%;
            max-width: 500px;
            transition: all 0.3s ease;
        }
        
        .wrapper {
            width: 100%;
            background-color: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(15px);
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            color: #fff;
            border-radius: 15px;
            padding: 40px;
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
            font-size: 2.2rem;
            text-align: center;
            margin-bottom: 30px;
            color: #fff;
            text-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }
        
        .input-group {
            position: relative;
            margin-bottom: 25px;
        }
        
        .input-box {
            position: relative;
            width: 100%;
            height: 50px;
        }
        
        .input-box input {
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            border: none;
            outline: none;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 40px;
            font-size: 16px;
            color: #fff;
            padding: 20px 45px 20px 20px;
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
        
        .password-strength {
            width: 100%;
            height: 5px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
            margin-top: 5px;
            overflow: hidden;
            display: none;
        }
        
        .strength-meter {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
        }
        
        .remember-forgot {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            font-size: 14px;
            margin: 20px 0;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
        }
        
        .remember-me input {
            accent-color: #fff;
            cursor: pointer;
        }
        
        .register-btn {
            width: 100%;
            height: 50px;
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
            margin-top: 10px;
        }
        
        .register-btn:hover {
            background: rgba(255, 255, 255, 0.9);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .login-link {
            text-align: center;
            font-size: 14px;
            margin: 25px 0 15px;
        }
        
        .login-link p {
            color: rgba(255, 255, 255, 0.8);
        }
        
        .login-link a {
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .login-link a:hover {
            text-decoration: underline;
            color: rgba(255, 255, 255, 0.8);
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .wrapper {
                padding: 30px;
            }
            
            .wrapper h1 {
                font-size: 2rem;
            }
        }
        
        @media (max-width: 576px) {
            .wrapper {
                padding: 25px;
                border-radius: 10px;
            }
            
            .wrapper h1 {
                font-size: 1.8rem;
                margin-bottom: 25px;
            }
            
            .input-box {
                height: 45px;
            }
            
            .input-box input {
                padding: 15px 40px 15px 15px;
            }
        }
        
        @media (max-width: 480px) {
            .wrapper {
                padding: 20px;
            }
            
            .wrapper h1 {
                font-size: 1.6rem;
            }
            
            .register-btn {
                height: 45px;
                font-size: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="registration-container">
        <div class="wrapper">
            <form method="POST" action="">
                <h1>User Registration</h1>

                <div class="input-group">
                    <div class="input-box">
                        <input type="text" name="name" placeholder="Full Name" required>
                        <i class="fas fa-user"></i>
                    </div>
                </div>

                <div class="input-group">
                    <div class="input-box">
                        <input type="email" name="email" placeholder="Email" required>
                        <i class="fas fa-envelope"></i>
                    </div>
                </div>

                <div class="input-group">
                    <div class="input-box">
                        <input type="text" name="mobile" placeholder="Mobile Number" required>
                        <i class="fas fa-phone"></i>
                    </div>
                </div>

                <div class="input-group">
                    <div class="input-box">
                        <input type="password" name="password" id="password" placeholder="Password" required>
                        <i class="fas fa-lock"></i>
                        <span class="toggle-password" onclick="togglePassword()">
                            
                        </span>
                    </div>
                    <div class="password-strength" id="password-strength">
                        <div class="strength-meter" id="strength-meter"></div>
                    </div>
                </div>
                
                <div class="remember-forgot">
                    <label class="remember-me">
                        <input type="checkbox" required> I agree to the terms & conditions
                    </label>
                </div>

                <button type="submit" name="userregistration" class="register-btn">Register</button>
                
                <div class="login-link">
                    <p>Already have an account? <a href="user_login.php">Login</a></p>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Password toggle visibility
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const eyeIcon = document.querySelector('.toggle-password i');
            
            if (passwordField.type === "password") {
                passwordField.type = "text";
                eyeIcon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passwordField.type = "password";
                eyeIcon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
        
        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthMeter = document.getElementById('strength-meter');
            const passwordStrength = document.getElementById('password-strength');
            
            if (password.length > 0) {
                passwordStrength.style.display = 'block';
                
                // Calculate strength (simple example)
                let strength = 0;
                if (password.length >= 8) strength += 1;
                if (password.match(/[A-Z]/)) strength += 1;
                if (password.match(/[0-9]/)) strength += 1;
                if (password.match(/[^A-Za-z0-9]/)) strength += 1;
                
                // Update meter
                const width = strength * 25;
                strengthMeter.style.width = width + '%';
                
                // Update color
                if (width < 50) {
                    strengthMeter.style.background = '#ff5252'; // Red
                } else if (width < 75) {
                    strengthMeter.style.background = '#ffb142'; // Orange
                } else {
                    strengthMeter.style.background = '#33d9b2'; // Green
                }
            } else {
                passwordStrength.style.display = 'none';
            }
        });
        
        // Add focus effects
        document.querySelectorAll('.input-box input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.querySelector('i').style.color = '#fff';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.querySelector('i').style.color = 'rgba(255, 255, 255, 0.8)';
            });
        });
        
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const termsChecked = document.querySelector('input[type="checkbox"]').checked;
            if (!termsChecked) {
                e.preventDefault();
                alert('Please agree to the terms and conditions');
            }
        });
    </script>
</body>
</html>