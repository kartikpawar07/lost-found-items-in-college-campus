# lost-found-items-in-college-campus
Smart Campus Lost and Found Management System is a simple web-based mini project developed using PHP and MySQL. The system helps students report lost and found items within a college campus. Users can register, log in, add lost or found item details, search for items, and manage their own records through a user-friendly dashboard.
# Smart Campus Lost and Found Management System

## Overview

The Smart Campus Lost and Found Management System is a web-based application developed to help students and staff manage lost and found items within a college campus. The system provides a centralized platform where users can report lost items, report found items, search for items, and track item records efficiently.

This project is designed as a beginner-friendly mini project using Core PHP and MySQL without any advanced frameworks.

---

## Features

### User Authentication

* User Registration
* User Login
* Logout Functionality
* Session Management
* Secure Password Hashing

### Lost Item Management

* Add Lost Item
* Edit Lost Item
* Delete Lost Item
* View Personal Lost Items

### Found Item Management

* Add Found Item
* Edit Found Item
* Delete Found Item
* View Personal Found Items

### Search Functionality

* Search Items by Name
* Search by Category
* Search by Location

### Dashboard

* Welcome User
* Display Total Lost Items
* Display Total Found Items
* Quick Navigation Options

### Admin Panel

* Admin Login
* Manage Users
* View Lost Items
* View Found Items
* Manage Claims
* Delete Invalid Records

### Claim Management

* Submit Claim Requests
* View Claim Status
* Admin Approval/Rejection

---

## Technology Stack

### Frontend

* HTML5
* CSS3
* JavaScript
* Bootstrap 5

### Backend

* PHP (Core PHP)

### Database

* MySQL

### Server

* XAMPP

---

## Project Structure

lost-found-system/

├── index.php

├── login.php

├── register.php

├── logout.php

├── dashboard.php

├── search.php

├── config/

│ └── database.php

├── admin/

├── lost_items/

├── found_items/

├── claims/

├── uploads/

├── assets/

│ ├── css/

│ ├── js/

│ └── images/

└── database/

└── lost_found.sql

---

## Database Tables

### users

* id
* name
* email
* phone
* department
* password
* created_at

### admin

* id
* username
* password

### lost_items

* id
* user_id
* item_name
* category
* description
* lost_date
* location
* image
* status

### found_items

* id
* user_id
* item_name
* category
* description
* found_date
* location
* image
* status

### claims

* id
* user_id
* found_item_id
* claim_reason
* additional_info
* status
* created_at

---

## Installation Guide

### Step 1

Install XAMPP on your computer.

### Step 2

Start:

* Apache Server
* MySQL Server

### Step 3

Copy the project folder into:

C:\xampp\htdocs\

### Step 4

Open phpMyAdmin and create a database:

lost_found_db

### Step 5

Import:

database/lost_found.sql

### Step 6

Configure database connection in:

config/database.php

### Step 7

Open browser and run:

http://localhost/lost-found-system

---

## Security Features

* Password Hashing
* Session Authentication
* Input Validation
* SQL Injection Prevention using Prepared Statements
* File Upload Validation

---

## Future Enhancements

* Email Notifications
* QR Code Verification
* Mobile Responsive Improvements
* Advanced Search Filters
* Item Image Matching

---

## Learning Outcomes

* PHP CRUD Operations
* User Authentication
* Session Handling
* Database Connectivity
* File Upload Handling
* Bootstrap UI Design
* MySQL Database Management

---

## Author

Developed as a Mini Project for Diploma/Engineering Students using PHP and MySQL.

## License

This project is developed for educational purposes only.
