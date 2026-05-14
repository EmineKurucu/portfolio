# Emine Kurucu — Full-Stack Web Portfolio

A dynamic, full-stack personal portfolio built with HTML5, CSS3, JavaScript, PHP, and MySQL as a final project for the Web Technologies course at Halic University.

---

## Live Demo

> _Link will be added after deployment._

## Project Structure

```
portfolio/
├── index.html              # Main portfolio page
├── css/
│   └── style.css           # External stylesheet
├── js/
│   └── main.js             # Client-side JavaScript
├── php/
│   ├── db.php              # Database connection helper
│   ├── config.php          # Credentials (gitignored)
│   ├── config.example.php  # Credential template
│   ├── contact.php         # Contact form API endpoint
│   ├── projects.php        # Projects API endpoint
│   └── admin/
│       ├── login.php       # Admin login (session management)
│       └── dashboard.php   # Admin dashboard (CRUD)
├── assets/
│   └── favicon.jpg
└── db/
    └── portfolio.sql       # Database export
```

---

## Tech Stack

| Layer      | Technology                        |
|------------|-----------------------------------|
| Markup     | HTML5                             |
| Styling    | CSS3 (Flexbox, Grid, Variables)   |
| Scripting  | Vanilla JavaScript (ES6+)         |
| Backend    | PHP 8+                            |
| Database   | MySQL (mysqli, prepared statements)|
| Fonts      | Google Fonts — Syne, Space Mono   |

---

## Requirements Coverage

### 1. Semantic HTML & Advanced CSS

| Requirement | Implementation |
|---|---|
| HTML5 semantic tags | `<nav>`, `<section>`, `<footer>`, `<header>` used throughout `index.html` |
| Tables | `<table class="info-table">` in the About section displays personal info |
| Forms | Contact form in `index.html` with labels, inputs, textarea |
| Responsive design (Flexbox / Grid) | CSS Grid for About, Projects, Skills layouts; Flexbox for Navbar, Hero, Contact; media queries at 900px, 700px, 480px in `style.css` |
| External stylesheet | `css/style.css` — linked in `<head>`, never inline |
| Consistent branding | CSS custom properties (`--accent`, `--bg`, `--font-display`, etc.) define a unified color palette and typography across all pages |

---

### 2. Client-Side Interactivity (JavaScript / DOM)

| Requirement | Implementation | Location |
|---|---|---|
| Dynamic UI element | **Dark / Light mode toggle** — switches CSS classes, persists preference via `localStorage` | `main.js` lines 12–37 |
| Interactive menu | **Hamburger menu** for mobile — toggles `.open` class on click, closes on link select | `main.js` lines 39–50 |
| Active nav highlight | `IntersectionObserver` updates `.active` class on nav links as sections scroll into view | `main.js` lines 52–66 |
| Scroll reveal animation | Elements fade in with `translateY` transition when they enter the viewport | `main.js` lines 220–239 |
| JS Form Validation | All four contact fields validated **before** fetch is called — empty check, email regex, min-length rules; live feedback on `blur` and `input` events | `main.js` lines 76–170 |
| DOM manipulation | Project cards are created with `document.createElement` and populated from the API response | `main.js` lines 175–211 |

---

### 3. Server-Side Logic & Database (PHP / MySQL)

| Requirement | Implementation | Location |
|---|---|---|
| Contact Management | `contact.php` receives JSON via POST, validates server-side, inserts into `contacts` table with a prepared statement, and sends an HTML email notification | `php/contact.php` |
| Dynamic Content | `projects.php` queries the `projects` table and returns JSON; `main.js` renders the cards without a page refresh | `php/projects.php`, `main.js:180` |
| AJAX Integration | Both the contact form submission and the projects fetch use the **Fetch API** (`async/await`) — no page reload on either action | `main.js:145`, `main.js:180` |
| SQL Injection Prevention | All queries use `mysqli` **prepared statements** with `bind_param` | `contact.php`, `projects.php`, `dashboard.php` |
| XSS Prevention | `escapeHtml()` helper in JS; `htmlspecialchars()` in all PHP output | `main.js:214`, every PHP echo |

---

### 4. State Management & Persistence

| Requirement | Implementation | Location |
|---|---|---|
| Sessions | `session_start()`, `$_SESSION['admin_logged_in']`, session timeout after 2 hours, `session_regenerate_id(true)` on login to prevent session fixation | `php/admin/login.php`, `dashboard.php` |
| Cookies | `setcookie('last_login', ...)` written on successful login; displayed as "Last login: …" on the login page | `php/admin/login.php:36` |
| Admin Dashboard — Add | Form POSTs to `dashboard.php` with `action=add_project`; inserts new row into `projects` table | `dashboard.php` |
| Admin Dashboard — Edit | `?edit=ID` loads the project into a pre-filled form; POSTs with `action=edit_project`; runs an `UPDATE` query | `dashboard.php` |
| Admin Dashboard — Delete | Inline form with `action=delete_project` / `action=delete_message`; runs a `DELETE` query | `dashboard.php` |
| Secure login | Password stored as **bcrypt hash** (`password_hash` / `password_verify`); credentials stored in gitignored `config.php` | `config.php`, `login.php` |

---

## Database Setup

```sql
-- Import with:
mysql -u root -p < db/portfolio.sql
```

The export file (`db/portfolio.sql`) creates the `portfolio_db` database, the `projects` and `contacts` tables, and inserts sample project data.

---

## Local Installation

```bash
# 1. Clone the repo
git clone https://github.com/EmineKurucu/portfolio.git

# 2. Place the folder inside htdocs (XAMPP) or www (MAMP)

# 3. Import the database
mysql -u root -p < db/portfolio.sql

# 4. Create your config file
cp php/config.example.php php/config.php
# Edit php/config.php with your DB credentials and a new bcrypt password hash

# 5. Generate a bcrypt password hash (optional)
php -r "echo password_hash('your_password', PASSWORD_BCRYPT);"

# 6. Open in browser
# http://localhost/portfolio/
```

---

## Admin Panel

```
URL:      http://localhost/portfolio/php/admin/login.php
User:     emine
Password: (set in config.php)
```

From the dashboard you can **add**, **edit**, and **delete** projects, and view or delete incoming contact messages.

---

## Features at a Glance

- Animated hero section with a rotating neural orb
- Timeline-based experience section
- Projects loaded dynamically from MySQL via AJAX
- Contact form with JS validation + server-side validation + email notification
- Dark / Light mode toggle with `localStorage` persistence
- Fully responsive layout (mobile hamburger menu)
- Secure admin panel (bcrypt, sessions, cookies, session timeout)

---

## Author

**Emine Kurucu**  
Halic University — Software Engineering, 3rd Year  
[github.com/EmineKurucu](https://github.com/EmineKurucu) · [linkedin.com/in/emine-kurucu-153422367](https://www.linkedin.com/in/emine-kurucu-153422367/)
