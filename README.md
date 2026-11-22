# RentMate – Personal Rent & Expense Tracker

A modern, robust web application built with **PHP 8.2+**, **Laravel 12**, **MySQL / PostgreSQL**, **Tailwind CSS**, and **Blade** designed for tenants to maintain accurate records of monthly rent, electricity bills, water bills, multi-installment payments, outstanding balances, Bikram Sambat (BS) Nepali calendar tracking, and historical rental archives.

- **Live Application**: [https://rentmate-web-production-f9977f93.up.railway.app](https://rentmate-web-production-f9977f93.up.railway.app)
- **Repository**: [https://github.com/LipiAdhikari/RentMate](https://github.com/LipiAdhikari/RentMate)

---

## 🌟 Key Features

1. **Dynamic SaaS Dashboard**:
   - Real-time calculations of current month expenses, house rent, electricity bill, water share, total paid, and outstanding balance.
   - All-time cumulative expense metrics since move-in date.
   - Interactive Chart.js charts: Monthly Expense Breakdown (Stacked Bar) & Electricity Unit Consumption (kWh).
   - Instant alerts for pending / partially paid months with one-click payment logging.
   - Recent payment transaction stream.

2. **Bikram Sambat (BS) Nepali & Gregorian (AD) Calendar**:
   - Seamless calendar integration with Bikram Sambat (BS) date support (Baisakh to Chaitra, Gate 01–32).
   - Clean, intuitive date selectors for payment logging and rental ledger records without cluttered jargon.
   - Server-side conversion engine mapping AD dates to BS years/months/days accurately.

3. **Monthly Rental Records (CRUD)**:
   - Complete record creation, viewing, editing, and deletion.
   - Dynamic calculations with instant live browser previews.
   - Unique constraints per rental month/year per user to prevent duplicate records.
   - Detailed statement views with printable receipts.
   - Strict multi-tenant data isolation.

4. **Flexible Electricity Billing System**:
   - Multi-month flexible billing periods (e.g. 1, 2, 3, 4, 5+ months like September–December or January–May).
   - Units Consumed = Current Reading - Previous Reading.
   - Electricity Bill = Units × Electricity Rate.
   - Form validation prevents current reading from being lower than previous reading.
   - Independent electricity payment tracking (supports partial payments, multiple installments, and methods).
   - Optional monthly expense allocation linking multi-month bills to individual rental months without inflating or duplicating costs.
   - Standalone electricity payments remain completely separate from monthly house rent settlements.
   - Quick-update reading endpoints and historical consumption trend analytics.

5. **Water Bill Sharing (Configurable Split)**:
   - Configurable tenant/owner split percentage (Default: 50% tenant, 50% landlord).
   - Tenant Share = Total Water Bill × (Tenant % / 100).
   - Owner share automatically computed and tracked.

6. **Payment Management & Installments**:
   - Supports multiple partial installments against any single month.
   - Methods supported: **eSewa**, **Khalti**, **Bank Transfer**, **Cash**, and **Other**.
   - Automatic status progression: `Unpaid` → `Partially Paid` → `Fully Paid` → `Overpaid`.
   - Never overwrites previous transactions; preserves complete audit trails.
   - Auto-allocation to oldest unpaid month when recording general payments.

7. **Historical Records & Archives**:
   - Dedicated section to log older months from previous years of tenancy.
   - Distinguishes **Verified** records (backed by receipts/statements) from **Estimated / Incomplete** records.
   - Adheres to the strict financial rule: *never invent or guess missing payment amounts*.

8. **Reports & Exports**:
   - Filter by year, date range, and payment status.
   - Export structured data to **CSV / Excel** with UTF-8 BOM.
   - Generate and download printable **PDF Statements** via DomPDF.

9. **Configurable Rental Settings**:
   - Configurable monthly rent (Default: NPR 16,000.00).
   - Configurable electricity rate (Default: NPR 10.00/unit).
   - Configurable water sharing percentage (Default: 50.00%).
   - Currency configuration (NPR).
   - Landlord & property details with payment reminder day preference.
   - **Rate Preservation Guarantee**: Changing future settings never alters rates on existing historical records.

10. **Authentication & Multi-Tenant Security**:
    - User registration, login, and secure logout.
    - Rate limiting on authentication endpoints (5 requests/minute).
    - Strict per-user data isolation—each user accesses only their own records.
    - Password hashing via Bcrypt with minimum round constraints.

---

## 📐 Financial Calculation & Integrity Engine

All financial math is evaluated server-side within `MonthlyRecord::recalculate(bool $save)` with strict precision:

1. **Electricity Units**:
   $$\text{Units Consumed} = \max(0, \text{curr\_reading} - \text{prev\_reading})$$
   *Enforced with validation requiring $\text{curr\_reading} \ge \text{prev\_reading}$.*

2. **Electricity Cost**:
   $$\text{Electricity Cost} = \text{Units Consumed} \times \text{Electricity Rate}$$

3. **Water Utility Split**:
   $$\text{Tenant Water Share} = \text{Total Water Bill} \times \left(\frac{\text{Tenant Percentage}}{100}\right)$$
   $$\text{Owner Water Share} = \text{Total Water Bill} - \text{Tenant Water Share}$$

4. **Additional Charges**:
   $$\text{Additional Expenses} = \sum(\text{additional\_expenses.amount})$$

5. **Total Monthly Expenses**:
   $$\text{Total Expenses} = \text{Rent} + \text{Electricity Cost} + \text{Tenant Water Share} + \text{Additional Expenses}$$

6. **Total Paid & Outstanding Balance**:
   $$\text{Total Paid} = \sum(\text{payments.amount})$$
   $$\text{Outstanding Balance} = \text{Total Expenses} - \text{Total Paid}$$

7. **Automatic Status Lifecycle**:
   - $\text{Total Paid} \le 0 \implies \mathbf{Unpaid}$
   - $0 < \text{Total Paid} < \text{Total Expenses} \implies \mathbf{Partially\ Paid}$
   - $\text{Total Paid} = \text{Total Expenses} \implies \mathbf{Paid}$
   - $\text{Total Paid} > \text{Total Expenses} \implies \mathbf{Overpaid}$

> **Data Integrity**: Whenever a `Payment` or `AdditionalExpense` is saved, updated, or deleted, Eloquent lifecycle hooks automatically invoke `recalculate(true)` on the parent `MonthlyRecord`, guaranteeing ledger consistency.

---

## 🗄️ Database Architecture

RentMate uses normalized tables with multi-tenant scoping and `DECIMAL(10,2)` fields for currency:

- **`users`**: Tenant accounts, encrypted credentials, remember tokens.
- **`rental_settings`**: Per-user configurations (`rent_amount`, `electricity_rate`, `water_share_percentage`, `currency`, `landlord details`).
- **`monthly_records`**: Core ledger containing monthly rent, meter readings, water bills, computed totals, balance, and payment status.
  - Unique composite index: `['user_id', 'year', 'month']` prevents duplicate records per tenancy.
- **`payments`**: Individual transactions tied to monthly records (`payment_date`, `bs_payment_date`, `amount`, `payment_method`, `transaction_reference`).
- **`additional_expenses`**: Itemized miscellaneous expenses (waste management, repairs, internet).
- **`electricity_bills`**: Flexible billing periods covering 1 to 5+ months (`billing_period_start`, `billing_period_end`, readings, units, rate, total, paid, balance, status).
- **`electricity_bill_allocations`**: Optional link allocating a portion of a multi-month electricity bill to covered monthly records without duplicating original amounts.
- **`electricity_payments`**: Standalone payments specifically against electricity bills, decoupled from monthly house rent settlements.

---

## 🛠️ Technology Stack

- **Backend**: Laravel 12 (PHP 8.2+)
- **Database**: MySQL / PostgreSQL (using `DECIMAL(10,2)` for financial precision)
- **Frontend**: Laravel Blade + Tailwind CSS (Minimal SaaS aesthetic, Navy `#0f172a` sidebar, Teal `#0d9488` accents)
- **JavaScript**: Vanilla JS for real-time form math, DOM manipulation, and dynamic date synchronization
- **Charts**: Chart.js v4
- **Icons**: Lucide Icons
- **PDF Generation**: `barryvdh/laravel-dompdf`
- **Deployment Platform**: Railway (Nixpacks build system, automated database migrations, managed MySQL)

---

## 🚀 Setup & Installation Instructions

### 1. Prerequisites
- **PHP 8.2+** with required extensions: `pdo_mysql`, `mbstring`, `openssl`, `bcmath`, `curl`, `xml`, `zip`
- **Composer 2.x**
- **Node.js 18+** & npm
- **MySQL 8.0+** (or SQLite for local development)

### 2. Clone & Install Dependencies
```bash
git clone https://github.com/LipiAdhikari/RentMate.git
cd RentMate
composer install
npm install
```

### 3. Environment Configuration
Copy `.env.example` to `.env`:
```bash
cp .env.example .env
```

Configure your `.env` file:
```ini
APP_NAME=RentMate
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database Configuration (MySQL)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rentmate
DB_USERNAME=root
DB_PASSWORD=

# Session & Cache
SESSION_DRIVER=database
CACHE_STORE=database
```

### 4. Generate Application Key
```bash
php artisan key:generate
```

### 5. Run Database Migrations & Seeders
```bash
php artisan migrate --seed
```

### 6. Build Frontend Assets
```bash
npm run build
```

### 7. Start the Development Server
```bash
php artisan serve
```
Visit `http://localhost:8000` in your web browser. Default test account:
- **Email**: `tenant@rentmate.com`
- **Password**: `password123`

---

## ☁️ Deployment Overview (Railway)

RentMate is configured for zero-downtime deployment on **Railway**:
- **Build Configuration**: Defined in `nixpacks.toml`:
  - Installs PHP 8.2, Composer, Node.js, and required extensions (`pdo_mysql`, `mbstring`, `bcmath`, `gd`).
  - Automatically runs `composer install --optimize-autoloader --no-dev`.
  - Runs `npm install && npm run build`.
  - Automatically executes `php artisan migrate --force` and optimization commands on release.
- **Database**: Managed MySQL service on Railway connected via standard private environment variables (`DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).
- **Web Server**: Apache / PHP built-in worker with proxy headers trusted for SSL termination.

---

## 🧪 Automated Testing

RentMate includes a comprehensive feature test suite verifying all financial calculations, business logic, calendar conversions, security constraints, and UI flows:

```bash
php artisan test
```

**Test Coverage Summary (25 passed tests, 167 assertions)**:
1. `late payment on feb 15 for january rent auto detected as one month cleared`
2. `auto allocation applies payment to oldest unpaid month`
3. `can create monthly record with automatic calculations`
4. `validation rejects current reading lower than previous`
5. `recording multiple partial payments updates balance and status`
6. `overpayment sets overpaid status`
7. `can edit and delete record`
8. `nepali calendar conversion accuracy`
9. `all routes and exports accessible`
10. `user cannot access another users records` (Multi-tenant isolation)
11. `duplicate month prevention`
12. `historical rate preservation`
13. `dashboard calculations accuracy`
14. `settings change does not affect historical records`
15. `complete user workflow scenario from finalcheck spec`
16. `electricity reading update endpoint`
17. `water bill update endpoint`
18. `historical record creation verified and estimated`
19. `reports pdf export downloads successfully`
20. `user registration creates account and default settings`
21. `record payment using bs calendar fields`
22. `register page back button points to landing page`
23. `login page renders clean divider`
24. `record payment and payments page have back actions`
25. `that true is true` (Unit baseline)

---

## 📂 Project Structure

```
RentMate/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php             # User registration, login, logout
│   │   │   ├── DashboardController.php        # Dynamic KPI aggregates & Chart.js data
│   │   │   ├── DocsController.php             # In-app user documentation
│   │   │   ├── ElectricityController.php      # Electricity meter logs & reading updates
│   │   │   ├── HistoricalRecordController.php # Historical archive & confidence toggles
│   │   │   ├── LandingController.php          # Public landing page
│   │   │   ├── MonthlyRecordController.php    # Core ledger CRUD & recalculations
│   │   │   ├── PaymentController.php          # Multi-installment transaction management
│   │   │   ├── ProfileController.php          # User profile and security management
│   │   │   ├── ReportController.php           # Analytics, CSV & PDF statement exports
│   │   │   ├── SettingController.php          # Configurable rates & landlord info
│   │   │   └── WaterBillController.php        # Water utility tracking & split updates
│   │   ├── Middleware/
│   │   │   ├── EnsureAppKey.php               # Ensures APP_KEY is initialized
│   │   │   ├── EnsureSession.php              # Session preservation & regeneration
│   │   │   └── HandleProductionErrors.php     # User-friendly error pages in production
│   │   └── Requests/
│   │       └── StoreMonthlyRecordRequest.php  # Monthly record form validation rules
│   ├── Models/
│   │   ├── AdditionalExpense.php              # Variable charges model
│   │   ├── MonthlyRecord.php                  # Core record with recalculate() engine
│   │   ├── Payment.php                        # Payment transaction model
│   │   ├── RentalSetting.php                  # Per-user rental preferences
│   │   └── User.php                           # User authentication model
│   └── Services/
│       └── NepaliCalendar.php                 # Bikram Sambat (BS) conversion engine
├── bootstrap/
│   ├── app.php                                # Application middleware & exception handlers
│   └── providers.php                          # Service provider definitions
├── config/                                    # Standard Laravel framework configuration
├── database/
│   ├── migrations/                            # Schema migrations with unique multi-tenant indexes
│   └── seeders/DatabaseSeeder.php             # Cross-database demo data seeder
├── public/
│   ├── js/
│   │   ├── chart.min.js                       # Chart.js library
│   │   └── lucide.min.js                      # Lucide icons library
│   └── index.php                              # Application entry point
├── resources/
│   ├── css/app.css                            # Tailwind CSS 4 directives
│   ├── js/app.js                              # Application frontend bootstrap
│   └── views/                                 # Organized Blade templates
│       ├── auth/                              # Login and registration views
│       ├── components/                        # Shared reusable components
│       ├── dashboard/                         # Dashboard overview & analytics
│       ├── docs/                              # In-app documentation
│       ├── electricity/                       # Electricity tracking views
│       ├── errors/                            # Production 500 error templates
│       ├── historical/                        # Historical entry & archive views
│       ├── landing/                           # Public marketing landing page
│       ├── layouts/                           # Master application & auth layouts
│       ├── monthly-records/                   # Monthly record CRUD views
│       ├── payments/                          # Transactions log & installment records
│       ├── profile/                           # User account management views
│       ├── reports/                           # Reports & PDF template
│       ├── settings/                          # Rate configuration views
│       └── water/                             # Water utility views
├── routes/
│   ├── console.php                            # Artisan console commands
│   └── web.php                                # Web application routes
├── tests/
│   ├── Feature/RentMateTest.php               # Comprehensive test suite (25 tests)
│   └── TestCase.php                           # Base test case
├── nixpacks.toml                              # Railway deployment build definition
└── vite.config.js                             # Vite asset bundler configuration
```

---

## 🔮 Extending RentMate for Future Features

The codebase is built on clean Laravel conventions for easy extension:

1. **Multi-Property / Landlord Mode**:
   - Add a `properties` table (`id`, `owner_id`, `address`, `default_rent`).
   - Add `property_id` foreign key to `monthly_records` and `rental_settings`.
2. **Automated Reminders (SMS / Email)**:
   - Configure Laravel command in `routes/console.php` with scheduler:
     ```php
     Schedule::command('rentmate:reminders')->dailyAt('09:00');
     ```
   - Connect to local SMS gateways (Sparrow SMS, Aakash SMS in Nepal) using HTTP client.
3. **Electricity Meter Photo Proofs**:
   - Add `meter_photo_path` column to `monthly_records`.
   - Store images using Laravel Flysystem (`Storage::disk('public')`).
4. **Direct Digital Wallet API (eSewa / Khalti)**:
   - Wire eSewa ePay or Khalti Payment Gateway webhooks into `PaymentController` for automated transaction reconciliation.

---

## 🔒 Security & Code Standards

- **Secret Protection**: `.env` is never tracked in Git; only safe `.env.example` placeholders are committed.
- **Financial Precision**: All monetary values and meter readings use `DECIMAL(10,2)` columns.
- **Server-Side Enforcement**: All financial math, balances, and payment status evaluations occur server-side.
- **Multi-Tenant Data Isolation**: Database queries enforce user ownership via `Auth::id()` scoping and composite unique database constraints (`user_id`, `year`, `month`).
- **Rate Preservation Guarantee**: Changing future settings never modifies rates on existing historical records.
- **SQL & CSRF Protection**: Complete protection via Eloquent parameter binding and Laravel CSRF tokens.
- **Rate Limiting**: Authentication endpoints are rate-limited to 5 requests per minute per IP.

---

## 📄 License

Open-sourced software licensed under the [MIT License](LICENSE).
