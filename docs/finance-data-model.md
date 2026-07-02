# Finance Data Model

## Purpose

This document defines the proper database structure for the Finance page so the current dummy data can be replaced with real records.

The page is for **personal monthly salary planning**, not business accounting.

The core user flow is:

1. A user tracks how much balance they had **before salary day** for a given month and year.
2. The user records that month's salary.
3. The user records that month's planned commitments such as rent, bills, WiFi, Netflix, loan, and other recurring obligations.
4. The page shows:
   - current month salary
   - current month commitments
   - salary balance left after bills
   - paid vs unpaid commitments
   - month-to-month comparison
   - year-aware chart history

This is a **period-based tracker**, so the database must treat **year and month together** as one finance period.

---

## Design Principles

- Every finance dataset belongs to a specific `user_id`.
- Month data must always include **year** as well.
- UI totals should be **derived from records**, not duplicated in storage.
- The system should support:
  - viewing past months
  - comparing months across different years
  - keeping recurring commitment templates
  - optionally keeping an audit trail of edits

---

## Existing Table

The project already has:

### `finance_page_accesses`

Purpose:
- Controls who can access the Finance page.

This table should remain separate from the actual finance data tables.

---

## Recommended New Tables

## 1. `finance_periods`

Purpose:
- Stores one finance period per user for one specific month and year.
- This is the main parent table for monthly finance tracking.

### Required columns

- `id`
- `user_id`
- `period_year`
- `period_month`
- `salary_received_on`
- `salary_amount`
- `carry_balance_before_salary`
- `remarks` nullable
- `created_at`
- `updated_at`

### Column notes

- `period_year`
  - integer, example: `2026`
- `period_month`
  - tiny integer, range `1..12`
- `salary_received_on`
  - actual salary date inside that period, example: `2026-03-07`
- `salary_amount`
  - decimal, positive value
- `carry_balance_before_salary`
  - decimal, zero or positive
  - this is the amount left from the previous month **before** the current salary is added

### Constraints

- unique index on: `user_id + period_year + period_month`
- check or validation:
  - `period_month` must be between `1` and `12`
  - `salary_amount >= 0`
  - `carry_balance_before_salary >= 0`

### Why this table exists

This table replaces the following dummy data fields:

- `monthStatuses[*].id`
- `monthStatuses[*].label`
- `monthStatuses[*].salary_date`
- `monthStatuses[*].salary`
- `monthStatuses[*].carry_balance`

### Derived values from this table

- current month salary
- balance before salary
- current balance before bills

Formula:

`current_balance_before_bills = carry_balance_before_salary + salary_amount`

---

## 2. `finance_commitment_categories`

Purpose:
- Stores reusable commitment types for a user.
- These are the selectable categories in the modal.

Examples:
- Rent
- Electric Bill
- Water Bill
- WiFi
- Netflix
- Car Loan
- Flexible Spending Buffer

### Required columns

- `id`
- `user_id`
- `name`
- `default_amount` nullable
- `color` nullable
- `icon` nullable
- `is_active`
- `sort_order` nullable
- `created_at`
- `updated_at`

### Constraints

- unique index on: `user_id + name`

### Notes

- `default_amount` is optional, but useful if the same bill amount usually repeats.
- `is_active` allows hiding old categories without deleting history.
- `color` and `icon` are optional presentation helpers if the UI wants to keep category styling.

### Why this table exists

This table replaces the hardcoded spending categories currently generated from:

- `groupedRecords`
- modal category options
- add/remove category actions

---

## 3. `finance_period_commitments`

Purpose:
- Stores the actual commitments for a specific user period.
- This is the table that powers the "Month Status" list and paid/unpaid tracking.

### Required columns

- `id`
- `finance_period_id`
- `finance_commitment_category_id` nullable
- `name_snapshot`
- `amount`
- `status`
- `paid_on` nullable
- `notes` nullable
- `created_at`
- `updated_at`

### Column notes

- `finance_period_id`
  - foreign key to `finance_periods`
- `finance_commitment_category_id`
  - nullable to allow one-off commitments or deleted categories while preserving history
- `name_snapshot`
  - stores the displayed name at the time of entry
  - recommended even if category relation exists
- `amount`
  - positive decimal value
  - use positive storage and treat the value as spending in logic
- `status`
  - string or enum
  - recommended values: `paid`, `unpaid`
- `paid_on`
  - actual date paid, nullable while unpaid

### Constraints

- check or validation:
  - `amount >= 0`
  - `status in ('paid', 'unpaid')`

### Why this table exists

This table replaces:

- `monthStatuses[*].commitments[*].category`
- `monthStatuses[*].commitments[*].amount`
- `monthStatuses[*].commitments[*].status`
- `monthStatuses[*].commitments[*].paid_on`

### Derived values from this table

- current month commitment total
- paid commitment total
- unpaid commitment total
- paid count
- unpaid count
- month comparison cards

Formula examples:

- `current_month_commitment = sum(amount for all commitments in selected period)`
- `salary_balance_left_after_bills = (carry_balance_before_salary + salary_amount) - current_month_commitment`
- `paid_total = sum(amount where status = 'paid')`
- `unpaid_total = sum(amount where status = 'unpaid')`

---

## 4. `finance_records` (optional but recommended)

Purpose:
- Stores an audit-style history of finance actions and user inputs.
- Useful if you want the modal actions to be traceable instead of only updating summary tables.

This is not strictly required for the Finance page to function, but it is the best structure if you want reliable edit history.

### Required columns

- `id`
- `user_id`
- `finance_period_id` nullable
- `record_type`
- `finance_commitment_category_id` nullable
- `recorded_on`
- `amount`
- `title` nullable
- `notes` nullable
- `created_at`
- `updated_at`

### Recommended `record_type` values

- `salary`
- `carry_balance`
- `commitment`

### Notes

- `amount` should stay positive here too.
- Sign meaning should come from `record_type`, not from storing negative numbers.

### Why this table exists

This table replaces the current dummy grouped history in:

- `recordGroups`
- modal record insertion behavior
- carry-balance update behavior

### Recommended usage

- When a user adds salary in the modal:
  - create or update the `finance_periods` row
  - optionally insert a `finance_records` row with `record_type = salary`
- When a user updates end-month balance:
  - update `finance_periods.carry_balance_before_salary`
  - optionally insert a `finance_records` row with `record_type = carry_balance`
- When a user adds a commitment:
  - create or update `finance_period_commitments`
  - optionally insert a `finance_records` row with `record_type = commitment`

---

## Relationship Summary

### `users`

- has many `finance_periods`
- has many `finance_commitment_categories`
- has many `finance_records`

### `finance_periods`

- belongs to `user`
- has many `finance_period_commitments`
- has many `finance_records`

### `finance_commitment_categories`

- belongs to `user`
- has many `finance_period_commitments`
- has many `finance_records`

### `finance_period_commitments`

- belongs to `finance_period`
- optionally belongs to `finance_commitment_category`

### `finance_records`

- belongs to `user`
- optionally belongs to `finance_period`
- optionally belongs to `finance_commitment_category`

---

## Year and Month Handling

This page must not identify periods by month name alone.

Correct examples:

- March 2026
- February 2027
- January 2028

Wrong examples:

- March
- February

### Required rule

Every period must be identified by:

- `period_year`
- `period_month`

Recommended display label:

- `"March 2026"`

Recommended sortable identifier:

- `"2026-03"`

### Why this matters

Without year support, these would collide:

- March 2026 salary
- March 2027 salary

The chart, previous-month comparison, and table history all need year-aware periods.

---

## What Should Be Stored vs Derived

## Store in database

- user access selection
- finance period year
- finance period month
- salary date
- salary amount
- carry-over balance before salary
- commitment categories
- each month's commitment rows
- paid/unpaid state
- paid date
- optional audit records

## Do not store as primary data

- chart arrays
- summary card text
- current month commitment total
- current balance
- salary balance left after bills
- paid/unpaid totals
- previous month comparison cards

These should be computed from the underlying tables.

---

## Mapping From Current Dummy Structures

## Dummy `chartPresets`

Should be generated from `finance_periods` plus `finance_period_commitments`.

For each period:

- `carry` = `carry_balance_before_salary`
- `income` = `salary_amount`
- `balance` = `carry_balance_before_salary + salary_amount`
- `spending` = sum of that period's commitments
- `labels` = month/year labels

## Dummy `monthStatuses`

Should come from:

- one `finance_periods` row
- many `finance_period_commitments` rows

## Dummy `recordGroups`

Should come from:

- `finance_records` if audit history is needed
- otherwise can be removed entirely and replaced with summaries/query results

---

## Recommended Minimal Production Schema

If the goal is the cleanest production setup, use these three new tables:

1. `finance_periods`
2. `finance_commitment_categories`
3. `finance_period_commitments`

If the goal also includes full audit/history, add:

4. `finance_records`

---

## Recommended Naming Notes

To keep the design understandable:

- use `period_year` and `period_month`
- do not use only `month`
- use `salary_amount`, not generic `value`
- use `carry_balance_before_salary`, not generic `balance`
- use `amount` as positive money values for commitments
- use `status` with clear values like `paid` and `unpaid`

---

## Implementation Recommendation

Best next implementation order:

1. Create `finance_periods`
2. Create `finance_commitment_categories`
3. Create `finance_period_commitments`
4. Update `FinanceController` to load real period data
5. Replace Blade dummy arrays with controller/view-model data
6. Add `finance_records` only if audit history is needed

---

## Final Recommendation

The most proper database structure for this Finance page is:

- one user-owned table for monthly finance periods with **year + month**
- one user-owned table for reusable commitment categories
- one child table for per-period commitments and paid status
- one optional audit table for modal action history

That structure is enough to support:

- monthly salary planning
- carry-over tracking
- recurring commitment tracking
- paid/unpaid month status
- previous month comparison
- year-aware charts and history
