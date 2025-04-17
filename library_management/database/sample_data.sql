-- Insert admin user
INSERT INTO admin (username, password, full_name, email) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin@sdckl.edu');

-- Insert categories
INSERT INTO categories (name, description) VALUES
('Computer Science', 'Books related to programming, algorithms, and computer systems'),
('Engineering', 'Books covering various engineering disciplines'),
('Mathematics', 'Books on pure and applied mathematics'),
('Physics', 'Books on theoretical and applied physics'),
('Literature', 'Fiction and non-fiction literary works'),
('History', 'Books about world history and historical events');

-- Insert books
INSERT INTO books (title, author, isbn, category_id, publisher, publication_year, copies, available_copies, shelf_location, description) VALUES
('Introduction to Algorithms', 'Thomas H. Cormen', '9780262033848', 1, 'MIT Press', 2009, 3, 3, 'CS-101', 'Comprehensive introduction to algorithms'),
('Clean Code', 'Robert C. Martin', '9780132350884', 1, 'Prentice Hall', 2008, 2, 2, 'CS-102', 'Guide to writing clean and maintainable code'),
('Engineering Mathematics', 'K.A. Stroud', '9781137031204', 3, 'Palgrave', 2013, 4, 4, 'MT-201', 'Essential mathematics for engineering students'),
('Fundamentals of Physics', 'Halliday & Resnick', '9781118230718', 4, 'Wiley', 2013, 3, 3, 'PH-101', 'Comprehensive introduction to physics'),
('To Kill a Mockingbird', 'Harper Lee', '9780446310789', 5, 'Grand Central', 1988, 5, 5, 'LT-301', 'Classic American literature'),
('A Brief History of Time', 'Stephen Hawking', '9780553380163', 4, 'Bantam', 1998, 2, 2, 'PH-102', 'Popular science book on cosmology');

-- Insert members
INSERT INTO members (member_id, full_name, email, phone, member_type, department, status) VALUES
('STU001', 'John Smith', 'john.smith@student.sdckl.edu', '1234567890', 'student', 'Computer Science', 'active'),
('STU002', 'Mary Johnson', 'mary.johnson@student.sdckl.edu', '2345678901', 'student', 'Engineering', 'active'),
('STU003', 'David Wilson', 'david.wilson@student.sdckl.edu', '3456789012', 'student', 'Physics', 'active'),
('FAC001', 'Dr. Sarah Brown', 'sarah.brown@sdckl.edu', '4567890123', 'faculty', 'Mathematics', 'active'),
('FAC002', 'Prof. James Davis', 'james.davis@sdckl.edu', '5678901234', 'faculty', 'Computer Science', 'active');

-- Insert some borrowing records
INSERT INTO borrowings (book_id, member_id, borrow_date, due_date, status) VALUES
(1, 1, DATE_SUB(CURRENT_DATE, INTERVAL 10 DAY), DATE_ADD(DATE_SUB(CURRENT_DATE, INTERVAL 10 DAY), INTERVAL 14 DAY), 'borrowed'),
(2, 2, DATE_SUB(CURRENT_DATE, INTERVAL 20 DAY), DATE_SUB(CURRENT_DATE, INTERVAL 6 DAY), 'overdue'),
(3, 4, DATE_SUB(CURRENT_DATE, INTERVAL 5 DAY), DATE_ADD(DATE_SUB(CURRENT_DATE, INTERVAL 5 DAY), INTERVAL 14 DAY), 'borrowed');

-- Update available copies for borrowed books
UPDATE books SET available_copies = available_copies - 1 WHERE id IN (1, 2, 3);

-- Insert fine settings
INSERT INTO fine_settings (fine_per_day, grace_period_days) VALUES (1.00, 0);
