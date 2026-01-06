# Database Relationships for Employee Leave Management System

## Table Relationships Overview

### 1. Users Table Relationships
- **Primary Table**: `users`
- **Purpose**: Stores user information for both admins and employees

### 2. Leave Types Table Relationships
- **Primary Table**: `leave_types`
- **Purpose**: Defines different types of leave available

### 3. Leave Requests Table Relationships
- **Primary Table**: `leave_requests`
- **Foreign Key Relationships**:
  - `user_id` → `users.id` (Many-to-One)
    - Each leave request belongs to one user
    - One user can have multiple leave requests
  - `leave_type_id` → `leave_types.id` (Many-to-One)
    - Each leave request is for one specific leave type
    - One leave type can be used in multiple leave requests
  - `approved_by` → `users.id` (Many-to-One, Optional)
    - Each leave request can be approved by one admin user
    - One admin can approve multiple leave requests
  - `rejected_by` → `users.id` (Many-to-One, Optional)
    - Each leave request can be rejected by one admin user
    - One admin can reject multiple leave requests

### 4. Leave Balances Table Relationships
- **Primary Table**: `leave_balances`
- **Foreign Key Relationships**:
  - `user_id` → `users.id` (Many-to-One)
    - Each leave balance record belongs to one user
    - One user can have multiple leave balance records (for different leave types and years)
  - `leave_type_id` → `leave_types.id` (Many-to-One)
    - Each leave balance record is for one specific leave type
    - One leave type can be associated with multiple leave balance records

## Detailed Relationship Mappings

### Users Table
- `leave_requests.user_id` → `users.id` (One-to-Many)
- `leave_balances.user_id` → `users.id` (One-to-Many)
- `leave_requests.approved_by` → `users.id` (One-to-Many, Optional)
- `leave_requests.rejected_by` → `users.id` (One-to-Many, Optional)

### Leave Types Table
- `leave_requests.leave_type_id` → `leave_types.id` (One-to-Many)
- `leave_balances.leave_type_id` → `leave_types.id` (One-to-Many)

## Constraints and Cascade Rules

### Foreign Key Constraints
1. `leave_requests.user_id` → `users.id`: CASCADE DELETE
   - When a user is deleted, all their leave requests are also deleted

2. `leave_balances.user_id` → `users.id`: CASCADE DELETE
   - When a user is deleted, all their leave balances are also deleted

3. `leave_requests.leave_type_id` → `leave_types.id`: RESTRICT DELETE
   - Prevents deletion of a leave type if it's referenced in any leave request

4. `leave_balances.leave_type_id` → `leave_types.id`: RESTRICT DELETE
   - Prevents deletion of a leave type if it's referenced in any leave balance

5. `leave_requests.approved_by` → `users.id`: SET NULL ON DELETE
   - When an admin user is deleted, the approved_by field becomes NULL

6. `leave_requests.rejected_by` → `users.id`: SET NULL ON DELETE
   - When an admin user is deleted, the rejected_by field becomes NULL

## Unique Constraints

1. `users.username`: Must be unique across all users
2. `users.email`: Must be unique across all users
3. `users.employee_id`: Must be unique across all users
4. `leave_types.name`: Must be unique across all leave types
5. `leave_balances` (user_id, leave_type_id, year): Unique combination to prevent duplicate balances