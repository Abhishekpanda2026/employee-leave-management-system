-- Sample Data for Employee Leave Management System

USE leave_management;

-- Insert sample admin user
INSERT INTO users (username, email, password, first_name, last_name, employee_id, department, position, role, date_joined, is_active) VALUES
('admin', 'admin@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'admin', 'ADM001', 'IT', 'System Admin', 'admin', '2023-01-15', 1);

-- Insert sample employee users
INSERT INTO users (username, email, password, first_name, last_name, employee_id, department, position, role, date_joined, is_active) VALUES
('john_doe', 'john.doe@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Doe', 'EMP001', 'Engineering', 'Software Engineer', 'employee', '2023-02-01', 1),
('babun_panda', 'babunpanda@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'babun', 'panda', 'EMP002', 'Marketing', 'Marketing Manager', '2025-03-15', 1),
('robert_wilson', 'robert.wilson@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Robert', 'Wilson', 'EMP003', 'HR', 'HR Specialist', '2023-01-10', 1),
('sarah_johnson', 'sarah.johnson@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah', 'Johnson', 'EMP004', 'Finance', 'Accountant', '2023-04-20', 1);

-- Insert additional leave types if needed (already inserted in schema)
-- The following are additional leave types that might be needed
INSERT INTO leave_types (name, description, max_days_per_year, carry_forward) VALUES
('Bereavement Leave', 'Leave for bereavement', 5, 0),
('Study Leave', 'Leave for educational purposes', 10, 0);

-- Insert leave balances for each employee for the current year (2023)
-- Annual Leave balances
INSERT INTO leave_balances (user_id, leave_type_id, year, allocated_days, used_days, remaining_days) VALUES
(2, 1, 2023, 20.00, 5.00, 15.00), -- John Doe - Annual Leave
(3, 1, 2023, 20.00, 0.00, 20.00), -- Jane Smith - Annual Leave
(4, 1, 2023, 20.00, 10.00, 10.00), -- Robert Wilson - Annual Leave
(5, 1, 2023, 20.00, 3.00, 17.00), -- Sarah Johnson - Annual Leave

-- Sick Leave balances
(2, 2, 2023, 10.00, 2.00, 8.00), -- John Doe - Sick Leave
(3, 2, 2023, 10.00, 0.00, 10.00), -- Jane Smith - Sick Leave
(4, 2, 2023, 10.00, 1.00, 9.00), -- Robert Wilson - Sick Leave
(5, 2, 2023, 10.00, 0.00, 10.00), -- Sarah Johnson - Sick Leave

-- Casual Leave balances
(2, 3, 2023, 5.00, 1.00, 4.00), -- John Doe - Casual Leave
(3, 3, 2023, 5.00, 0.00, 5.00), -- Jane Smith - Casual Leave
(4, 3, 2023, 5.00, 0.00, 5.00), -- Robert Wilson - Casual Leave
(5, 3, 2023, 5.00, 0.00, 5.00); -- Sarah Johnson - Casual Leave

-- Insert sample leave requests
INSERT INTO leave_requests (user_id, leave_type_id, start_date, end_date, reason, status, days_requested, approved_by, approved_at, created_at) VALUES
-- Pending requests
(2, 1, '2023-12-15', '2023-12-20', 'Family vacation', 'pending', 5, NULL, NULL, '2023-11-30 09:30:00'),
(3, 1, '2023-12-25', '2023-12-29', 'Holiday break', 'pending', 4, NULL, NULL, '2023-12-01 14:20:00'),

-- Approved requests
(2, 2, '2023-11-10', '2023-11-10', 'Medical appointment', 'approved', 1, 1, '2023-11-09 16:45:00', '2023-11-08 10:15:00'),
(4, 1, '2023-10-01', '2023-10-05', 'Personal time off', 'approved', 5, 1, '2023-09-28 11:30:00', '2023-09-25 13:20:00'),

-- Rejected requests
(5, 1, '2023-11-20', '2023-11-22', 'Family emergency', 'rejected', 3, 1, '2023-11-18 15:20:00', '2023-11-15 08:45:00'),

-- Additional approved requests
(2, 1, '2023-08-15', '2023-08-20', 'Summer vacation', 'approved', 5, 1, '2023-08-10 12:00:00', '2023-08-05 10:00:00'),
(3, 2, '2023-09-05', '2023-09-05', 'Doctor appointment', 'approved', 1, 1, '2023-09-04 09:15:00', '2023-09-03 14:30:00');

-- Insert more sample data for 2024 balances
INSERT INTO leave_balances (user_id, leave_type_id, year, allocated_days, used_days, remaining_days) VALUES
-- Annual Leave balances for 2024
(2, 1, 2024, 20.00, 0.00, 20.00), -- John Doe - Annual Leave
(3, 1, 2024, 20.00, 0.00, 20.00), -- Jane Smith - Annual Leave
(4, 1, 2024, 20.00, 0.00, 20.00), -- Robert Wilson - Annual Leave
(5, 1, 2024, 20.00, 0.00, 20.00), -- Sarah Johnson - Annual Leave

-- Sick Leave balances for 2024
(2, 2, 2024, 10.00, 0.00, 10.00), -- John Doe - Sick Leave
(3, 2, 2024, 10.00, 0.00, 10.00), -- Jane Smith - Sick Leave
(4, 2, 2024, 10.00, 0.00, 10.00), -- Robert Wilson - Sick Leave
(5, 2, 2024, 10.00, 0.00, 10.00), -- Sarah Johnson - Sick Leave

-- Casual Leave balances for 2024
(2, 3, 2024, 5.00, 0.00, 5.00), -- John Doe - Casual Leave
(3, 3, 2024, 5.00, 0.00, 5.00), -- Jane Smith - Casual Leave
(4, 3, 2024, 5.00, 0.00, 5.00), -- Robert Wilson - Casual Leave
(5, 3, 2024, 5.00, 0.00, 5.00); -- Sarah Johnson - Casual Leave

-- Insert a sample leave request for 2024
INSERT INTO leave_requests (user_id, leave_type_id, start_date, end_date, reason, status, days_requested, created_at) VALUES
(2, 1, '2024-02-15', '2024-02-20', 'Winter vacation', 'pending', 5, '2024-01-15 10:30:00');

-- Update some leave balances to reflect approved requests
-- For John Doe (user_id 2) Annual Leave
UPDATE leave_balances SET used_days = 10.00, remaining_days = 10.00 WHERE user_id = 2 AND leave_type_id = 1 AND year = 2023;

-- For Robert Wilson (user_id 4) Annual Leave
UPDATE leave_balances SET used_days = 15.00, remaining_days = 5.00 WHERE user_id = 4 AND leave_type_id = 1 AND year = 2023;

-- For Sarah Johnson (user_id 5) Annual Leave
UPDATE leave_balances SET used_days = 3.00, remaining_days = 17.00 WHERE user_id = 5 AND leave_type_id = 1 AND year = 2023;

-- For John Doe (user_id 2) Sick Leave
UPDATE leave_balances SET used_days = 3.00, remaining_days = 7.00 WHERE user_id = 2 AND leave_type_id = 2 AND year = 2023;

-- For Robert Wilson (user_id 4) Sick Leave
UPDATE leave_balances SET used_days = 2.00, remaining_days = 8.00 WHERE user_id = 4 AND leave_type_id = 2 AND year = 2023;