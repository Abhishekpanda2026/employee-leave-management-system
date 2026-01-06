-- SQL query for fetching leave data for calendar view

-- Query to get all leave requests for a specific user within a date range
SELECT 
    lr.id,
    lr.user_id,
    lr.leave_type_id,
    lr.start_date,
    lr.end_date,
    lr.reason,
    lr.status,
    lr.days_requested,
    lt.name as leave_type_name
FROM leave_requests lr
JOIN leave_types lt ON lr.leave_type_id = lt.id
WHERE lr.user_id = ? 
  AND lr.status IN ('approved', 'pending', 'rejected')
  AND (
    (lr.start_date <= ? AND lr.end_date >= ?) -- Date range overlaps with month
    OR 
    (lr.start_date <= ? AND lr.end_date >= ?) -- Month falls within leave period
  )
ORDER BY lr.start_date;

-- Alternative query to get all leave requests for a specific month
SELECT 
    lr.id,
    lr.user_id,
    u.first_name,
    u.last_name,
    lr.leave_type_id,
    lr.start_date,
    lr.end_date,
    lr.reason,
    lr.status,
    lt.name as leave_type_name
FROM leave_requests lr
JOIN users u ON lr.user_id = u.id
JOIN leave_types lt ON lr.leave_type_id = lt.id
WHERE lr.status IN ('approved', 'pending', 'rejected')
  AND YEAR(lr.start_date) = ? -- Specify year
  AND MONTH(lr.start_date) = ? -- Specify month
ORDER BY lr.start_date;

-- Query to get all leave requests that span a specific month (for admin view)
SELECT 
    lr.id,
    lr.user_id,
    u.first_name,
    u.last_name,
    lr.leave_type_id,
    lr.start_date,
    lr.end_date,
    lr.reason,
    lr.status,
    lt.name as leave_type_name
FROM leave_requests lr
JOIN users u ON lr.user_id = u.id
JOIN leave_types lt ON lr.leave_type_id = lt.id
WHERE lr.status IN ('approved', 'pending', 'rejected')
  AND (
    (YEAR(lr.start_date) = ? AND MONTH(lr.start_date) = ?) -- Starts in this month
    OR 
    (YEAR(lr.end_date) = ? AND MONTH(lr.end_date) = ?) -- Ends in this month
    OR 
    (lr.start_date <= CONCAT(?, '-', ?, '-01') AND lr.end_date >= CONCAT(?, '-', ?, '-31')) -- Spans this month
  )
ORDER BY lr.start_date;