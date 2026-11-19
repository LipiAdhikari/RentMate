# ROLE

Act as a Senior QA Automation Engineer, Full-Stack Laravel Developer, Database Tester, Security Auditor, and Software Reliability Engineer.

You are responsible for performing a complete end-to-end functional audit, testing, debugging, fixing, and validation of my existing RentMate web application.

# APPLICATION

Your mission is to ensure that RentMate is a genuinely functional, reliable, secure, and production-ready application.

IMPORTANT:
Do not just inspect the UI or tell me what might be wrong. Actually test the application, inspect its source code and database, reproduce bugs, fix them, and verify that the fixes work.

# 1. INITIAL PROJECT INSPECTION

Before making changes:

1. Inspect the complete project directory.
2. Identify the framework, PHP version, Laravel version, and dependencies.
3. Inspect all routes, controllers, models, migrations, middleware, Blade templates, JavaScript, and CSS.
4. Inspect the database schema and relationships.
5. Identify all existing application modules.
6. Check the current deployment configuration.
7. Inspect existing tests and test coverage.
8. Identify existing bugs and incomplete implementations.

Do not recreate the application.

Preserve the existing architecture and working features.

Create a detailed audit report before modifying the code.

# 2. TEST EVERY APPLICATION MODULE

Test every page and every feature.

## A. Dashboard

Verify:

- Dashboard loads without errors.
- All statistics come from actual database records.
- Total monthly expenses are correct.
- Total payments are correct.
- Outstanding balances are correct.
- Rent and utility balances are distinguishable.
- Monthly expense charts display accurate values.
- Recent transactions display correctly.
- Empty states display when no records exist.
- Dashboard updates immediately after adding or editing records.
- Date filters work correctly.
- No hardcoded sample statistics remain.

## B. Monthly Records

Test:

- Create a monthly record.
- View a monthly record.
- Edit a monthly record.
- Delete a monthly record.
- Filter records by month and year.
- Search existing records.
- Prevent duplicate records for the same rental month.
- Validate required fields.
- Reject invalid amounts.
- Reject negative values.
- Verify all calculated totals.
- Verify payment status.
- Verify outstanding balance.
- Verify records remain available after refreshing the page.

## C. Electricity Tracker

Test:

- Add electricity readings.
- Edit electricity readings.
- Delete electricity records.
- Calculate consumed units correctly.
- Calculate electricity bills correctly.
- Reject a current meter reading lower than the previous reading.
- Reject invalid or negative meter readings.
- Verify electricity rates.
- Verify historical rates remain unchanged after settings updates.
- Verify monthly electricity charts.
- Verify electricity expenses are reflected in monthly records and dashboard.

Calculation:

Units = Current Reading - Previous Reading

Electricity Cost = Units × Rate Per Unit

Use decimal-safe monetary calculations and avoid floating-point rounding errors.

## D. Water Bills

Test:

- Add water bills.
- Edit water bills.
- Delete water bills.
- Calculate tenant share.
- Calculate owner share.
- Change sharing percentages.
- Verify the two shares always equal the total bill.
- Reject invalid sharing percentages.
- Verify historical sharing rates are preserved.
- Verify water expenses appear correctly in monthly records and reports.

Default configuration:
Tenant: 50%
Owner: 50%

## E. Payments & Transactions

This is the most critical module.

Test:

- Add a payment.
- Edit a payment.
- Delete a payment.
- Add multiple payments for the same month.
- Add partial payments.
- Add full payments.
- Add overpayments.
- Record cash payments.
- Record eSewa payments.
- Record Khalti payments.
- Record bank transfers.
- Add transaction references.
- Filter payment history.
- Search transactions.
- Verify payment dates.
- Verify payment allocations.
- Verify payment history remains accurate after editing or deleting a transaction.

Ensure that rent payment does not automatically clear electricity and water dues unless the payment allocation actually covers them.

Prevent duplicate payment counting.

Calculate:

Total Due = Rent + Electricity + Tenant Water Share + Additional Expenses

Total Paid = Sum of valid allocated payments

Outstanding = Total Due - Total Paid

Statuses:

- Unpaid
- Partially Paid
- Fully Paid
- Overpaid

Use database transactions when updating financial records.

Never silently discard a payment transaction.

## F. Historical Records

Test:

- Add previous rental records.
- Edit historical records.
- Delete historical records.
- Add verified records.
- Add estimated records.
- Add incomplete records.
- Record unknown electricity bills.
- Record unknown water bills.
- Add historical payments.
- Verify historical balances.
- Prevent missing information from being treated as confirmed financial data.
- Verify historical records appear in dashboard and reports.

Historical records must not be fabricated or automatically marked as verified.

## G. Reports & Analytics

Test:

- Monthly reports.
- Yearly reports.
- Expense breakdown.
- Payment summaries.
- Outstanding balance reports.
- Electricity consumption charts.
- Water expense reports.
- Date filtering.
- Search and sorting.
- CSV export.
- Excel export if supported.
- PDF export.

Verify exported values match the database and dashboard.

Test exports with empty datasets and large datasets.

## H. Rental Settings

Test:

- Update rent amount.
- Update electricity rate.
- Update water-sharing percentage.
- Update rental start date.
- Change currency settings if supported.
- Update payment reminders.
- Save settings.
- Refresh the application.
- Verify settings persist.
- Verify historical records retain their original rates and amounts.
- Verify new records use updated settings.

# 3. COMPLETE USER WORKFLOW TESTING

Simulate a realistic tenant using RentMate.

Use a separate test database or isolated test records.

Scenario:

Rental configuration:

- Rent: NPR 16,000.
- Electricity rate: NPR 10/unit.
- Water sharing: 50/50.

Create a November rental record:

Previous meter reading: 1,250
Current meter reading: 1,380
Electricity consumption: 130 units
Electricity cost: NPR 1,300
Total water bill: NPR 600
Tenant water share: NPR 300

Expected total expense:

Rent: 16,000
Electricity: 1,300
Water: 300
Total: NPR 17,600

Now record three payments:

Payment 1: NPR 10,000
Payment 2: NPR 5,000
Payment 3: NPR 2,600

Expected results:

- Total paid: NPR 17,600.
- Outstanding balance: NPR 0.
- Payment status: Fully Paid.

Next, create another month with partial payments.

Verify that the application correctly displays the outstanding amount.

Also test overpayments, deleted payments, edited payments, and unpaid utility expenses.

Compare every result against independently calculated expected values.

# 4. UI AND INTERACTION TESTING

Inspect every visible element.

Test:

- All sidebar navigation links.
- All buttons.
- All forms.
- All dropdowns.
- All date pickers.
- All modal windows.
- All confirmation dialogs.
- All search fields.
- All filters.
- Pagination.
- Sorting.
- Export buttons.
- Mobile navigation.
- Responsive tables.
- Notifications.
- Error messages.
- Empty states.

There must be no:

- Dead buttons.
- Broken links.
- Empty placeholder pages.
- Non-functional forms.
- Misleading success notifications.
- Broken icons.
- Console errors.
- Unhandled JavaScript exceptions.
- Horizontal overflow on mobile.

Check desktop, tablet, and mobile layouts.

# 5. AUTHENTICATION AND SECURITY TESTING

Inspect:

- Login.
- Logout.
- Registration if available.
- Password validation.
- Protected routes.
- Session handling.
- CSRF protection.
- SQL injection protection.
- XSS protection.
- Authorization.
- File upload validation.
- Sensitive information exposure.
- Database credentials.
- Debug mode in production.

Verify that anonymous visitors cannot access private financial records.

Verify that users cannot modify records they are not authorized to access.

Never expose passwords, tokens, or environment variables.

Do not perform destructive penetration testing against the live production website.

# 6. DATABASE INTEGRITY TESTING

Verify:

- All foreign keys.
- Database relationships.
- Unique constraints.
- Required columns.
- Decimal precision.
- Null handling.
- Duplicate records.
- Orphaned payments.
- Cascading deletion behavior.
- Transaction rollback.
- Database consistency.

Financial operations must be atomic.

Deleting a monthly record must never silently destroy unrelated payment history.

Implement safe deletion and archival strategies where necessary.

# 7. DATE AND CALENDAR TESTING

RentMate supports Bikram Sambat and Gregorian dates.

Verify:

- BS to AD conversion.
- AD to BS conversion.
- Date selection.
- Month filters.
- Year filters.
- Payment dates.
- Late-payment calculations.
- Month boundaries.
- Nepali New Year transitions.
- Historical date accuracy.

Do not rely on approximate calendar conversion.

Use a reliable date conversion implementation and test known dates.

# 8. AUTOMATED TESTING

Implement automated tests using suitable Laravel testing tools.

Use:

- PHPUnit or Pest.
- Laravel HTTP feature tests.
- Database tests.
- Model tests.
- Calculation tests.
- Browser automation using Playwright if appropriate.

Create test coverage for all major modules.

Include:

- Successful operations.
- Invalid inputs.
- Unauthorized requests.
- Boundary conditions.
- Duplicate requests.
- Failed database operations.

Use isolated test data.

Never run destructive test operations against the production database.

# 9. PERFORMANCE AND RELIABILITY

Inspect:

- Slow database queries.
- N+1 query problems.
- Unnecessary database requests.
- Large frontend assets.
- Broken pagination.
- Missing indexes.
- Error handling.
- Application logs.
- Database connection failures.

Ensure the application can handle increasing numbers of rental records and payment transactions.

# 10. BUG FIXING RULES

For every discovered issue:

1. Reproduce the issue.
2. Identify its root cause.
3. Fix the underlying problem.
4. Add a regression test.
5. Run the relevant test suite again.
6. Verify that existing features still work.

Do not hide errors with temporary workarounds.

Do not remove functionality just to make tests pass.

Do not replace real database functionality with hardcoded sample data.

Do not claim successful testing without actual test results.

# 11. DEPLOYMENT VERIFICATION

Inspect the Railway deployment configuration.

Verify:

- Production environment variables.
- Database connectivity.
- Database migrations.
- Asset compilation.
- Production caching.
- Application logs.
- Storage permissions.
- HTTPS.
- Backup configuration.

Prepare safe deployment instructions.

Do not modify production data or deploy potentially destructive migrations without explicit approval.

# 12. FINAL QUALITY REPORT

When finished, provide a detailed report containing:

1. Total pages inspected.
2. Total features tested.
3. Total test cases executed.
4. Passed tests.
5. Failed tests.
6. Bugs discovered.
7. Bugs fixed.
8. Remaining issues.
9. Security issues.
10. Database issues.
11. Performance improvements.
12. Automated test results.
13. Production deployment status.

Classify issues as:

- Critical
- High
- Medium
- Low

# FINAL ACCEPTANCE CRITERIA

RentMate should only be considered ready when:

- All core modules work.
- All financial calculations are correct.
- Payments are tracked accurately.
- Historical records are preserved.
- Database operations are reliable.
- Authentication and authorization work.
- All visible buttons and forms perform their intended actions.
- Reports match actual database values.
- Desktop and mobile layouts work.
- Automated tests pass.
- No critical or high-priority bugs remain.

Start with the audit, then proceed module by module.

Do not stop after generating a report. Continue fixing and testing the application until all achievable acceptance criteria are satisfied.

If any task requires production credentials, external service access, or my approval, clearly identify the blocker rather than pretending it has been completed.
