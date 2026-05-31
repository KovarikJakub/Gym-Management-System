
# ltw-project-ltw02g04

## Features

**All users:**
- [x] Register a new account.
- [x] Log in and out.
- [x] Edit their profile, including name, username, password, and profile photo.

**Members:**
- [x] Browse the schedule of available fitness classes, filtering by type, trainer, day, or time.
- [x] Enroll in and cancel enrollment from upcoming classes, subject to capacity limits.
- [x] View trainer profiles, including their specializations and the classes they teach.
- [x] Check the current availability of equipment in the main training area.
- [x] Leave ratings and reviews for classes they have attended.

**Trainers:**
- [x] Manage their public profile, including bio, specializations, and certifications.
- [x] View the roster of members enrolled in their classes.
- [x] Track and manage their assigned class schedule.

**Admins:**
- [x] Manage members and trainers (create, update, and deactivate accounts).
- [x] Manage the class catalog (create, edit, and remove classes).
- [x] Manage equipment in the main training area (add, update availability status, and remove items).
- [X] Elevate a user to admin status.
- [x] Oversee and ensure the smooth operation of the entire system (via Analytics Panel).

**Extra:**
- [x] Dynamic Home Page with live database information.
- [x] Admin Analytics Dashboard (Session statistics, User distribution, etc.).
- [x] CSRF Protection for all sensitive actions.
- [x] Responsive layout for mobile and desktop.
- [x] Admins can view gym-wide metrics such as most
      popular classes, equipment usage, and trainer performance.
- [x] Admins can highlight specific classes or trainers
      on the homepage through featured promotions (Implemented via dynamic live-data
      highlighting).
- [x] Implementation of CSRF protection for all form actions
      and a fully responsive mobile-first design.

## Running

Initializing the database:
```bash
cd database
sqlite3 gym.db < gym.sql
```

Starting the server:
```bash
cd ..
php -S localhost:9000 -t public
```

## Credentials

All accounts use the password: **1234**

- **Admin:** `admin`
- **Trainers:** `trainer1`, `trainer2`, `trainer3`
- **Members:** `member1`, `member2`, `member3`, `member4`, `member5`

## File Structure

```
├── database/
│   ├── gym.db                   # SQLite database
│   └── gym.sql                  # Database schema and seed data
├── public/
│   ├── index.php                # Home page
│   ├── sessions.php             # Class schedule
│   ├── trainers.php             # Trainers list
│   ├── equipment.php            # Equipment inventory
│   ├── dashboard.php            # User dashboard
│   ├── edit_profile.php         # Profile management
│   ├── login.php / register.php # Authentication
│   ├── admin_*.php              # Admin management pages
│   ├── css/                     # Stylesheets
│   ├── js/                      # JavaScript logic
│   └── uploads/                 # User-uploaded content
├── src/
│   ├── auth.php                 # Authentication logic
│   ├── database.php             # Database connection singleton
│   ├── models/                  # Database interaction models
│   ├── templates/               # Reusable UI components
│   └── Utils/                   # Helper functions
└── tests/                       # Unit tests
```
