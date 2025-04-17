-- Create database
CREATE DATABASE IF NOT EXISTS sdckl_attendance;
USE sdckl_attendance;

-- Create Users table for admin authentication
CREATE TABLE IF NOT EXISTS users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('admin', 'teacher') NOT NULL DEFAULT 'teacher',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Create Classes table
CREATE TABLE IF NOT EXISTS classes (
    class_id INT PRIMARY KEY AUTO_INCREMENT,
    class_name VARCHAR(50) NOT NULL,
    class_teacher_id INT,
    academic_year VARCHAR(20) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (class_teacher_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Create Students table
CREATE TABLE IF NOT EXISTS students (
    student_id VARCHAR(20) PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    class_id INT NOT NULL,
    date_of_birth DATE NOT NULL,
    gender ENUM('M', 'F', 'Other') NOT NULL,
    contact_number VARCHAR(20),
    parent_name VARCHAR(100),
    parent_contact VARCHAR(20),
    address TEXT,
    enrollment_date DATE NOT NULL,
    status ENUM('active', 'inactive', 'graduated', 'transferred') DEFAULT 'active',
    biometric_data BLOB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE RESTRICT
);

-- Create Attendance table
CREATE TABLE IF NOT EXISTS attendance (
    attendance_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    student_id VARCHAR(20) NOT NULL,
    date DATE NOT NULL,
    time_in TIME,
    time_out TIME,
    status ENUM('present', 'absent', 'late', 'half_day') NOT NULL,
    marked_by INT,
    verification_method ENUM('biometric', 'manual') DEFAULT 'biometric',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE RESTRICT,
    FOREIGN KEY (marked_by) REFERENCES users(user_id) ON DELETE SET NULL,
    UNIQUE KEY unique_daily_attendance (student_id, date)
);

-- Create Holidays table
CREATE TABLE IF NOT EXISTS holidays (
    holiday_id INT PRIMARY KEY AUTO_INCREMENT,
    holiday_name VARCHAR(100) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create AttendanceSettings table
CREATE TABLE IF NOT EXISTS attendance_settings (
    setting_id INT PRIMARY KEY AUTO_INCREMENT,
    setting_name VARCHAR(50) UNIQUE NOT NULL,
    setting_value TEXT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password_hash, full_name, email, role)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin@sdckl.edu', 'admin');

-- Insert sample class
INSERT INTO classes (class_name, academic_year)
VALUES 
('10A', '2024-2025'),
('10B', '2024-2025'),
('11A', '2024-2025'),
('11B', '2024-2025');

-- Insert default attendance settings
INSERT INTO attendance_settings (setting_name, setting_value, description)
VALUES 
('school_start_time', '08:00:00', 'Regular school start time'),
('late_threshold', '08:30:00', 'Time after which students are marked as late'),
('attendance_method', 'biometric', 'Default method for marking attendance'),
('weekend_days', 'Saturday,Sunday', 'Days when school is closed');

-- Create view for daily attendance summary
CREATE VIEW daily_attendance_summary AS
SELECT 
    a.date,
    c.class_name,
    COUNT(DISTINCT a.student_id) as total_students,
    SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
    SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
    SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_count
FROM attendance a
JOIN students s ON a.student_id = s.student_id
JOIN classes c ON s.class_id = c.class_id
GROUP BY a.date, c.class_name;

-- Create view for student attendance percentage
CREATE VIEW student_attendance_percentage AS
SELECT 
    s.student_id,
    s.full_name,
    c.class_name,
    COUNT(a.attendance_id) as total_days,
    SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_days,
    ROUND((SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) * 100.0) / COUNT(a.attendance_id), 2) as attendance_percentage
FROM students s
JOIN classes c ON s.class_id = c.class_id
LEFT JOIN attendance a ON s.student_id = a.student_id
GROUP BY s.student_id, s.full_name, c.class_name;

-- Indexes for better performance
CREATE INDEX idx_attendance_date ON attendance(date);
CREATE INDEX idx_student_class ON students(class_id);
CREATE INDEX idx_student_status ON students(status);
CREATE INDEX idx_attendance_status ON attendance(status);
