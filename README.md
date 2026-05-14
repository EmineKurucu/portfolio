# Project Report — Full-Stack Web Portfolio

**Student:** Emine Kurucu  
**Course:** Web Technologies  
**University:** Halic University — Software Engineering  
**Live Demo:** https://emine-kurucu.infinityfreeapp.com  
**Repository:** https://github.com/EmineKurucu/portfolio

---

## Project Overview

This project is a personal portfolio website developed as the final assignment for the Web Technologies course. The goal was to design and build a full-stack dynamic web application that integrates all the technologies covered throughout the semester: HTML5, CSS3, JavaScript, PHP, and MySQL. Beyond being a course submission, the portfolio serves as a professional tool to present my skills, experience, and projects to potential employers.

---

## Technologies Used

- **HTML5** — Semantic markup with proper use of structural tags, tables, and forms.
- **CSS3** — External stylesheet with CSS custom properties for consistent branding, Flexbox and Grid for layout, and media queries for responsive design.
- **JavaScript (ES6+)** — Vanilla JS for all client-side interactivity and DOM manipulation, using the Fetch API for asynchronous communication and EmailJS for email delivery.
- **PHP 8** — Server-side logic handling form submissions, database queries, session and cookie management, and secure admin authentication.
- **MySQL** — Relational database with two tables (`projects`, `contacts`) storing dynamic content and contact messages.

---

## How It Was Built

The project is structured as a single-page frontend (`index.html`) backed by a PHP/MySQL server layer.

**Frontend:** The page is divided into sections — Hero, About, Experience, Projects, Skills, Contact, and Footer. The layout is fully responsive, adapting to mobile screens through a hamburger menu and fluid grid columns. A dark/light mode toggle switches the color theme and saves the user's preference to `localStorage` so it persists across visits. As the user scrolls, an `IntersectionObserver` highlights the active navigation link and triggers fade-in animations on cards and timeline items.

**Contact Form:** The contact form performs JavaScript validation on every field before submission — checking for empty inputs, a valid email format, and a minimum message length. If all fields pass, the data is sent asynchronously via the Fetch API to `php/contact.php`. The PHP script re-validates the input on the server side and persists the message to the `contacts` MySQL table using a prepared statement to prevent SQL injection. Email notification is delivered to my inbox via **EmailJS** (a client-side email SDK integrated via CDN). This approach was chosen because InfinityFree's free hosting environment blocks all outbound SMTP connections on ports 465 and 587, which prevented PHPMailer from reaching Gmail's SMTP server. By moving email delivery to the browser layer through EmailJS, the application remains fully functional on the free tier without requiring any server-side mail configuration. The user sees a success or error message without any page reload.

**Dynamic Projects:** Projects are stored in the `projects` MySQL table. On page load, `main.js` sends a Fetch request to `php/projects.php`, which queries the database and returns the data as JSON. The JavaScript then builds and injects the project cards into the DOM dynamically. If the server is unreachable, the static fallback cards remain visible.

**Admin Panel:** A password-protected admin dashboard is accessible at `/php/admin/login.php`. The admin password is stored as a bcrypt hash and verified with PHP's `password_verify()`. On successful login, a session is created with `session_regenerate_id()` to prevent session fixation, and a cookie records the last login timestamp. Sessions expire automatically after two hours. From the dashboard, the admin can add new projects, edit existing ones, delete projects, and view or delete incoming contact messages — all backed by prepared SQL statements to prevent injection attacks.

**Security:** Database credentials are stored in a `config.php` file that is excluded from the repository via `.gitignore`. All dynamic output in PHP is escaped with `htmlspecialchars()` and all JavaScript-rendered content goes through a custom `escapeHtml()` helper to prevent XSS.

---

## Database

The database contains two tables. The `projects` table holds the title, description, technology tags, GitHub URL, demo URL, and creation date of each project. The `contacts` table stores all messages submitted through the contact form, including name, email, subject, message body, and timestamp. The SQL export file is included in the repository at `db/portfolio.sql`.

---

## Deployment

The project is hosted on InfinityFree, a free web hosting service that supports PHP and MySQL. Files were uploaded via FTP using FileZilla, the database was imported through phpMyAdmin, and server-specific credentials were configured in `config.php` directly on the server.
