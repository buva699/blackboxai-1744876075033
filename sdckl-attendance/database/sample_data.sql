USE sdckl_attendance;

-- Insert sample students
INSERT INTO students (student_id, full_name, class_id, date_of_birth, gender, contact_number, parent_name, parent_contact, address, enrollment_date, status)
VALUES 
('1001', 'John Smith', 1, '2008-05-15', 'M', '+1234567890', 'Robert Smith', '+1234567891', '123 Main St, City', '2023-09-01', 'active'),
('1002', 'Emma Johnson', 1, '2008-07-22', 'F', '+1234567892', 'Sarah Johnson', '+1234567893', '456 Oak Ave, City', '2023-09-01', 'active'),
('1003', 'Michael Brown', 2, '2008-03-10', 'M', '+1234567894', 'James Brown', '+1234567895', '789 Pine Rd, City', '2023-09-01', 'active'),
('1004', 'Sarah Wilson', 1, '2008-11-30', 'F', '+1234567896', 'David Wilson', '+1234567897', '321 Elm St, City', '2023-09-01', 'active');

-- Insert sample attendance records
INSERT INTO attendance (student_id, date, time_in, time_out, status, verification_method)
VALUES 
-- Day 1
('1001', '2024-01-15', '07:55:00', '15:00:00', 'present', 'biometric'),
('1002', '2024-01-15', '07:58:00', '15:00:00', 'present', 'biometric'),
('1003', '2024-01-15', '08:35:00', '15:00:00', 'late', 'biometric'),
('1004', '2024-01-15', '08:00:00', '15:00:00', 'present', 'biometric'),

-- Day 2
('1001', '2024-01-16', '07:50:00', '15:00:00', 'present', 'biometric'),
('1002', '2024-01-16', '08:45:00', '15:00:00', 'late', 'biometric'),
('1003', '2024-01-16', '00:00:00', '00:00:00', 'absent', 'manual'),
('1004', '2024-01-16', '07:59:00', '15:00:00', 'present', 'biometric'),

-- Day 3
('1001', '2024-01-17', '08:05:00', '15:00:00', 'present', 'biometric'),
('1002', '2024-01-17', '08:02:00', '15:00:00', 'present', 'biometric'),
('1003', '2024-01-17', '08:00:00', '15:00:00', 'present', 'biometric'),
('1004', '2024-01-17', '08:01:00', '15:00:00', 'present', 'biometric'),

-- Day 4
('1001', '2024-01-18', '07:58:00', '15:00:00', 'present', 'biometric'),
('1002', '2024-01-18', '07:59:00', '15:00:00', 'present', 'biometric'),
('1003', '2024-01-18', '08:30:00', '15:00:00', 'late', 'biometric'),
('1004', '2024-01-18', '00:00:00', '00:00:00', 'absent', 'manual'),

-- Day 5
('1001', '2024-01-19', '08:00:00', '15:00:00', 'present', 'biometric'),
('1002', '2024-01-19', '08:01:00', '15:00:00', 'present', 'biometric'),
('1003', '2024-01-19', '08:02:00', '15:00:00', 'present', 'biometric'),
('1004', '2024-01-19', '08:03:00', '15:00:00', 'present', 'biometric');

-- Insert sample holidays
INSERT INTO holidays (holiday_name, start_date, end_date, description)
VALUES 
('New Year', '2024-01-01', '2024-01-01', 'New Year''s Day celebration'),
('Spring Break', '2024-03-25', '2024-03-29', 'Annual spring break'),
('Summer Vacation', '2024-06-15', '2024-08-31', 'Summer holidays'),
('Winter Break', '2024-12-20', '2025-01-05', 'Winter holidays and New Year celebration');

-- Update some attendance settings
INSERT INTO attendance_settings (setting_name, setting_value, description)
VALUES 
('attendance_notification', 'enabled', 'Send notifications for absent students'),
('minimum_attendance_percentage', '75', 'Minimum required attendance percentage'),
('grace_period_minutes', '15', 'Grace period for late arrival in minutes'),
('auto_mark_absent_time', '10:00:00', 'Time when unmarked students are automatically marked absent');
