# GitHub Copilot Instructions for My Tax Manager

## Project Overview
This is a **Yii2-based tax management system** for Sri Lankan businesses. Follow these instructions when assisting with code generation, refactoring, and documentation.

---

## 🎯 Core Principles

### 1. **Follow Yii2 Framework Standards**
- Use Yii2 naming conventions (e.g., `SalaryAdvance`, not `salary_advance` for class names)
- Utilize Yii2 built-in features: ActiveRecord, GridView, validators, behaviors
- Follow MVC pattern strictly: Models for business logic, Controllers for routing, Views for presentation
- Use Yii2 aliases (`@app`, `@webroot`, `@vendor`)
- Implement proper namespacing (`app\models`, `app\controllers`, `app\helpers`)

### 2. **PHP Coding Standards**
- Follow **PSR-12** coding style
- Use **type hints** for method parameters and return types
- Write **PHPDoc comments** for all classes, methods, and properties
- Use **strict types**: `declare(strict_types=1);` at the top of files
- Prefer **dependency injection** over static calls where possible
- Use **short array syntax**: `[]` instead of `array()`

### 3. **Database & MySQL Standards**
- Use **Yii2 migrations** for all database changes
- Follow MySQL naming conventions: `snake_case` for tables and columns
- Always include **indexes** for foreign keys and frequently queried columns
- Use **proper data types**: `DECIMAL` for money, `DATETIME` for timestamps
- Include `created_at` and `updated_at` with **TimestampBehavior**
- Add **foreign key constraints** with proper CASCADE/RESTRICT rules
- Use table prefix `mb_` for all tables

---

## 📝 Code Generation Guidelines

### Models
```php
<?php
declare(strict_types=1);

namespace app\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * Model description
 * 
 * @property int $id
 * @property string $name
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 * @property int $updated_by
 */
class ExampleModel extends BaseModel
{
    public static function tableName(): string
    {
        return '{{%example}}';
    }
    
    public function behaviors(): array
    {
        return [
            TimestampBehavior::class,
            BlameableBehavior::class,
        ];
    }
    
    public function rules(): array
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
        ];
    }
    
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
```

### Controllers
- Keep controllers **thin** - delegate business logic to models/services
- Use **access control** filters for authentication/authorization
- Return **JSON responses** for AJAX requests
- Handle **exceptions** gracefully with try-catch blocks
- Use **flash messages** for user feedback
- Extend `BaseWebController` for common functionality

### Views
- Use **Yii2 widgets**: GridView, DetailView, ActiveForm
- Follow **Bootstrap 4** styling conventions (project uses Bootstrap 4)
- Add **Font Awesome icons** for visual clarity
- Include **tooltips** on action buttons
- Use **consistent button classes**: `btn-sm` for grid actions
- Implement **confirmation dialogs** for destructive actions
- Use `BHtml` helper instead of `Html` for consistency

---

## 🧪 Unit Testing Requirements

### **ALWAYS write unit tests for:**
- ✅ New model methods and business logic
- ✅ Database queries and relationships
- ✅ Validation rules
- ✅ Helper functions and services
- ✅ Console commands
- ✅ Status workflows and calculations

### **ALWAYS use fixtures for database tests:**
- ✅ Use **ActiveFixture** for consistent, reusable test data
- ✅ Create **practical dummy data** with Sri Lankan context (realistic names, addresses, phone formats)
- ✅ Define **fixture dependencies** for related models (foreign keys)
- ✅ **Never manually create** test data in `_before()` method
- ✅ Use **meaningful fixture keys** like `john_doe`, `electricity_jan`, `invoice_2025_001`
- ✅ Include **edge cases** in fixtures (inactive, left, cancelled, foreign currency)
- ✅ Reference: See `tests/FIXTURES.md` for complete guide

### **Test Structure with Fixtures**
```php
<?php
namespace tests\unit\models;

use app\models\ExampleModel;
use Codeception\Test\Unit;
use tests\fixtures\ExampleFixture;
use tests\fixtures\RelatedFixture;

class ExampleModelTest extends Unit
{
    protected $tester;
    
    /**
     * Load fixtures before each test
     */
    public function _fixtures()
    {
        return [
            'examples' => [
                'class' => ExampleFixture::class,
            ],
            'related' => [
                'class' => RelatedFixture::class,
            ],
        ];
    }
    
    public function testValidation()
    {
        $model = new ExampleModel();
        verify($model->validate())->false();
        verify($model->hasErrors('name'))->true();
        
        $model->name = 'Test';
        verify($model->validate())->true();
    }
    
    public function testWithFixtureData()
    {
        // Access fixture data
        $example = $this->tester->grabFixture('examples', 'fixture_key');
        
        verify($example)->notNull();
        verify($example->name)->equals('Expected Name');
        verify($example->id)->notNull();
    }
    
    public function testRelationship()
    {
        $example = $this->tester->grabFixture('examples', 'fixture_key');
        
        verify($example->relatedModel)->notNull();
        verify($example->relatedModel)->instanceOf(RelatedModel::class);
        verify($example->relatedModel->name)->equals('Related Name');
    }
}
```

### **Fixture Creation Pattern**

**Step 1: Create Fixture Class** (`tests/fixtures/ExampleFixture.php`)
```php
<?php
namespace tests\fixtures;

use yii\test\ActiveFixture;

class ExampleFixture extends ActiveFixture
{
    public $modelClass = 'app\models\Example';
    public $dataFile = '@tests/_data/example.php';
    
    // Optional: Define dependencies
    public $depends = [
        'tests\fixtures\RelatedFixture',
    ];
}
```

**Step 2: Create Data File** (`tests/_data/example.php`)
```php
<?php
return [
    'john_doe' => [  // Meaningful key
        'first_name' => 'John',
        'last_name' => 'Doe',
        'nic' => '199012345678',  // Valid Sri Lankan NIC
        'phone' => '0771234567',  // Valid Sri Lankan phone
        'email' => 'john.doe@company.com',
        'position' => 'Software Engineer',
        'department' => 'IT',
        'status' => 'active',
        'created_at' => 1700000000,
        'updated_at' => 1700000000,
        'created_by' => 1,
        'updated_by' => 1,
    ],
    'inactive_user' => [  // Edge case
        'first_name' => 'Inactive',
        'status' => 'inactive',
        // ...
    ],
];
```

### **Testing Best Practices**
- Use **Codeception 5.x** syntax: `verify($value)->true()` (not `$this->assertTrue()`)
- Use **fixtures** for all database-related tests
- Test **both success and failure scenarios**
- Test **edge cases** and boundary conditions (use edge case fixtures)
- Use **descriptive test method names**: `testCalculateTotalWithDiscount()`
- **Never manually create** data in `_before()` - use fixtures instead
- **Never manually delete** data in `_after()` - fixtures auto-cleanup
- Test **relationships** using fixture data: `$this->tester->grabFixture('employees', 'john_doe')`
- Aim for **70%+ code coverage** on new features
- Run tests **before committing**: `docker exec mb-php ./vendor/bin/codecept run unit`

### **Practical Dummy Data Guidelines**
1. ✅ **Sri Lankan Context**: Use realistic Sri Lankan names (Fernando, Perera, Silva), addresses (Colombo 03, Galle Road), phone formats (0112345678)
2. ✅ **Varied Data**: Different departments (IT, Finance, Marketing), positions, statuses
3. ✅ **Edge Cases**: Include inactive/left employees, cancelled invoices, foreign currencies
4. ✅ **Meaningful Keys**: Use descriptive keys like `john_doe`, `electricity_jan`, `invoice_2025_001`
5. ✅ **Realistic Amounts**: Use practical Sri Lankan Rupee amounts (25000, 150000, etc.)
6. ✅ **Multiple Scenarios**: Cover LKR/USD currencies, paid/pending statuses, recurring/one-time flags

### **Codeception 5.x Verify Methods**
```php
verify($value)->true();
verify($value)->false();
verify($value)->null();
verify($value)->notNull();
verify($value)->empty();
verify($value)->notEmpty();
verify($value)->equals($expected);
verify($value)->notEquals($unexpected);
verify($value)->greaterThan($min);
verify($value)->lessThan($max);
verify($value)->instanceOf(ClassName::class);
verify($value)->arrayHasKey($key);
verify($string)->stringContainsString($substring);
```

---

## 🗃️ Test Fixtures

### **Available Fixtures**

The project has comprehensive fixtures with realistic Sri Lankan business data:

**Core Fixtures:**
- **EmployeeFixture** - 4 employees (IT, Finance, Marketing, HR - includes left employee)
- **CustomerFixture** - 4 customers (active & inactive)
- **VendorFixture** - 4 vendors (CEB, Dialog, Office Supplies, Property)
- **ExpenseCategoryFixture** - 5 categories (Utilities, Rent, Office Supplies, Telecom, Salaries)
- **ExpenseFixture** - 5 expenses (recurring & one-time, LKR & USD)
- **InvoiceFixture** - 6 invoices (paid, pending, overdue, cancelled)
- **EmployeeSalaryAdvanceFixture** - 5 salary advances (multiple employees & months)

### **Fixture Keys Reference**

**Employees:**
- `john_doe` - Software Engineer, IT, active
- `jane_smith` - Senior Accountant, Finance, active
- `robert_brown` - Marketing Manager, Marketing, active
- `sarah_wilson` - HR Manager, left (edge case)

**Customers:**
- `tech_solutions` - Tech Solutions (Pvt) Ltd
- `retail_mart` - Retail Mart Lanka
- `global_exports` - Global Exports Ltd
- `inactive_corp` - Inactive (edge case)

**Vendors:**
- `office_supplies_co` - Office Supplies Co.
- `electricity_board` - Ceylon Electricity Board
- `telecom_provider` - Dialog Axiata PLC
- `rent_landlord` - ABC Properties

**Expenses:**
- `electricity_jan` - Recurring utility (LKR)
- `rent_jan` - Recurring rent (LKR)
- `office_supplies_oct` - One-time (LKR)
- `telecom_nov` - Recurring telecom (LKR)
- `foreign_expense` - Software license (USD)

**Salary Advances:**
- `john_jan_advance` - John's January advance
- `john_feb_advance` - John's February advance
- `jane_march_advance` - Jane's March advance
- `robert_nov_advance` - Robert's November advance
- `john_nov_advance` - John's November advance

### **Using Fixtures in Tests**

**Basic Usage:**
```php
public function _fixtures()
{
    return [
        'employees' => ['class' => EmployeeFixture::class],
        'advances' => ['class' => EmployeeSalaryAdvanceFixture::class],
    ];
}

public function testExample()
{
    // Get single record
    $employee = $this->tester->grabFixture('employees', 'john_doe');
    verify($employee->first_name)->equals('John');
    
    // Test relationship
    $advance = $this->tester->grabFixture('advances', 'john_jan_advance');
    verify($advance->employee)->notNull();
    verify($advance->employee->first_name)->equals('John');
}
```

**Multiple Fixtures with Dependencies:**
```php
public function _fixtures()
{
    return [
        'categories' => ['class' => ExpenseCategoryFixture::class],
        'vendors' => ['class' => VendorFixture::class],
        'expenses' => ['class' => ExpenseFixture::class], // Depends on above
    ];
}

public function testExpenseRelationships()
{
    $expense = $this->tester->grabFixture('expenses', 'electricity_jan');
    
    verify($expense->expenseCategory)->instanceOf(ExpenseCategory::class);
    verify($expense->expenseCategory->name)->equals('Utilities');
    verify($expense->vendor->name)->equals('Ceylon Electricity Board');
}
```

**Testing Business Logic with Fixtures:**
```php
public function testMonthlyTotal()
{
    $employee = $this->tester->grabFixture('employees', 'john_doe');
    
    // john_doe has 50k in January from fixtures
    $total = EmployeeSalaryAdvance::getMonthlyTotal($employee->id, 2025, 1);
    verify($total)->equals(50000.00);
}
```

### **Fixture Best Practices**

✅ **DO:**
- Use fixtures for all database tests
- Create fixtures for new models immediately
- Use realistic Sri Lankan data
- Include edge cases (inactive, cancelled, foreign currency)
- Use meaningful keys (`john_doe`, not `user1`)
- Define dependencies in correct order
- Test relationships using fixture data

❌ **DON'T:**
- Manually create test data in `_before()`
- Manually delete test data in `_after()`
- Specify `id` in fixture data (let database auto-generate)
- Use generic names (`test_user`, `record1`)
- Modify fixture data directly in tests
- Create data without fixtures for database tests

**For complete guide, see:** `tests/FIXTURES.md`

---

## 📚 Documentation Requirements

### **When to Update README.md**
✅ **DO update README for:**
- New features added
- New database tables/migrations
- New configuration options
- New console commands
- Changes to architecture or structure
- New model methods that users interact with

❌ **DON'T update README for:**
- Bug fixes (unless they change behavior significantly)
- Minor refactoring
- UI styling tweaks
- Code cleanup
- Performance optimizations
- Internal improvements

### **README Update Format**
When adding a new feature to README, follow this structure:

```markdown
## 🆕 Feature Name

Brief description of what it does and why it's useful.

**Key Features:**
- Feature 1
- Feature 2
- Feature 3

**Access:** Where to find it in the UI or via commands

**Database:** Table name and key fields if relevant

**Model Methods:**
```php
ModelName::methodName($params);  // Brief description
```

**Usage Example:**
1. Step 1
2. Step 2
```

### **Documentation Style**
- Use **emojis** for section headers (🔧 ⚙️ 💰 📊 ✅ 🚀 💻 🐳)
- Include **tables** for structured data (database schema, services)
- Add **code examples** only when necessary for clarity
- Use **checkmarks** for completed items
- Keep it **concise** - avoid redundancy and excessive detail
- Focus on **what** and **how**, not internal implementation details
- Use **bold** for emphasis, not excessive formatting

---

## 🔒 Security Practices

- **Always validate** user input with Yii2 validators
- Use **parameterized queries** (Yii2 ActiveRecord does this by default)
- Implement **access control** filters in controllers
- **Sanitize output** in views: `Html::encode()` or `BHtml::encode()`
- Store **sensitive data** in `.env` file, never in code
- Use **CSRF protection** (enabled by default in Yii2)
- Implement **audit trails** for financial operations (BlameableBehavior, TimestampBehavior)
- Use **foreign key constraints** with CASCADE on delete where appropriate
- Validate **file uploads**: type, size, and sanitize filenames
- Use **authentication** filters: `'access' => ['class' => AccessControl::class]`

---

## 🚀 Performance Optimization

- Use **eager loading** to avoid N+1 queries: `->with(['relation'])`
- Implement **caching** for frequently accessed data
- Add **database indexes** for foreign keys and frequently queried columns
- Use **batch operations** for bulk inserts/updates
- Avoid **loading unnecessary data** - use `select()` to get only needed columns
- Use **asArray()** when you don't need model instances
- Implement **pagination** for large datasets
- Cache **static configuration** data from database

---

## 🎨 UI/UX Consistency

### Button Styling in GridView
```php
// Standard action buttons
[
    'view' => 'btn btn-sm btn-info',      // View details
    'update' => 'btn btn-sm btn-primary', // Edit/Update
    'delete' => 'btn btn-sm btn-danger',  // Delete
    'custom' => 'btn btn-sm btn-warning', // Warning action (payroll)
    'success' => 'btn btn-sm btn-success', // Success action (attendance)
    'dark' => 'btn btn-sm btn-dark',      // Dark action (advance)
]
```

### Badge Styling
```php
// Status badges
'badge badge-success'  // Green - completed, active
'badge badge-warning'  // Yellow - pending, partial
'badge badge-danger'   // Red - cancelled, overdue
'badge badge-info'     // Blue - in progress
'badge badge-pill'     // Rounded pill shape
```

### Icons
- Use **Font Awesome 5** icons
- Common icons: `fa-eye` (view), `fa-pencil-alt` (edit), `fa-trash` (delete)
- Add icons in buttons: `<i class="fas fa-icon"></i>`
- Include **tooltips** on icon-only buttons

---

## 📦 Project Structure

```
php/
├── base/                      # Base classes
│   ├── BaseModel.php         # Extended ActiveRecord
│   ├── BaseWebController.php # Base controller
│   └── BaseMigration.php     # Migration helpers
├── models/                    # ActiveRecord models
├── controllers/              # Controllers
├── views/                    # View templates
├── components/               # Services & components
├── helpers/                  # Helper classes
│   ├── ConfigHelper.php     # System config
│   └── Params.php           # Parameter helper
├── widgets/                  # Reusable widgets
├── commands/                 # Console commands
├── migrations/               # Database migrations
├── tests/                    # Unit tests
│   └── unit/
│       ├── models/          # Model tests
│       └── components/      # Service tests
└── web/
    └── uploads/             # User uploads
```

---

## 🛠️ Common Yii2 Patterns

### Query Optimization
```php
// Good - eager loading
$employees = Employee::find()
    ->with(['payrolls', 'attendances'])
    ->where(['status' => Employee::STATUS_ACTIVE])
    ->all();

// Bad - N+1 problem
$employees = Employee::find()->all();
foreach ($employees as $employee) {
    $payrolls = $employee->payrolls; // N queries
}
```

### Transaction Handling
```php
$transaction = Yii::$app->db->beginTransaction();
try {
    // Multiple operations
    $model1->save();
    $model2->save();
    $transaction->commit();
} catch (\Exception $e) {
    $transaction->rollBack();
    Yii::error($e->getMessage(), __METHOD__);
    throw $e;
}
```

### Flash Messages
```php
Yii::$app->session->setFlash('success', 'Operation completed successfully');
Yii::$app->session->setFlash('error', 'An error occurred: ' . $model->getErrorSummary(false)[0]);
Yii::$app->session->setFlash('warning', 'Please review the data');
Yii::$app->session->setFlash('info', 'Information notice');
```

### Using Helpers
```php
use app\helpers\ConfigHelper;
use app\helpers\Params;

// Get config from database
$businessName = ConfigHelper::getBusinessName();
$banking = ConfigHelper::getBankingDetails();

// Get params with fallback
$value = Params::get('key', 'default');
```

---

## 📋 Checklist for New Features

- [ ] **Model** created extending `BaseModel` with proper validation rules
- [ ] **Migration** created with indexes and foreign keys
- [ ] **Controller** actions implemented with access control
- [ ] **Views** created with consistent styling and widgets
- [ ] **Unit tests** written and passing (70%+ coverage)
- [ ] **README.md** updated with feature documentation
- [ ] **Security** considerations addressed (validation, sanitization)
- [ ] **Performance** optimized (indexes, eager loading, caching)
- [ ] **UI/UX** follows project standards (buttons, badges, icons)
- [ ] **Code** follows PSR-12 and Yii2 conventions
- [ ] **Audit trail** implemented (BlameableBehavior, TimestampBehavior)
- [ ] **Error handling** implemented with try-catch and logging

---

## 🔄 Database Migration Template

```php
<?php

use yii\db\Migration;

/**
 * Creates table for {{%example}}
 */
class m251118_000001_create_example_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%example}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'amount' => $this->decimal(10, 2)->notNull()->defaultValue(0),
            'status' => $this->string(20)->notNull()->defaultValue('pending'),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'created_by' => $this->integer()->notNull(),
            'updated_by' => $this->integer()->notNull(),
        ]);

        // Add indexes
        $this->createIndex('idx-example-status', '{{%example}}', 'status');
        $this->createIndex('idx-example-created_at', '{{%example}}', 'created_at');

        // Add foreign keys if needed
        // $this->addForeignKey(
        //     'fk-example-created_by',
        //     '{{%example}}',
        //     'created_by',
        //     '{{%user}}',
        //     'id',
        //     'RESTRICT',
        //     'CASCADE'
        // );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%example}}');
    }
}
```

---

## 💻 Git Commit Messages

Follow conventional commit format:

```
feat: Add salary advance monthly overview feature
fix: Resolve attendance calculation bug for half-day entries
docs: Update README with salary advance documentation
test: Add unit tests for EmployeeSalaryAdvance model
refactor: Simplify payroll calculation logic
style: Update button styling for consistency in employee grid
perf: Add index on employee_id for faster queries
chore: Update dependencies and clear cache
```

**Prefixes:**
- `feat:` - New feature
- `fix:` - Bug fix
- `docs:` - Documentation only
- `test:` - Adding tests
- `refactor:` - Code refactoring
- `style:` - Formatting, styling
- `perf:` - Performance improvement
- `chore:` - Maintenance tasks

---

## 🧰 Development Workflow

### Before Committing
1. ✅ Run tests: `docker exec mb-php ./vendor/bin/codecept run unit`
2. ✅ Check for errors in IDE
3. ✅ Test feature in browser
4. ✅ Verify migrations work: `docker exec mb-php ./yii migrate/up --interactive=0`
5. ✅ Update README.md if adding new feature
6. ✅ Clear cache: `docker exec mb-php php yii cache/flush-all`

### Console Commands
```bash
# Run migrations
docker exec mb-php ./yii migrate/up --interactive=0

# Clear cache
docker exec mb-php php yii cache/flush-all

# Run unit tests
docker exec mb-php ./vendor/bin/codecept run unit

# Run specific test
docker exec mb-php ./vendor/bin/codecept run unit models/ExampleModelTest

# Generate coverage report
docker exec mb-php ./vendor/bin/codecept run unit --coverage --coverage-html
```

---

## 🎓 Project-Specific Conventions

### Fiscal Year
- Sri Lankan tax year: **April 1 to March 31**
- Use date format: `Y-m-d` (YYYY-MM-DD)

### Currency
- Default currency: **LKR (Sri Lankan Rupee)**
- Use `DECIMAL(10,2)` for money fields
- Format in views: `Yii::$app->formatter->asCurrency($amount)`

### Table Prefix
- All tables use prefix: `mb_`
- Reference in migrations: `{{%table_name}}`

### Authentication
- Role for authenticated users: `@`
- Access control in controllers using `AccessControl` filter

### Base Classes
- Models extend: `BaseModel` (includes TimestampBehavior, BlameableBehavior)
- Controllers extend: `BaseWebController`
- Migrations extend: `BaseMigration`

---

## 📖 Key Resources

- **Yii2 Guide**: https://www.yiiframework.com/doc/guide/2.0/en
- **Yii2 API**: https://www.yiiframework.com/doc/api/2.0
- **Codeception**: https://codeception.com/docs
- **PSR-12**: https://www.php-fig.org/psr/psr-12/
- **Bootstrap 4**: https://getbootstrap.com/docs/4.6/
- **Font Awesome**: https://fontawesome.com/icons

---

## ⚠️ Important Notes

### Docker Environment
- **Container name**: `mb-php`, `mb-mariadb`, `mb-nginx`, `mb-redis`
- **Database**: MariaDB 10.2, database name `mybs`
- **Ports**: 80 (nginx), 3307 (mariadb), 8080 (phpmyadmin)
- All commands run inside containers: `docker exec mb-php <command>`

### File Locations
- **Uploads**: `web/uploads/` (bank statements, receipts)
- **Logs**: `runtime/logs/app.log`
- **Cache**: `runtime/cache/`
- **Tests**: `tests/unit/`

### Health Check Features
- **Expense Health Check**: Detects missing recurring expenses (pattern: 2-3+ consecutive months)
- **Paysheet Health Check**: Auto-generates missing monthly paysheets
- Both run via console commands and cron jobs

---

**Remember**: Quality over speed. Write clean, tested, well-documented code that follows Yii2 and project conventions. Always think about maintainability, security, and performance.

