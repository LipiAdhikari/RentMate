# RentMate – Personal Rent & Expense Tracker

A modern, robust web application built with **PHP 8.2+**, **Laravel 12**, **MySQL**, **Tailwind CSS**, and **Blade** designed for tenants to maintain accurate records of monthly rent, electricity bills, water bills, multi-installment payments, outstanding balances, and historical rental archives.

---

## 🌟 Key Features

1. **Dynamic SaaS Dashboard**:
   - Real-time calculations of current month expenses, house rent, electricity bill, 50% water share, total paid, and outstanding balance.
   - All-time cumulative expense metrics since move-in date.
   - Interactive Chart.js charts: Monthly Expense Breakdown (Stacked Bar) & Electricity Unit Consumption (kWh).
   - Instant alerts for pending / partially paid months with one-click payment logging.
   - Recent payment transaction stream.

2. **Monthly Rental Records (CRUD)**:
   - Complete record creation, viewing, editing, and deletion.
   - Dynamic calculations with instant live browser previews.
   - Unique constraints per rental month/year to prevent duplicate records.
   - Detailed statement views with printable receipts.

3. **Electricity Meter Tracker**:
   - Formula: $\text{Units Consumed} = \text{Current Reading} - \text{Previous Reading}$.
   - Formula: $\text{Electricity Bill} = \text{Units} \times \text{Electricity Rate}$.
   - Validation prevents current reading from being lower than previous reading.
   - Consumption analytics and historical trend charts.

4. **Water Bill Sharing (50/50 Split)**:
   - Configurable tenant/owner split percentage (Default: 50% tenant, 50% landlord).
   - Formula: $\text{Tenant Share} = \text{Total Water Bill} \times (\text{Tenant \%} / 100)$.
   - Owner share automatically computed and tracked.

5. **Payment Management & Installments**:
   - Supports multiple partial installments against any single month.
   - Methods supported: **eSewa**, **Khalti**, **Bank Transfer**, **Cash**, and **Other**.
   - Automatic status lifecycle: `Unpaid` → `Partially Paid` → `Fully Paid` → `Overpaid`.
   - Never overwrites previous transactions; preserves complete audit trails.

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

---

## 🛠️ Technology Stack

- **Backend**: Laravel 12 (PHP 8.2+)
- **Database**: MySQL (using `DECIMAL(10,2)` for financial precision)
- **Frontend**: Laravel Blade + Tailwind CSS (Minimal SaaS aesthetic, Navy `#0f172a` sidebar, Teal `#0d9488` accents)
- **JavaScript**: Vanilla JS for real-time form math and DOM manipulation
- **Charts**: Chart.js v4
- **Icons**: Lucide Icons
- **PDF Generation**: `barryvdh/laravel-dompdf`

---

## 🚀 Setup & Installation Instructions

### 1. Prerequisites
- **PHP 8.2+** with extensions: `pdo_mysql`, `mbstring`, `openssl`, `bcmath`, `curl`, `xml`
- **Composer 2.x**
- **MySQL / MariaDB** (Running locally)
- **Node.js** (v18+)

### 2. Clone / Setup Project
```bash
cd c:\Users\Lipi\Projects\RentMate
composer install
```

### 3. Database Configuration
In `.env`:
```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3307        # Or 3306 depending on your local MySQL setup
DB_DATABASE=rentmate
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Run Migrations & Seeders
Execute the migrations to generate normalized tables with transactions and constraints, and seed realistic demo records:
```bash
php artisan migrate --seed
```

### 5. Start the Development Server
```bash
php artisan serve
```
Open your browser and navigate to:
```
http://localhost:8000
```

---

## 🧪 Automated Testing

RentMate includes a comprehensive feature test suite verifying all 12 core functional and calculation scenarios:
```bash
php artisan test --filter=RentMateTest
```

**Tested Scenarios**:
1. Automatic calculation of rent, electricity, and water shares upon record creation.
2. Validation rejecting current meter readings lower than previous readings.
3. Multiple partial payment installments and automatic status progression (`unpaid` → `partially_paid` → `paid`).
4. Overpayment handling and balance calculations.
5. Record editing, recalculation, and cascading deletions.
6. Historical record logging with `is_verified` confidence toggles.
7. Dashboard dynamic calculations matching exact database queries.
8. Rate preservation guarantee across settings updates.
9. Route accessibility and CSV/PDF export generation.

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
│   │   └── SettingController.php          # Configurable rates & landlord info
│   └── Models/
│       ├── MonthlyRecord.php              # Monthly model with recalculate() engine
│       ├── Payment.php                    # Payment transaction model
│       ├── AdditionalExpense.php          # Variable charges (waste, repairs)
│       └── RentalSetting.php              # Global rental configuration
├── database/
│   ├── migrations/                        # Normalized MySQL schema with DECIMAL types
│   └── seeders/DatabaseSeeder.php         # Seed data for past months
├── resources/views/
│   ├── layouts/app.blade.php              # Main layout (Navy sidebar, topbar, modals)
│   ├── dashboard/                         # Dashboard views & analytics
│   ├── monthly-records/                   # CRUD views with live math
│   ├── electricity/                       # Meter reading logs & trend charts
│   ├── water/                             # 50/50 water sharing tables
│   ├── payments/                          # Transactions log & method filters
│   ├── historical/                        # Archive entry with verification toggle
│   ├── reports/                           # Analytics, charts, CSV & PDF views
│   └── settings/                          # Rate configurations
└── tests/Feature/RentMateTest.php         # Automated verification suite
```

---

## 📄 License
Open-sourced software licensed under the [MIT License](LICENSE).
