# SDCKL Attendance System Database

This directory contains the database schema and sample data for the SDCKL Student Attendance Management System.

## Database Structure

The database consists of the following main tables:

1. `users` - Stores admin and teacher accounts
2. `classes` - Manages class information
3. `students` - Stores student records
4. `attendance` - Records daily attendance
5. `holidays` - Tracks school holidays
6. `attendance_settings` - System configuration settings

## Views

1. `daily_attendance_summary` - Provides daily attendance statistics by class
2. `student_attendance_percentage` - Calculates attendance percentages for each student

## Setup Instructions

1. Install MySQL Server (version 5.7 or higher)

2. Create and populate the database:
```bash
# Login to MySQL
mysql -u root -p

# Create and populate the database
mysql> source schema.sql
mysql> source sample_data.sql
```

3. Default Login Credentials:
- Username: admin
- Password: admin123

## Database Relationships

- Students belong to Classes (many-to-one)
- Attendance records are linked to Students (many-to-one)
- Classes can have a Teacher (many-to-one with users table)

## Important Notes

1. The schema includes indexes for optimized query performance
2. Foreign key constraints ensure data integrity
3. Timestamps are automatically managed for created_at and updated_at fields
4. The sample data includes:
   - 4 students
   - 4 classes
   - 5 days of attendance records
   - Sample holidays and settings

## Backup Recommendations

1. Regular backups:
```bash
mysqldump -u root -p sdckl_attendance > backup_$(date +%Y%m%d).sql
```

2. Restore from backup:
```bash
mysql -u root -p sdckl_attendance < backup_file.sql
```

## Common Queries

1. Get daily attendance for a class:
```sql
SELECT * FROM daily_attendance_summary 
WHERE class_name = '10A' 
AND date = CURRENT_DATE;
```

2. Get student attendance percentage:
```sql
SELECT * FROM student_attendance_percentage 
WHERE class_name = '10A';
```

3. Get absent students for today:
```sql
SELECT s.student_id, s.full_name, s.contact_number, s.parent_contact
FROM students s
LEFT JOIN attendance a ON s.student_id = a.student_id 
AND a.date = CURRENT_DATE
WHERE a.attendance_id IS NULL 
AND s.status = 'active';
```

## Security Considerations

1. The password_hash field uses bcrypt encryption
2. User roles are strictly enforced (admin/teacher)
3. Sensitive operations are logged with user tracking
4. All dates and times are stored in UTC

## Maintenance

1. Regular cleanup of old records:
```sql
DELETE FROM attendance 
WHERE date < DATE_SUB(CURRENT_DATE, INTERVAL 5 YEAR);
```

2. Optimize tables:
```sql
OPTIMIZE TABLE students, attendance, classes;
```

For additional support or questions, please refer to the system documentation or contact the system administrator.
