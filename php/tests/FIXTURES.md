# Test Fixtures Guide

## Overview

This project uses **Yii2 ActiveFixture** for database testing. Fixtures provide consistent, reusable test data that is automatically loaded before each test and cleaned up afterward.

## Benefits of Using Fixtures

✅ **Consistency**: Same test data across all test runs  
✅ **Speed**: Pre-defined data loads faster than creating manually  
✅ **Maintainability**: Update test data in one place  
✅ **Relationships**: Automatically handles foreign key dependencies  
✅ **Isolation**: Each test gets fresh data, no interference between tests  

---

## Fixture Structure

```
php/tests/
├── fixtures/              # Fixture class definitions
│   ├── EmployeeFixture.php
│   ├── ExpenseFixture.php
│   ├── InvoiceFixture.php
│   └── ...
└── _data/                 # Fixture data files
    ├── employee.php
    ├── expense.php
    ├── invoice.php
    └── ...
```

---

## Creating a New Fixture

### Step 1: Create Fixture Class

```php
<?php
// tests/fixtures/EmployeeFixture.php

namespace tests\fixtures;

use yii\test\ActiveFixture;

class EmployeeFixture extends ActiveFixture
{
    public $modelClass = 'app\models\Employee';
    public $dataFile = '@tests/_data/employee.php';
    
    // Optional: Specify dependencies
    public $depends = [
        // 'tests\fixtures\DepartmentFixture',
    ];
}
```

### Step 2: Create Data File

```php
<?php
// tests/_data/employee.php

return [
    'john_doe' => [  // Unique key for referencing in tests
        'first_name' => 'John',
        'last_name' => 'Doe',
        'nic' => '199012345678',
        'phone' => '0771234567',
        'email' => 'john.doe@company.com',
        'position' => 'Software Engineer',
        'department' => 'IT',
        'hire_date' => '2023-01-15',
        'status' => 'active',
        'created_at' => 1700000000,
        'updated_at' => 1700000000,
        'created_by' => 1,
        'updated_by' => 1,
    ],
    'jane_smith' => [
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'nic' => '198512345678',
        'phone' => '0772345678',
        // ... more fields
    ],
];
```

---

## Using Fixtures in Tests

### Basic Usage

```php
<?php
namespace tests\unit\models;

use app\models\Employee;
use Codeception\Test\Unit;
use tests\fixtures\EmployeeFixture;

class EmployeeTest extends Unit
{
    protected $tester;

    /**
     * Load fixtures before each test
     */
    public function _fixtures()
    {
        return [
            'employees' => [
                'class' => EmployeeFixture::class,
            ],
        ];
    }

    /**
     * Access fixture data in tests
     */
    public function testEmployeeFromFixture()
    {
        // Grab fixture by key
        $employee = $this->tester->grabFixture('employees', 'john_doe');
        
        verify($employee)->notNull();
        verify($employee->first_name)->equals('John');
        verify($employee->id)->notNull(); // Auto-generated ID
    }
}
```

### Multiple Fixtures

```php
public function _fixtures()
{
    return [
        'employees' => [
            'class' => EmployeeFixture::class,
        ],
        'advances' => [
            'class' => EmployeeSalaryAdvanceFixture::class,
        ],
        'categories' => [
            'class' => ExpenseCategoryFixture::class,
        ],
    ];
}
```

### Accessing Fixture Data

```php
// Get single record
$employee = $this->tester->grabFixture('employees', 'john_doe');

// Get all records
$allEmployees = $this->tester->grabFixture('employees');

// Get specific field value
$email = $this->tester->grabFixture('employees', 'john_doe')['email'];
```

---

## Fixture Dependencies

When a model has foreign key relationships, define dependencies:

```php
<?php
namespace tests\fixtures;

use yii\test\ActiveFixture;

class ExpenseFixture extends ActiveFixture
{
    public $modelClass = 'app\models\Expense';
    public $dataFile = '@tests/_data/expense.php';
    
    // Load these fixtures first
    public $depends = [
        'tests\fixtures\ExpenseCategoryFixture',
        'tests\fixtures\VendorFixture',
    ];
}
```

Then in data file, use the IDs from dependent fixtures:

```php
<?php
// tests/_data/expense.php

return [
    'electricity_jan' => [
        'expense_category_id' => 1, // References id from expense_category.php
        'vendor_id' => 2,           // References id from vendor.php
        'expense_date' => '2025-01-15',
        'title' => 'Monthly Electricity Bill',
        'amount' => 25000.00,
        // ...
    ],
];
```

---

## Practical Dummy Data Guidelines

### 1. Use Realistic Sri Lankan Data

```php
// Good: Sri Lankan names and addresses
'business_name' => 'Tech Solutions (Pvt) Ltd',
'contact_person' => 'Michael Fernando',
'address' => '123 Galle Road, Colombo 03',
'phone' => '0112345678',

// Bad: Generic/unrealistic data
'business_name' => 'Test Company',
'contact_person' => 'Test Person',
'address' => '123 Main St',
```

### 2. Use Varied Data

```php
// Different employee types
'john_doe' => ['position' => 'Software Engineer', 'department' => 'IT'],
'jane_smith' => ['position' => 'Senior Accountant', 'department' => 'Finance'],
'robert_brown' => ['position' => 'Marketing Manager', 'department' => 'Marketing'],
```

### 3. Include Edge Cases

```php
// Active employee
'active_employee' => ['status' => 'active', 'left_date' => null],

// Left employee
'former_employee' => ['status' => 'left', 'left_date' => '2025-08-31'],

// Different NIC formats
'old_nic' => ['nic' => '912345678V'],   // Old format
'new_nic' => ['nic' => '199212345678'], // New format
```

### 4. Cover Different Scenarios

```php
// Different currencies
'lkr_expense' => ['currency_code' => 'LKR', 'exchange_rate' => 1.00],
'usd_expense' => ['currency_code' => 'USD', 'exchange_rate' => 330.00],

// Different statuses
'paid_invoice' => ['status' => 'paid', 'payment_date' => '2025-02-10'],
'pending_invoice' => ['status' => 'pending', 'payment_date' => null],
'overdue_invoice' => ['status' => 'overdue', 'payment_date' => null],
```

### 5. Use Meaningful Names

```php
// Good: Descriptive keys
'john_jan_advance' => [...],  // John's January advance
'rent_jan' => [...],          // Rent expense for January
'invoice_2025_001' => [...],  // Invoice number 2025-001

// Bad: Generic keys
'record1' => [...],
'test_data' => [...],
```

---

## Available Fixtures

### Core Fixtures

- **EmployeeFixture**: 4 employees (active & left)
- **CustomerFixture**: 4 customers (active & inactive)
- **VendorFixture**: 4 vendors (utilities, rent, telecom, etc.)
- **ExpenseCategoryFixture**: 5 categories
- **ExpenseFixture**: 5 expenses (recurring & one-time, LKR & USD)
- **InvoiceFixture**: 6 invoices (various statuses)
- **EmployeeSalaryAdvanceFixture**: 5 salary advances

### Fixture Keys Reference

#### Employees
- `john_doe` - Software Engineer (IT)
- `jane_smith` - Senior Accountant (Finance)
- `robert_brown` - Marketing Manager (Marketing)
- `sarah_wilson` - HR Manager (left)

#### Customers
- `tech_solutions` - Active customer
- `retail_mart` - Active customer
- `global_exports` - Active customer
- `inactive_corp` - Inactive customer

#### Vendors
- `office_supplies_co` - Office supplies
- `electricity_board` - CEB
- `telecom_provider` - Dialog
- `rent_landlord` - Property rental

#### Expense Categories
- `utilities` - Electricity, water
- `rent` - Office rent
- `office_supplies` - Stationery
- `telecommunications` - Phone, internet
- `salaries` - Employee salaries

---

## Testing Best Practices

### 1. Use Fixtures for Database Tests

```php
// ✅ Good: Use fixtures
public function testEmployeeRelationship()
{
    $advance = $this->tester->grabFixture('advances', 'john_jan_advance');
    verify($advance->employee)->notNull();
}

// ❌ Bad: Manually create data
public function testEmployeeRelationship()
{
    $employee = new Employee();
    $employee->detachBehaviors();
    // ... lots of setup code
}
```

### 2. Don't Modify Fixture Data Directly

```php
// ✅ Good: Create new record if you need to modify
public function testUpdateAmount()
{
    $expense = new Expense();
    $expense->amount = 50000;
    // ... test update logic
}

// ❌ Bad: Modify fixture data
public function testUpdateAmount()
{
    $expense = $this->tester->grabFixture('expenses', 'rent_jan');
    $expense->amount = 50000; // Might affect other tests
}
```

### 3. Test Multiple Scenarios

```php
public function testStatusValidation()
{
    // Test valid statuses
    foreach (['pending', 'paid', 'cancelled', 'overdue'] as $status) {
        $model = new Invoice();
        $model->status = $status;
        verify($model->validate(['status']))->true();
    }
    
    // Test invalid status
    $model->status = 'invalid';
    verify($model->validate(['status']))->false();
}
```

### 4. Test Relationships

```php
public function testExpenseHasCategory()
{
    $expense = $this->tester->grabFixture('expenses', 'electricity_jan');
    
    verify($expense->expenseCategory)->notNull();
    verify($expense->expenseCategory->name)->equals('Utilities');
}
```

### 5. Test Business Logic

```php
public function testMonthlyTotal()
{
    $employee = $this->tester->grabFixture('employees', 'john_doe');
    
    // john_doe has 50k in January from fixtures
    $januaryTotal = EmployeeSalaryAdvance::getMonthlyTotal(
        $employee->id, 
        2025, 
        1
    );
    
    verify($januaryTotal)->equals(50000.00);
}
```

---

## Running Tests with Fixtures

```bash
# Run all unit tests
docker exec mb-php ./vendor/bin/codecept run unit

# Run specific test file
docker exec mb-php ./vendor/bin/codecept run unit models/EmployeeTest

# Run with verbose output
docker exec mb-php ./vendor/bin/codecept run unit --verbose

# Rebuild fixtures (if structure changes)
docker exec mb-php ./vendor/bin/codecept clean
docker exec mb-php ./vendor/bin/codecept build
```

---

## Troubleshooting

### Problem: Foreign Key Constraint Error

**Solution**: Ensure dependencies are defined in correct order

```php
public $depends = [
    'tests\fixtures\ExpenseCategoryFixture',  // Load first
    'tests\fixtures\VendorFixture',           // Load second
];
```

### Problem: Fixture Data Not Loading

**Solution**: Rebuild Codeception

```bash
docker exec mb-php ./vendor/bin/codecept build
```

### Problem: Auto-increment ID Conflicts

**Solution**: Don't specify `id` in fixture data - let database auto-generate

```php
// ✅ Good
'john_doe' => [
    'first_name' => 'John',
    // id will be auto-generated
],

// ❌ Bad
'john_doe' => [
    'id' => 1,  // Can cause conflicts
    'first_name' => 'John',
],
```

---

## Extending Fixtures

### Adding More Test Data

Simply add more entries to the data file:

```php
return [
    // ...existing data...
    
    'new_employee' => [
        'first_name' => 'New',
        'last_name' => 'Employee',
        // ...
    ],
];
```

### Creating Custom Fixture Methods

```php
class EmployeeFixture extends ActiveFixture
{
    // ...existing code...
    
    /**
     * Get active employees only
     */
    public function getActiveEmployees()
    {
        return array_filter($this->data, function($employee) {
            return $employee['status'] === 'active';
        });
    }
}
```

---

**For more information, see:**
- [Yii2 Testing Guide](https://www.yiiframework.com/doc/guide/2.0/en/test-fixtures)
- [Codeception Documentation](https://codeception.com/docs/modules/Yii2)

