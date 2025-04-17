# SDCKL Library Management System

A comprehensive library management system for SDCKL College library. This system helps librarians manage books, members, borrowings, and generate reports efficiently.

## Features

- Book Management
  - Add, edit, and delete books
  - Track book copies and availability
  - Categorize books
  - Search books by title, author, or ISBN

- Member Management
  - Manage student and faculty members
  - Track member borrowing history
  - Active/Inactive status management

- Borrowing Management
  - Issue books to members
  - Track due dates
  - Handle returns
  - Automatic fine calculation for overdue books

- Reports & Statistics
  - Overview dashboard
  - Popular books report
  - Active members report
  - Overdue books report
  - Fine collection report

## System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)

## Installation

1. Create a MySQL database named `sdckl_library`

2. Import the database schema:
```bash
mysql -u your_username -p sdckl_library < database/schema.sql
```

3. Import sample data (optional):
```bash
mysql -u your_username -p sdckl_library < database/sample_data.sql
```

4. Update database configuration in `database/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'sdckl_library');
```

5. Place the files in your web server directory

## Default Login

After importing sample data, you can login with:
- Username: admin
- Password: password

## Directory Structure

```
library_management/
├── database/
│   ├── config.php
│   ├── schema.sql
│   └── sample_data.sql
├── index.php
├── dashboard.php
├── books.php
├── members.php
├── borrowings.php
├── categories.php
├── reports.php
└── logout.php
```

## Usage

1. **Books Management**
   - Add new books with details like title, author, ISBN
   - Specify number of copies
   - Assign categories
   - Track availability

2. **Members Management**
   - Register new members (students/faculty)
   - View borrowing history
   - Manage member status

3. **Borrowing Process**
   - Select member and book
   - System automatically:
     - Checks book availability
     - Sets due date (14 days from borrowing)
     - Updates book status
     - Tracks overdue books
     - Calculates fines

4. **Reports**
   - View overall statistics
   - Track popular books
   - Monitor overdue books
   - Generate fine reports

## Security Features

- Password hashing
- Session management
- Input sanitization
- SQL injection prevention
- XSS protection

## Maintenance

Regular maintenance tasks:
1. Backup database regularly
2. Update member status as needed
3. Review and clear old records
4. Monitor system logs

## Support

For technical support or questions, please contact:
- Email: admin@sdckl.edu
- Phone: (123) 456-7890

## License

This software is proprietary and confidential. Unauthorized copying or distribution is prohibited.

© 2024 SDCKL Library. All rights reserved.
