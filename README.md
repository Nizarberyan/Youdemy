# YouDemy Learning Platform

A comprehensive online learning platform where teachers can create and publish courses, and students can enroll and learn.

## Features

### User Management

- Multi-role user system (Admin, Teacher, Student)
- Role-based access control
- Profile management with profile pictures
- Teacher approval system

### Course Management

- Course creation and management
- Support for both video and text-based lessons
- Course sections and lessons organization
- Course thumbnail uploads
- Course status (draft, published, archived)

### Learning Features

- Course enrollment system
- Progress tracking
- Course reviews and ratings
- Course categories

### Admin Features

- User management
- Course approval system
- Category management
- Analytics dashboard
- Teacher approval system

## Tech Stack

- PHP 8.2
- MySQL/MariaDB
- HTML5
- Tailwind CSS
- JavaScript
- Font Awesome

## Installation

1. Clone the repository:

```bash
git clone https://github.com/Nizarberyan/youdemy.git
```

2. Set up your web server (Apache/Nginx) to point to the project directory

3. Create a MySQL database and import the schema:

```bash
mysql -u your_username -p your_database_name < database/schema.sql
```

4. Configure your database connection in `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'youdemy');
```

5. Make sure the following directories are writable:

```
assets/images/uploads/courses/
assets/images/uploads/profiles/
```

## Directory Structure

```
├── admin/           # Admin dashboard and management
├── assets/          # Static assets (CSS, JS, images)
├── auth/            # Authentication related files
├── classes/         # PHP classes
├── config/          # Configuration files
├── courses/         # Course-related pages
├── database/        # Database schema and migrations
├── includes/        # Shared components (header, footer)
├── student/         # Student dashboard and features
└── teacher/         # Teacher dashboard and course management
```

## Usage

### For Teachers

1. Register as a teacher
2. Wait for admin approval
3. Create and manage courses
4. Upload lessons (video/text)
5. Track student progress

### For Students

1. Register as a student
2. Browse available courses
3. Enroll in courses
4. Track learning progress
5. Leave reviews and ratings

### For Admins

1. Manage users and roles
2. Approve/reject teachers
3. Manage course categories
4. View platform statistics

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details

## Contact

Your Name - nizarberiane@proton.me
Project Link: https://github.com/Nizarberyan/youdemy
