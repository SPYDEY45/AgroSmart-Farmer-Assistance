# AgroSmart – Smart Agriculture Assistant & Farmer Market Portal
**BCA Final Year Field Project**

---

## 1. Project Overview

**AgroSmart** is an integrated agricultural intelligence and direct-to-buyer market disintermediation platform. It bridges the gap between rural cultivators, wholesale agricultural produce buyers, certified agronomy scientists, and state agricultural administrations.

Key Project Objectives:
* Disintermediate produce trading to eliminate exploitative middlemen commissions.
* Provide scientific crop selection advisories based on soil, season, and water availability.
* Deliver transparent daily APMC mandi wholesale price benchmarks.
* Provide an agronomy disease doctor guide with organic and chemical controls.
* Streamline grievance redressal for local farming issues.
* Bridge government welfare schemes with direct digital links.

---

## 2. Technology Stack

* **Frontend:** HTML5, CSS3, JavaScript (ES6+), Bootstrap 5.3, Chart.js, Bootstrap Icons
* **Backend:** PHP 8+ (Modular MVC architecture, PDO Prepared Statements, CSRF tokens, Bcrypt password hashing)
* **Database:** MySQL 8+ (Normalized 13-table relational schema with foreign key constraints)
* **Mobile Companion:** Kotlin & Jetpack Compose (Material 3)

---

## 3. Database Architecture (13 Tables)

1. `users`: Authentication credentials, roles (`farmer`, `buyer`, `expert`, `admin`), active/blocked status.
2. `farmers`: Cultivator profiles, land area in acres, soil type, irrigation source, village, district.
3. `buyers`: Commercial firms, licenses, contact representative, trading address.
4. `experts`: Agricultural scientists, degrees, specializations, years of experience.
5. `crops`: Comprehensive agronomic catalog with sowing calendars, water requirements, and cultivation guidance.
6. `farmer_crops`: Active seasonal cultivation tracking with sowing date and land acreage.
7. `crop_diseases`: Plant pathology database with symptoms, pathogens, chemical & organic solutions, and cultural precautions.
8. `products`: Direct farm produce lots listed by farmers with expected prices, quantities, and verification status (`pending`, `approved`, `rejected`, `sold`).
9. `enquiries`: B2B procurement enquiries from buyers to farmers with negotiated quantities, messages, and contact unlocking upon acceptance.
10. `market_prices`: Daily APMC wholesale auction rates (Min, Max, Modal prices) across market yards.
11. `complaints`: Farmer grievance redressal tickets with category tracking and official admin responses.
12. `schemes`: Government welfare schemes (PM-KISAN, MahaDBT solar pumps, etc.) with benefits and application guides.
13. `expert_questions`: Farmer-to-scientist consultation advisory pipeline with status (`Pending`, `Answered`).

---

## 4. Default Demo Accounts

All accounts use the default password: **`password123`**

| Role | Email | Password | Key Capabilities |
|---|---|---|---|
| **Admin** | `admin@agrosmart.com` | `password123` | System oversight, user moderation, product approval, APMC prices CRUD, complaint resolution, Chart.js analytics |
| **Farmer** | `farmer@agrosmart.com` | `password123` | Cultivation logs, smart crop advisor, listing produce, handling buyer enquiries, asking agronomy experts, grievance ticketing |
| **Buyer** | `buyer@agrosmart.com` | `password123` | Browsing verified farm produce lots, dispatching purchase enquiries, unlocking farmer contact details |
| **Expert** | `expert@agrosmart.com` | `password123` | Answering farmer agronomy queries, prescribing dosages, providing disease advisories |

---

## 5. Setup & Installation Guide

1. **Local Server Setup:**
   * Copy the `/agrosmart` directory into your web server document root (e.g. `htdocs/` in XAMPP/WAMP or `/var/www/html/`).
   * Or run PHP's built-in server from the project root:
     ```bash
     php -S localhost:8000 -t agrosmart
     ```

2. **Database Import:**
   * Open **phpMyAdmin** or MySQL CLI.
   * Create database `agrosmart`:
     ```sql
     CREATE DATABASE agrosmart CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
     ```
   * Import `/agrosmart/database.sql`.

3. **Configuration:**
   * Review `/agrosmart/config/database.php` if your MySQL username or password differ from default (`root` / empty).

4. **Run Application:**
   * Open `http://localhost:8000/` or `http://localhost/agrosmart/` in any modern web browser.

---

## 6. Viva Talking Points for BCA Examiners

1. **Why PDO instead of mysqli?**
   PDO offers database abstraction, named prepared statements, and consistent error handling modes (`PDO::ERRMODE_EXCEPTION`) that prevent SQL injection.
2. **How is CSRF prevented?**
   Every mutation form generates a cryptographic CSRF token via `bin2hex(random_bytes(32))` stored in `$_SESSION['csrf_token']` and validated using `hash_equals()`.
3. **How does the Crop Recommendation Algorithm work?**
   It uses weighted heuristic rule scoring evaluating season compatibility (40 pts), soil type absorption (35 pts), and irrigation feasibility (25 pts) to compute a suitability percentage.
4. **How are passwords stored?**
   Bcrypt hashing via PHP's native `password_hash($password, PASSWORD_BCRYPT)` with cost factor 10, verified via constant-time `password_verify()`.
