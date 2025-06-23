# Campus Navigator

A web application to help students, faculty, and visitors find and share spots in and around campus. Built as a mini project for BCA using PHP, MySQL, HTML, and Tailwind CSS.

## Features
- User, Faculty, and Admin roles
- Register, login, and profile management
- Dashboard with live time, weather, featured spots, and categories
- Add, edit, view, and delete campus spots
- Spot details with comments, ratings, favorites, report, and suggest edit
- Manage your posts and favorites
- Faculty/Admin: Approve/reject/delete posts
- Admin: Manage users and faculties (promote/demote/delete)
- Responsive UI with dark mode

## Tech Stack
- PHP (backend)
- MySQL (database)
- HTML, Tailwind CSS (frontend)
- JavaScript (live time, dark mode)

## Setup Instructions
1. **Clone or Download the Project**
2. **Database Setup**
   - Import `sql/campus_navigator.sql` into your MySQL server (use phpMyAdmin or CLI).
   - Add some categories manually to the `categories` table (see below).
3. **Configure Database Connection**
   - Edit `includes/db.php` if your MySQL credentials are different (default: root, no password).
4. **Run the Project**
   - Place the project folder in your web server directory (e.g., `htdocs` for XAMPP).
   - Access `index.php` via your browser (e.g., `http://localhost/campusNavigatorCursor/index.php`).
5. **Add Categories**
   - Use phpMyAdmin or MySQL CLI:
     ```sql
     INSERT INTO categories (name, icon) VALUES
       ('Cafeteria', '🍽️'),
       ('Library', '📚'),
       ('ATM', '🏧'),
       ('Shop', '🛒'),
       ('Hangout', '🎉');
     ```
6. **Create Users**
   - Register as user, faculty, or admin (set role in database for admin).

## Usage
- **Users:** Add/view spots, comment, rate, favorite, manage own posts.
- **Faculty:** Approve/reject/delete posts, manage own posts.
- **Admin:** All authorities, manage users/faculties, approve/reject/delete posts.

## Weather API
- Uses [OpenWeatherMap](https://openweathermap.org/) for live weather in Kochi.
- API key is included in `includes/functions.php`.

## Credits
- Developed by [Your Name] for BCA Mini Project
- Tailwind CSS, OpenWeatherMap API

---
Feel free to customize and extend this project for your campus needs! 