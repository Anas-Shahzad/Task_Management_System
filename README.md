# 🔐 Task Management System (TMS)

![PHP](https://img.shields.io/badge/PHP-8.0+-purple.svg)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-blue.svg)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-orange.svg)

A secure web application for task management with role-based access control (Admin/User).
<br>

## ✨ Features

### Admin Panel
- Create, assign, edit, and delete tasks
- View all user submissions
- Secure session management

### User Portal
- View assigned tasks
- Submit task updates
- Responsive dashboard


## 🛠️ Installation

### Prerequisites
- PHP 8.0+
- MySQL 5.7+
- Apache/Nginx
- Composer (recommended)

### Setup
1. Clone the repository:
   ```bash
   git clone https://github.com/your-username/task-management-system.git
   cd task-management-system


## Database Setup

### Method 1: Using phpMyAdmin (GUI)
1. Open phpMyAdmin in your browser (usually `http://localhost/phpmyadmin`)
2. Create a new database:  
   - Click **New** → Name: `tms_db` → Collation: `utf8mb4_general_ci` → Create
3. Import the SQL file:  
   - Select the new database → Click **Import** → Choose File  
   - Select `Database/tms_db.sql` → Click **Go**

### Method 2: Command Line (Advanced)
```bash
- mysql -u root -p -e "CREATE DATABASE tms_db"
- mysql -u root -p tms_db < Database/tms_db.sql  


### Project Structure:
TMS/
├── Admin/
│   ├── admin_dashboard.php
│   ├── admin_login.php
│   ├── Admin_register_info
│   ├── create_task.php
│   ├── delete_task.php
│   ├── edit_task.php
│   └── bootstrap-5.3.6-dist/  # Bootstrap assets
│
├── css/
│   └── styles.css
│
├── Database/
│   ├── tms_db.sql          # Primary SQL file
│   └── tms_db.sql.zip      # Zipped backup
│
├── includes/               # Core system files only
│   ├── connection.php      # Database config
│   └── jquery.js           # jQuery library
│
├── root_files/             
│   ├── forgot_password.php
│   ├── index.php           # Entry point
│   ├── register.php
│   ├── update_task.php
│   ├── user_dashboard.php
│   └── user_login.php
│
└── README.md               # Working of the Project 
