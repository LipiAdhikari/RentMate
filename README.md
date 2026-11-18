# RentMate – Personal Rent & Expense Tracker

A modern, robust web application built with **PHP 8.2+**, **Laravel 12**, **PostgreSQL/MySQL**, **Tailwind CSS**, and **Blade** designed for tenants to maintain accurate records of monthly rent, electricity bills, water bills, multi-installment payments, outstanding balances, and historical rental archives.

---

## 🌟 Key Features

1. **Dynamic SaaS Dashboard**:
   - Real-time calculations of current month expenses, house rent, electricity bill, water share, total paid, and outstanding balance.
   - All-time cumulative expense metrics since move-in date.
   - Interactive Chart.js charts: Monthly Expense Breakdown (Stacked Bar) & Electricity Unit Consumption (kWh).
   - Instant alerts for pending / partially paid months with one-click payment logging.
   - Recent payment transaction stream.

2. **Monthly Rental Records (CRUD)**:
   - Complete record creation, viewing, editing, and deletion.
   - Dynamic calculations with instant live browser previews.
   - Unique constraints per rental month/year per user to prevent duplicate records.
   - Detailed statement views with printable receipts.
   - Multi-tenant data isolation.

3. **Electricity Meter Tracker**:
   - Units Consumed = Current Reading - Previous Reading.
   - Electricity Bill = Units × Electricity Rate.
   - Validation prevents current reading from being lower than previous reading.
   - Consumption analytics and historical trend charts.

4. **Water Bill Sharing (Configurable Split)**:
   - Configurable tenant/owner split percentage (Default: 50% tenant, 50% landlord).
   - Tenant Share = Total Water Bill × (Tenant % / 100).
   - Owner share automatically computed and tracked.

5. **Payment Management & Installments**:
   - Supports multiple partial installments against any single month.
   - Methods supported: **eSewa**, **Khalti**, **Bank Transfer**, **Cash**, and **Other**.
   - Automatic status lifecycle: `Unpaid` → `Partially Paid` → `Fully Paid` → `Overpaid`.
   - Never overwrites previous transactions; preserves complete audit trails.
   - Auto-allocation to oldest unpaid month.

6. **Historical Records & Archives**:
   - Dedicated section to log older months from previous years of tenancy.
   - Distinguishes **Verified** records (backed by receipts/statements) from **Estimated / Incomplete** records.
   - Adheres to the strict financial rule: *never invent or guess missing payment amounts*.

7. **Reports & Exports**:
   - Filter by year, date range, and payment status.
   - Export structured data to **CSV / Excel** with UTF-8 BOM.
   - Generate and download printable **PDF Statements** via DomPDF.

8. **Configurable Rental Settings**:
   - Configurable monthly rent (Default: NPR 16,000.00).
   - Configurable electricity rate (Default: NPR 10.00/unit).
   - Configurable water sharing percentage (Default: 50.00%).
   - Currency configuration (NPR).
   - Landlord & property details with payment reminder day preference.
   - **Rate Preservation Guarantee**: Changing future settings never alters rates on existing historical records.

9. **Authentication & User Management**:
   - User registration, login, and logout.
   - Password reset support.
   - Per-user data isolation - each user sees only their own records.
   - Rate limiting on auth endpoints.
   - User-specific settings and records.

---

## 🛠️ Technology Stack

- **Backend**: Laravel 12 (PHP 8.2+)
- **Database**: PostgreSQL / MySQL (using `DECIMAL(10,2)` for financial precision)
- **Frontend**: Laravel Blade + Tailwind CSS (Minimal SaaS aesthetic, Navy `#0f172a` sidebar, Teal `#0d9488` accents)
- **JavaScript**: Vanilla JS for real-time form math and DOM manipulation
- **Charts**: Chart.js v4
- **Icons**: Lucide Icons
- **PDF Generation**: barryvdh/laravel-dompdf
- **Authentication**: Laravel built-in auth with rate limiting

---

## 🚀 Setup & Installation Instructions

### 1. Prerequisites
- **PHP 8.2+** with extensions: `pdo_mysql` or `pdo_pgsql`, `mbstring`, `openssl`, `bcmath`, `curl`, `xml`
- **Composer 2.x**
- **MySQL / MariaDB** or **PostgreSQL** (Running locally)
- **Node.js** (v18+)

### 2. Clone / Setup Project
```bash
cd RentMate
composer install
npm install
```

### 3. Environment Configuration
Copy `.env.example` to `.env` and configure:

```ini
APP_NAME=RentMate
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database (MySQL or PostgreSQL/Supabase)
DB_CONNECTION=pgsql
DB_HOST=your-db-host
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=your-username
DB_PASSWORD=your-password
DB_SSLMODE=require

# Session
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

### 4. Generate Application Key
```bash
php artisan key:generate
```

### 5. Run Migrations & Seeders
```bash
php artisan migrate --seed
```

### 6. Start the Development Server
```bash
php artisan serve
```
Open your browser and navigate to `http://localhost:8000`.

---

## 🧪 Automated Testing

RentMate includes a comprehensive feature test suite:

```bash
php artisan test
```

**Tested Scenarios**:
1. Automatic calculation of rent, electricity, and water shares upon record creation.
2. Validation rejecting current meter readings lower than previous readings.
3. Multiple partial payment installments and automatic status progression.
4. Overpayment handling and balance calculations.
5. Record editing, recalculation, and cascading deletions.
6. Historical record logging with `is_verified` confidence toggles.
7. Dashboard dynamic calculations matching exact database queries.
8. Rate preservation guarantee across settings updates.
9. Route accessibility and CSV/PDF export generation.
10. User data isolation (multi-tenancy).
11. Historical rate preservation when settings change.
12. Duplicate month prevention.

---

## 📂 Project Architecture

```
RentMate/
├── app/
│   ├── Http/Controllers/
│   │   ├── DashboardController.php        # Dynamic KPI aggregates & Chart.js data
│   │   ├── MonthlyRecordController.php    # Core monthly record CRUD & calculations
│   │   ├── ElectricityController.php      # Electricity meter tracker
│   │   ├── WaterBillController.php        # 50/50 water utility tracker
│   │   ├── PaymentController.php          # Multi-installment transaction management
│   │   ├── HistoricalRecordController.php # Historical archive & verification tracking
│   │   ├── ReportController.php           # Analytics, CSV & PDF statement exports
│   │   ├── SettingController.php          # Configurable rates & landlord info
│   │   ├── AuthController.php             # Authentication (login/register/logout)
│   │   ├── ProfileController.php          # User profile management
│   │   └── LandingController.php          # Landing page
│   ├── Http/Middleware/
│   │   └── EnsureAppKey.php               # Ensures APP_KEY is configured
│   ├── Http/Requests/
│   │   └── StoreMonthlyRecordRequest.php  # Form request validation
│   ├── Models/
│   │   ├── MonthlyRecord.php              # Monthly model with recalculate() engine
│   │   ├── Payment.php                    # Payment transaction model
│   │   ├── AdditionalExpense.php          # Variable charges model
│   │   ├── RentalSetting.php              # Global rental configuration
│   │   └── User.php                       # User authentication model
│   └── Services/
│       └── NepaliCalendar.php             # Bikram Sambat calendar conversion
├── database/
│   ├── migrations/                        # Normalized MySQL/PostgreSQL schema
│   └── seeders/DatabaseSeeder.php         # Seed data for demo records
├── resources/views/
│   ├── layouts/app.blade.php              # Main layout
│   ├── auth/                              # Login & register views
│   ├── dashboard/                         # Dashboard views & analytics
│   ├── monthly-records/                   # CRUD views with live math
│   ├── electricity/                       # Meter reading logs & trend charts
│   ├── water/                             # 50/50 water sharing tables
│   ├── payments/                          # Transactions log & method filters
│   ├── historical/                        # Archive entry with verification toggle
│   ├── reports/                           # Analytics, charts, CSV & PDF views
│   ├── settings/                          # Rate configurations
│   ├── profile/                           # User profile views
│   └── components/                        # Reusable Blade components
├── routes/
│   └── web.php                            # All application routes
├── tests/
│   └── Feature/RentMateTest.php           # Automated verification suite
└── vite.config.js                         # Vite build configuration
```

---

## 🔒 Security

- All financial data is stored using `DECIMAL` type for precision.
- All calculations are performed server-side.
- User data is isolated - users can only access their own records.
- Rate limiting on authentication endpoints (5 requests/minute).
- CSRF protection on all forms.
- SQL injection protection via Eloquent ORM.
- Rate preservation guarantee - existing records are not affected by settings changes.

---

## 📄 License

Open-sourced software licensed under the [MIT License](LICENSE).
