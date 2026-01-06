# Employee Leave Management System

A complete leave management system built with Core PHP, MySQL, HTML, CSS, and JavaScript.

## Features

- User authentication (Admin & Employee)
- Leave request management
- Leave approval workflow
- Leave balance tracking
- Admin dashboard
- Employee dashboard
- Monthly calendar view
- Responsive design using Bootstrap 5

## Security Features Implemented

- Input sanitization using `htmlspecialchars()` and custom validation functions
- SQL injection protection with prepared statements
- Session security with strict mode and HttpOnly cookies
- Client and server-side form validation
- CSRF protection through session validation
- Password hashing using PHP's `password_hash()` function

## How to Run the Project in XAMPP

### Prerequisites
- XAMPP with Apache and MySQL
- PHP 7.4 or higher
- MySQL 5.7 or higher

### Installation Steps

1. **Copy the Project Files**
   - Place the entire project folder in your XAMPP `htdocs` directory
   - Default location: `C:\xampp\htdocs\leave-mng`

2. **Start XAMPP Services**
   - Open XAMPP Control Panel
   - Start Apache and MySQL services

3. **Import the Database**
   - Open your web browser and go to `http://localhost/phpmyadmin`
   - Click on "Databases" tab
   - Create a new database named `leave_management`
   - Click on the newly created database
   - Go to "Import" tab
   - Click "Choose File" and select `database_schema.sql` from the project root
   - Click "Go" to import the database schema
   - Import `sample_data.sql` to add sample data (optional)

4. **Configure Database Connection**
   - The database connection is already configured in `config/database.php`
   - Default settings: 
     - Host: `localhost`
     - Username: `root`
     - Password: `''` (empty)
     - Database: `leave_management`

5. **Access the Application**
   - Open your web browser
   - Go to `http://localhost/leave-mng`
   - You will be redirected to the login page

## Default Admin Credentials

- **Username:** `admin`
- **Password:** `admin123`

## Default Employee Credentials

- **Username:** `john_doe`, `jane_smith`, `robert_wilson`, `sarah_johnson`
- **Password:** `password123`

## Project Structure

```
leave-mng/
├── admin/                 # Admin dashboard and pages
│   ├── dashboard.php
│   ├── manage_leaves.php
│   └── users.php
├── employee/              # Employee dashboard and pages
│   ├── dashboard.php
│   ├── apply_leave.php
│   ├── my_leaves.php
│   └── calendar.php
├── assets/                # CSS, JS, and image files
│   ├── css/
│   ├── js/
│   └── images/
├── config/                # Configuration files
│   └── database.php
├── includes/              # Common includes
│   └── auth.php
├── database_schema.sql    # Database schema
├── sample_data.sql        # Sample data
└── login.php              # Login page
```

## Security Measures

1. **Input Sanitization**
   - All user inputs are sanitized using `htmlspecialchars()` and custom validation functions
   - SQL injection prevention with prepared statements

2. **Session Security**
   - Session cookies configured with HttpOnly and secure flags
   - Session ID regeneration after login
   - Session validation on each page

3. **Form Validation**
   - Client-side validation using JavaScript
   - Server-side validation with PHP
   - Data type validation and sanitization

4. **Password Security**
   - Passwords stored using PHP's `password_hash()` with bcrypt algorithm
   - Secure password verification using `password_verify()`

5. **Access Control**
   - Role-based access control (Admin/Employee)
   - Protected pages with authentication middleware
   - Proper redirection based on user roles

## Usage

1. **Admin Functions:**
   - View and manage all employee leave requests
   - Approve or reject leave applications
   - View employee information
   - Access to calendar view for all employees

2. **Employee Functions:**
   - Apply for leave
   - View leave balance
   - Check leave request status
   - View calendar of approved leaves

## Database Schema

The system uses four main tables:

1. `users` - Stores user information (id, username, email, password, role, etc.)
2. `leave_types` - Defines different leave types (id, name, description, etc.)
3. `leave_requests` - Stores leave requests (id, user_id, leave_type_id, dates, status, etc.)
4. `leave_balances` - Tracks leave balances per user (user_id, leave_type_id, year, etc.)

## Customization

- Modify `config/database.php` to change database connection settings
- Update CSS files in `assets/css/` for styling changes
- Add new leave types in the database or through admin panel
- Customize validation rules in the PHP files as needed