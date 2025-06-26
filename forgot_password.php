<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Recovery | TMS</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
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
        
        .password-container {
            width: 100%;
            max-width: 500px;
        }
        
        .password-box {
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
            animation: fadeInUp 0.5s 0.3s ease forwards;
        }
        
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .password-box h1 {
            font-size: 2rem;
            text-align: center;
            margin-bottom: 30px;
            color: #fff;
            text-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }
        
        .message-box {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
            border-left: 4px solid rgba(255, 255, 255, 0.5);
            transition: all 0.3s ease;
        }
        
        .message-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        .message-box i {
            font-size: 3rem;
            margin-bottom: 20px;
            display: block;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .message-box p {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .back-btn {
            width: 100%;
            height: 50px;
            background: rgba(255, 255, 255, 0.9);
            border: none;
            outline: none;
            border-radius: 40px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            font-size: 16px;
            color: #333;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .back-btn:hover {
            background: #fff;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .progress-container {
            width: 100%;
            height: 6px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
            margin-top: 30px;
            overflow: hidden;
        }
        
        .progress-bar {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #33d9b2, #218c74);
            border-radius: 3px;
            animation: progressAnimation 3s ease-in-out infinite;
        }
        
        @keyframes progressAnimation {
            0% { width: 0%; }
            50% { width: 100%; }
            100% { width: 0%; }
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .password-box {
                padding: 30px;
            }
            
            .password-box h1 {
                font-size: 1.8rem;
            }
        }
        
        @media (max-width: 576px) {
            .password-box {
                padding: 25px;
                border-radius: 10px;
            }
            
            .password-box h1 {
                font-size: 1.6rem;
                margin-bottom: 25px;
            }
            
            .message-box {
                padding: 20px;
            }
            
            .message-box i {
                font-size: 2.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="password-container">
        <div class="password-box animate__animated">
            <h1>Password Recovery</h1>
            
            <div class="message-box animate__animated animate__pulse animate__delay-1s">
                <i class="fas fa-tools"></i>
                <h3>Service Temporarily Unavailable</h3>
                <p>We're currently upgrading our password recovery system to serve you better. This feature will be available soon with enhanced security measures.</p>
                <p>Please contact our support team for immediate assistance.</p>
            </div>
            
            <div class="progress-container">
                <div class="progress-bar"></div>
            </div>
            
            <button onclick="window.location.href='user_login.php'" class="back-btn animate__animated animate__fadeIn animate__delay-2s">
                <i class="fas fa-arrow-left"></i> Back to Login
            </button>
        </div>
    </div>

    <script>
        // Add animation class on load
        document.addEventListener('DOMContentLoaded', function() {
            const box = document.querySelector('.password-box');
            box.classList.add('animate__fadeInUp');
            
            // Animate the message box
            setTimeout(() => {
                const messageBox = document.querySelector('.message-box');
                messageBox.classList.add('animate__heartBeat');
            }, 1500);
        });
    </script>
</body>
</html>