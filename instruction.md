# ROLE

Act as a senior full-stack Laravel developer, database architect, and UI/UX designer. Build a complete, functional, modern rental expense and payment tracking web application from scratch.

# TASK

Develop a web application named **RentMate – Personal Rent & Expense Tracker**.

The application is initially designed for a single tenant who wants to maintain accurate records of monthly rent, electricity bills, water bills, payments, outstanding balances, and historical rental expenses.

The application must be practical, lightweight, responsive, beginner-friendly, and designed so that additional features can be introduced in the future.

# CONTEXT

My actual rental situation:

- Monthly house rent: NPR 16,000.
- Electricity rate: NPR 10 per unit.
- Electricity is calculated using previous and current meter readings.
- Water bill is divided equally: 50% tenant and 50% house owner.
- I have been living in this rented house for approximately two years.
- I do not have a complete historical record of my payments.
- Sometimes I may pay rent in multiple installments during a month.
- I need to track how much I have paid, how much I owe, and my total rental expenses.

Use these values as initial configurable settings, not hardcoded permanent values.

# TECHNOLOGY STACK

Use the following technologies:

- Backend: PHP 8.2+ with Laravel.
- Frontend: Laravel Blade.
- Styling: Tailwind CSS.
- Database: MySQL.
- JavaScript: Vanilla JavaScript.
- Charts: Chart.js.
- Icons: Lucide Icons.

Use Laravel's MVC architecture, migrations, Eloquent relationships, validation, middleware, and proper backend practices.

# CORE FEATURES

## 1. Dashboard

Create a modern dashboard displaying:

- Current month's total expenses.
- Total rent amount.
- Total electricity bill.
- Total water bill share.
- Total amount paid.
- Outstanding balance.
- Total rental expenses since moving in.
- Monthly expense overview.
- Electricity consumption chart.
- Recent payment transactions.
- Unpaid and partially paid months.

All dashboard figures must be calculated dynamically from database records.

## 2. Rental Settings

Allow the user to configure:

- Monthly rent amount.
- Electricity price per unit.
- Water bill sharing percentage.
- Rental start date.
- Currency (NPR).
- Payment reminder preferences.

Default values:

- Rent: NPR 16,000.
- Electricity: NPR 10/unit.
- Tenant water share: 50%.

Historical monthly records must preserve the rates that applied when they were created, even if settings change later.

## 3. Monthly Records

Create a complete monthly rental record management system.

Each monthly record should include:

- Month and year.
- Rent amount.
- Previous electricity meter reading.
- Current electricity meter reading.
- Electricity units consumed.
- Electricity rate.
- Electricity amount.
- Total water bill.
- Tenant water share.
- Other additional charges.
- Total monthly expenses.
- Total paid amount.
- Remaining balance.
- Payment status.

Allow users to create, edit, view, and delete records with proper validation.

Prevent duplicate records for the same rental month.

## 4. Electricity Tracker

Implement automatic electricity calculations:

Electricity Units = Current Meter Reading - Previous Meter Reading.

Electricity Bill = Electricity Units × Electricity Rate.

Features:

- Enter meter readings.
- Automatically calculate consumed units.
- Automatically calculate electricity cost.
- Validate that current reading cannot be lower than previous reading.
- Maintain historical electricity consumption.
- Display monthly consumption charts.

## 5. Water Bill Tracker

Features:

- Enter total water bill.
- Automatically calculate tenant's share.
- Automatically calculate owner's share.
- Allow configurable sharing percentages.
- Maintain monthly water bill history.

Formula:
Tenant Share = Total Water Bill × Tenant Percentage / 100.

## 6. Payment Management

This is one of the most important modules.

Allow multiple payments against a single monthly record.

Each payment must include:

- Payment date.
- Amount.
- Payment method (Cash, eSewa, Khalti, Bank Transfer, Other).
- Transaction reference (optional).
- Notes (optional).

Automatically calculate:

Total Paid = Sum of all payments for that month.

Remaining Balance = Total Monthly Expenses - Total Paid.

Payment statuses:

- Unpaid.
- Partially Paid.
- Fully Paid.
- Overpaid.

Never overwrite previous payment transactions when adding a new payment.

## 7. Historical Records

Create a dedicated historical data entry section.

Allow the user to manually enter records from previous months.

Important:

- Allow entering historical rent, water, electricity and payments.
- Allow incomplete historical records.
- Distinguish verified records from estimated or incomplete records.
- Never invent missing payment amounts.
- Allow editing historical records.
- Provide a historical expense summary.

## 8. Reports and Analytics

Create a reports section with:

- Monthly expense reports.
- Yearly expense reports.
- Total rent paid.
- Total electricity expenses.
- Total water expenses.
- Total payments made.
- Outstanding balances.
- Electricity consumption trends.

Include date filters and charts.

Allow exporting reports as PDF and Excel/CSV.

## 9. User Interface

Create a professional, clean, modern dashboard.

Design direction:

- Minimal SaaS dashboard aesthetic.
- White/light background.
- Navy or dark slate sidebar.
- Teal/blue primary accent.
- Clean typography.
- Rounded cards.
- Subtle shadows.
- Consistent spacing.
- Lucide icons.
- Responsive layouts for desktop, tablet and mobile.

Sidebar navigation:

1. Dashboard
2. Monthly Records
3. Electricity
4. Water Bills
5. Payments
6. Historical Records
7. Reports
8. Settings

Include:

- Search and filters.
- Confirmation dialogs for destructive actions.
- Success and error notifications.
- Empty states.
- Loading states where appropriate.
- Form validation messages.

Avoid unnecessary animations, excessive gradients, oversized cards, and cluttered layouts.

# DATABASE REQUIREMENTS

Design a normalized MySQL database with appropriate tables, foreign keys, indexes, timestamps, and Eloquent relationships.

Suggested tables:

- rental_settings
- monthly_records
- payments
- additional_expenses

You may introduce additional tables if necessary.

Important database rules:

- Use DECIMAL for monetary values, not FLOAT.
- Preserve historical electricity rates and rent amounts.
- Store each payment as a separate transaction.
- Prevent duplicate monthly records.
- Maintain accurate outstanding balances.
- Use database transactions for operations involving multiple related records.
- Never calculate financial balances using unreliable frontend-only values.

# SECURITY AND CODE QUALITY

- Validate all user input on the backend.
- Use CSRF protection.
- Use Eloquent ORM and parameterized database operations.
- Protect against SQL injection and XSS.
- Use authentication if the application is deployed or accessible beyond the local machine.
- Organize code using Laravel best practices.
- Avoid duplicate logic.
- Create reusable Blade components.
- Write readable, maintainable code.
- Do not use fake or hardcoded dashboard statistics.

# DEVELOPMENT INSTRUCTIONS

Build the application incrementally.

1. Inspect the existing development environment and project directory before making changes.
2. If a Laravel project already exists, work inside it instead of creating another project.
3. Set up the Laravel application and MySQL connection.
4. Create migrations and database relationships.
5. Implement the backend logic and controllers.
6. Build the Blade layouts and frontend components.
7. Implement all CRUD operations.
8. Implement automatic financial calculations.
9. Connect the dashboard to real database data.
10. Implement reports and exports.
11. Test all major functionalities.
12. Fix errors before considering the project complete.

Do not create placeholder buttons or non-functional pages.

Do not skip backend implementation by building only a frontend mockup.

Do not delete or overwrite existing project files without inspecting them first.

If a required dependency is missing, explain it and install it only when appropriate.
h

# TESTING REQUIREMENTS

Test these scenarios:

1. Create a monthly rent record.
2. Calculate electricity using meter readings.
3. Divide the water bill equally.
4. Record one payment.
5. Record multiple partial payments.
6. Verify remaining balances.
7. Verify fully paid and unpaid statuses.
8. Verify overpayment handling.
9. Edit and delete records.
10. Add historical records.
11. Verify dashboard calculations.
12. Verify that changing future settings does not alter historical records.

# FINAL DELIVERABLE

Deliver a fully functional RentMate application with:

- Complete Laravel source code.
- MySQL database migrations.
- Working dashboard.
- Functional CRUD modules.
- Automatic rent, water and electricity calculations.
- Payment tracking.
- Historical record management.
- Reports and analytics.
- Responsive UI.
- README.md with setup instructions.
- instruction.md explaining project architecture, modules, database structure and how to extend the application.

Keep the code understandable and maintainable.

**FINAL PRIORITY:** Build a reliable personal financial tracking application first. Prioritize calculation accuracy, payment history, historical records and outstanding balance tracking over unnecessary advanced features.

---

# ARCHITECTURE, DATABASE STRUCTURE & EXTENSION GUIDE

## 1. Project Architecture Overview

RentMate is built on Laravel 12 utilizing modern MVC architecture and financial data integrity principles:

```
[ HTTP Requests / Browser ]
           │
           ▼
[ Routes (routes/web.php) ]
           │
           ▼
[ Controllers (app/Http/Controllers/*) ]
   ├── DashboardController         ──> Dynamic KPI calculations & Chart.js aggregates
   ├── MonthlyRecordController     ──> Monthly rental record CRUD & live previews
   ├── ElectricityController       ──> Dedicated meter tracking & calculations
   ├── WaterBillController         ──> 50/50 water division management
   ├── PaymentController           ──> Installments & payment transactions
   ├── HistoricalRecordController  ──> Incomplete/verified historical archiving
   ├── ReportController            ──> Analytics, CSV and PDF export engines
   └── SettingController           ──> Contractual rates & configuration
           │
           ▼
[ Eloquent Models (app/Models/*) ]
   ├── MonthlyRecord               ──> Holds recalculate() engine & business logic
   ├── Payment                     ──> Booted hooks auto-trigger recalculate()
   ├── AdditionalExpense           ──> Booted hooks auto-trigger recalculate()
   └── RentalSetting               ──> Singleton active configuration
           │
           ▼
[ MySQL Database (Normalized Schema) ]
   ├── rental_settings
   ├── monthly_records
   ├── payments
   └── additional_expenses
```

---

## 2. Database Schema & Data Dictionary

All financial columns strictly use `DECIMAL(10,2)` rather than floating-point types to eliminate IEEE 754 rounding errors.

### `rental_settings`

Global active defaults for future monthly records.

- `id` (PK, unsigned bigint)
- `rent_amount` (`DECIMAL(10,2)`, default: `16000.00`)
- `electricity_rate` (`DECIMAL(10,2)`, default: `10.00`)
- `water_share_percentage` (`DECIMAL(5,2)`, default: `50.00`)
- `currency` (`VARCHAR(10)`, default: `'NPR'`)
- `rental_start_date` (`DATE`, default: `'2024-11-01'`)
- `owner_name`, `owner_phone`, `owner_email`, `property_address` (`VARCHAR(255)`, nullable)
- `payment_reminder_days` (`SMALLINT UNSIGNED`, default: `5`)
- `notes` (`TEXT`, nullable)
- `timestamps`

### `monthly_records`

Core monthly ledger records. Preserves contractual rates at time of entry.

- `id` (PK, unsigned bigint)
- `year` (`SMALLINT UNSIGNED`, e.g. `2026`)
- `month` (`TINYINT UNSIGNED`, 1–12)
- `record_date` (`DATE`, first day of rental month, indexed)
- `rent_amount` (`DECIMAL(10,2)`)
- `prev_electricity_reading` (`DECIMAL(10,2)`, default: `0.00`)
- `curr_electricity_reading` (`DECIMAL(10,2)`, default: `0.00`)
- `electricity_units` (`DECIMAL(10,2)`, default: `0.00`)
- `electricity_rate` (`DECIMAL(10,2)`, default: `10.00`)
- `electricity_amount` (`DECIMAL(10,2)`, default: `0.00`)
- `total_water_bill` (`DECIMAL(10,2)`, default: `0.00`)
- `tenant_water_percentage` (`DECIMAL(5,2)`, default: `50.00`)
- `water_amount` (`DECIMAL(10,2)`, tenant's share, default: `0.00`)
- `other_charges` (`DECIMAL(10,2)`, default: `0.00`)
- `total_expenses` (`DECIMAL(10,2)`, default: `0.00`)
- `total_paid` (`DECIMAL(10,2)`, default: `0.00`)
- `balance` (`DECIMAL(10,2)`, default: `0.00`)
- `status` (`ENUM('unpaid', 'partially_paid', 'paid', 'overpaid')`, indexed)
- `is_historical` (`BOOLEAN`, default: `false`)
- `is_verified` (`BOOLEAN`, default: `true`, distinguishes confirmed from estimated)
- `notes` (`TEXT`, nullable)
- `timestamps`
- Unique Index: `['year', 'month']` prevents duplicate records for the same month.

### `payments`

Individual payment transactions. Supports multiple installments.

- `id` (PK, unsigned bigint)
- `monthly_record_id` (FK references `monthly_records.id` ON DELETE CASCADE)
- `payment_date` (`DATE`, indexed)
- `amount` (`DECIMAL(10,2)`)
- `payment_method` (`ENUM('Cash', 'eSewa', 'Khalti', 'Bank Transfer', 'Other')`)
- `transaction_reference` (`VARCHAR(255)`, nullable)
- `notes` (`TEXT`, nullable)
- `timestamps`

### `additional_expenses`

Discretionary or variable monthly charges (waste management, repairs, shared internet).

- `id` (PK, unsigned bigint)
- `monthly_record_id` (FK references `monthly_records.id` ON DELETE CASCADE)
- `title` (`VARCHAR(255)`)
- `amount` (`DECIMAL(10,2)`)
- `category` (`VARCHAR(100)`, default: `'maintenance'`)
- `notes` (`TEXT`, nullable)
- `timestamps`

---

## 3. Financial Calculation & Integrity Engine

All balance calculations occur server-side through `MonthlyRecord::recalculate(bool $save)`:

1. **Electricity Consumed Units**:
   $$\text{Units} = \max(0, \text{curr\_reading} - \text{prev\_reading})$$
   Validated so $\text{curr\_reading} \ge \text{prev\_reading}$.

2. **Electricity Cost**:
   $$\text{Electricity Cost} = \text{Units} \times \text{Electricity Rate}$$

3. **Tenant Water Share (50%)**:
   $$\text{Tenant Water Share} = \text{Total Water Bill} \times \frac{\text{Tenant Percentage}}{100}$$
   $$\text{Owner Water Share} = \text{Total Water Bill} - \text{Tenant Water Share}$$

4. **Additional Charges**:
   $$\text{Other Charges} = \sum(\text{additional\_expenses.amount})$$

5. **Total Monthly Expenses**:
   $$\text{Total Expenses} = \text{Rent} + \text{Electricity Cost} + \text{Tenant Water Share} + \text{Other Charges}$$

6. **Total Paid & Outstanding Balance**:
   $$\text{Total Paid} = \sum(\text{payments.amount})$$
   $$\text{Balance} = \text{Total Expenses} - \text{Total Paid}$$

7. **Status Determinant**:
    - $\text{Total Paid} \le 0 \implies \text{unpaid}$
    - $0 < \text{Total Paid} < \text{Total Expenses} \implies \text{partially\_paid}$
    - $\text{Total Paid} = \text{Total Expenses} \implies \text{paid}$
    - $\text{Total Paid} > \text{Total Expenses} \implies \text{overpaid}$

Whenever a `Payment` or `AdditionalExpense` is saved or deleted, Eloquent lifecycle hooks (`saved`, `deleted`) automatically invoke `recalculate(true)` on the parent `MonthlyRecord`, ensuring 100% data consistency.

---

## 4. How to Extend the Application

The RentMate architecture is structured for easy forward extension:

1. **Multi-Property or Multi-Tenant Mode**:
    - Add a `properties` or `tenancies` table.
    - Add `property_id` foreign key to `monthly_records` and `rental_settings`.
    - Update routes and scopes using `TenantScope` or middleware.

2. **Automated Reminders & Notifications (SMS / Email)**:
    - Schedule an Artisan command in `routes/console.php`:
        ```php
        Schedule::command('rentmate:send-reminders')->dailyAt('09:00');
        ```
    - Connect to local SMS gateways (Sparrow SMS, Aakash SMS in Nepal) using HTTP Guzzle.

3. **Meter Reading Photo Uploads**:
    - Add `meter_photo_path` to `monthly_records`.
    - Use Laravel Flysystem Local or S3 storage disk to allow tenants to snap photo evidence of their electricity meter each month.

4. **Direct Digital Wallet API Integration**:
    - Integrate eSewa ePay or Khalti Payment Gateway webhooks into `PaymentController` to verify transactions via API.
