<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TMS - Task Management System</title>
    
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
        
        .main-container {
            width: 100%;
            max-width: 600px;
            background-color: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(15px);
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            color: #fff;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            transition: all 0.3s ease;
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
        
        .main-container h3 {
            font-size: 2rem;
            margin-bottom: 30px;
            text-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }
        
        .btn-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-top: 20px;
        }
        
        .role-btn {
            width: 100%;
            height: 50px;
            border: none;
            border-radius: 40px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
        }
        
        .role-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .role-btn i {
            font-size: 1.2rem;
        }
        
        .btn-user {
            background: #28a745;
            color: white;
        }
        
        .btn-user:hover {
            background: #218838;
        }
        
        .btn-register {
            background: #ffc107;
            color: #212529;
        }
        
        .btn-register:hover {
            background: #e0a800;
        }
        
        .btn-admin {
            background: #17a2b8;
            color: white;
        }
        
        .btn-admin:hover {
            background: #138496;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .main-container {
                padding: 30px;
            }
            
            .main-container h3 {
                font-size: 1.8rem;
                margin-bottom: 25px;
            }
        }
        
        @media (max-width: 576px) {
            .main-container {
                padding: 25px;
                border-radius: 10px;
            }
            
            .main-container h3 {
                font-size: 1.5rem;
                margin-bottom: 20px;
            }
            
            .role-btn {
                height: 45px;
                font-size: 0.9rem;
            }
        }
        
        @media (max-width: 400px) {
            .main-container {
                padding: 20px;
            }
            
            .main-container h3 {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <h3>Choose Login Role</h3>
        <div class="btn-container">
            <a href="user_login.php" class="role-btn btn-user">
                <i class="fas fa-user"></i> User Login
            </a>
            <a href="register.php" class="role-btn btn-register">
                <i class="fas fa-user-plus"></i> User Registration
            </a>
            <a href="Admin/admin_login.php" class="role-btn btn-admin">
                <i class="fas fa-lock"></i> Admin Login
            </a>
        </div>
    </div>

    <script>
        // Add animation delay for buttons
        document.addEventListener('DOMContentLoaded', function() {
            const buttons = document.querySelectorAll('.role-btn');
            buttons.forEach((btn, index) => {
                btn.style.transitionDelay = `${index * 0.1}s`;
            });
        });
    </script>
</body>
</html>