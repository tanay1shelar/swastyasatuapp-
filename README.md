# Healthcare & Medical Camp Management System (HMCMS) - SwasthyaSetu

SwasthyaSetu (HMCMS) is an enterprise-grade clinical administration and medical camp management web application. Designed for healthcare workers, camp managers, physicians, and administrators, the system coordinates mobile healthcare units, patient intake registrations, biometric ID verifications, queue triage attendance, medical inventory tracking, and XLSX reporting.

---

## 🛠️ Technology Stack

* **Backend Engine**: PHP (PDO Parameterized Data Access Layer)
* **Database**: MySQL (Relational Schema with `swasthyasetu` DDL & DML seeds)
* **Frontend UI**: Bootstrap 5.3, Bootstrap Icons, Vanilla JavaScript (ES6 Modules & Event Driven UI)
* **Export Utilities**: Server-side Excel Writer (`xlsxwriter.class.php`) and Client-Side `SheetJS`
* **Architecture**: Modular Feature Domain Architecture (`modules/`, `authentication/`, `api/`, `includes/`, `config/`, `assets/`)

---

## 📂 Project Architecture & Folder Structure

```
hmcms/
│
├── 🔑 authentication/          # Authentication Views & Session Controllers
│   ├── login.php               # Login Portal
│   ├── logout.php              # Session Termination Action
│   └── auth.php                # Auth Helper Logic
│
├── ⚙️ config/                  # Core Configuration & Environment Bootstrapping
│   ├── constants.php           # Global Application Constants & Role Codes
│   ├── config.php              # Dynamic BASE_URL Detection & Bootstrapper
│   ├── database.php            # PDO Database Connection Factory (Singleton)
│   ├── helpers.php             # Input Sanitization, JSON Helpers & BMI Math
│   ├── queries.php             # Parameterized SQL Statement Dictionary
│   ├── functions.php           # Data Service & CRUD Methods
│   └── xlsxwriter.class.php    # Excel Export Engine
│
├── 🧩 includes/                # Shared View Components & Layout Engines
│   ├── header.php              # Global HTML <head> & CSS Tokens
│   ├── navbar.php              # Sticky Top Bar, Clock & Notifications Dropdown
│   ├── sidebar.php             # Collapsible Navigation Menu & Brand Identity
│   ├── footer.php              # Footer Credits & Core JavaScript Bundle
│   ├── session.php             # Session Manager, RBAC Guards & Cookie Security
│   ├── functions.php           # Shared Layout Render Helpers
│   └── placeholder.php         # Module Placeholder Engine
│
├── 📦 modules/                 # Modular Feature Domains
│   ├── dashboard/              # Main Administrative Dashboard
│   ├── patient-registration/   # Patient Intake & Document Uploads
│   ├── patient-verification/   # Aadhaar & Biometric Verification
│   ├── patient-attendance/     # QR & Token Queue Check-in/out
│   ├── patient-list/           # Patient Directory & XLSX Exporters
│   ├── camp-assistance/        # Vitals Triage & Clinical Logger
│   ├── medical-stock/          # Inventory Catalog & Stock Alerts
│   ├── update-patient/         # Medical Record Editor
│   ├── notifications/          # System Alerts & Audit Logs
│   └── profile/                # Health Worker Profile Settings
│
├── 🔌 api/                     # Backend AJAX API Routing Layer
│   └── index.php               # Action Request Dispatcher
│
├── 🎨 assets/                  # Public Web Assets
│   ├── css/                    # Modular Stylesheets (variables, layouts, components, responsive)
│   ├── js/                     # Modular JavaScript (core utilities & module scripts)
│   ├── images/                 # System Logos, Icons & Graphic Assets
│   └── archive/                # Preserved Legacy Scripts
│
├── 🗄️ database/                 # Relational Database Schema
│   └── schema.sql              # MySQL DDL Schema & Sample Records
│
├── 📤 uploads/                  # Secure Persistent Files Storage
│   ├── documents/              # Encrypted Aadhaar & Identity Documents
│   ├── images/                 # Patient Profile Photos
│   └── profile/                # Health Worker Avatars
│
├── index.php                   # Root Entry Point Router
├── api.php                     # Root API Proxy (Backward Compatibility)
└── README.md                   # System Architecture Documentation
```

---

## ⚡ Running Locally

### Prerequisites
* **XAMPP / WAMP / LAMP** or standalone **PHP 7.4+** & **MySQL 5.7+**.

### Step 1: Database Setup
1. Open XAMPP Control Panel and start **Apache** and **MySQL**.
2. Open **phpMyAdmin** (`http://localhost/phpmyadmin/`).
3. Create a database named `hmcms`.
4. Import `database/schema.sql` into the `hmcms` database.

### Step 2: Project Deployment
1. Move the `hmcms` project directory to your web server root (e.g. `C:\xampp\htdocs\hmcms`).
2. Open your browser and navigate to:
   - **Main System Portal**: [http://localhost/hmcms/](http://localhost/hmcms/)
   - **Login Console**: [http://localhost/hmcms/authentication/login.php](http://localhost/hmcms/authentication/login.php)

---

## 🔐 Demo User Credentials

| Role | Username / ID | Password | Access Rights |
|---|---|---|---|
| **Health Worker** | `EMP-2026-9042` | `Password@123` | Full Camp Operations & Patient Intakes |
| **System Admin** | `admin@hmcms.org` | `Password@123` | System Maintenance & Stock Management |

---

## 🛡️ Security Features
* **SQL Injection Immunity**: 100% PDO prepared statements with bound parameters.
* **XSS Defense**: Strict HTML output escaping with `htmlspecialchars()`.
* **Session Hardening**: HTTP-only cookies, automatic session regeneration, and RBAC authorization guards.
