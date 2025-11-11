<?php

namespace app\commands;

use app\models\BankAccount;
use app\models\CapitalAsset;
use app\models\Customer;
use app\models\CustomerEmail;
use app\models\Employee;
use app\models\EmployeePayrollDetails;
use app\models\Expense;
use app\models\ExpenseCategory;
use app\models\FinancialTransaction;
use app\models\Invoice;
use app\models\InvoiceItem;
use app\models\PaymentTerm;
use app\models\Paysheet;
use app\models\TaxConfig;
use app\models\TaxPayment;
use app\models\TaxRecord;
use app\models\Vendor;
use Faker\Factory;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\Exception;

/**
 * Data seeder for development environment
 *
 * Usage:
 * php yii seed              - Seed all data
 * php yii seed/customers    - Seed only customers
 * php yii seed/invoices     - Seed only invoices
 * php yii seed/expenses     - Seed only expenses
 * php yii seed/employees    - Seed only employees
 * php yii seed/clear        - Clear all seeded data
 */
class SeedController extends Controller
{
    private $faker;
    private $userId = 1; // Admin user ID

    public function init()
    {
        parent::init();

        // Check if running in development mode
        if (YII_ENV_PROD) {
            $this->stdout("⚠️  Warning: This command should only be run in development environment!\n");
            $this->stdout("Set YII_ENV to 'dev' in your environment variables.\n");
            exit(1);
        }

        $this->faker = Factory::create();

        // Verify admin user exists
        $adminUser = \app\models\User::findOne(1);
        if (!$adminUser) {
            $this->stderr("❌ Error: Admin user not found. Please run migrations first.\n", \yii\helpers\Console::FG_RED);
            exit(1);
        }
    }

    /**
     * Seeds all data
     */
    public function actionIndex()
    {
        $this->stdout("🌱 Starting data seeding for development environment...\n\n");

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->actionCategories();
            $this->actionPaymentTerms();
            $this->actionBankAccounts();
            $this->actionCustomers();
            $this->actionVendors();
            $this->actionEmployees();
            $this->actionInvoices();
            $this->actionExpenses();
            $this->actionTaxRecords();
            $this->actionCapitalAssets();

            $transaction->commit();
            $this->stdout("\n✅ All data seeded successfully!\n", \yii\helpers\Console::FG_GREEN);
            $this->showSummary();

            return ExitCode::OK;
        } catch (\Exception $e) {
            $transaction->rollBack();
            $this->stderr("❌ Error: " . $e->getMessage() . "\n", \yii\helpers\Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Seed expense categories
     */
    public function actionCategories()
    {
        $this->stdout("📁 Seeding expense categories...\n");

        $categories = [
            'Office Supplies' => 'Pens, paper, printer supplies, etc.',
            'Software & Subscriptions' => 'Software licenses, SaaS subscriptions',
            'Utilities' => 'Electricity, water, internet, phone',
            'Rent & Lease' => 'Office rent, equipment leasing',
            'Travel & Transport' => 'Business travel, fuel, vehicle maintenance',
            'Marketing & Advertising' => 'Online ads, print materials, campaigns',
            'Professional Services' => 'Legal, accounting, consulting fees',
            'Insurance' => 'Business insurance, liability insurance',
            'Maintenance & Repairs' => 'Equipment repairs, building maintenance',
            'Employee Benefits' => 'Health insurance, meal allowances',
            'Training & Development' => 'Courses, workshops, certifications',
            'Telecommunications' => 'Phone bills, video conferencing',
        ];

        foreach ($categories as $name => $description) {
            $category = new ExpenseCategory();
            $this->saveModel($category, [
                'name' => $name,
                'description' => $description,
            ]);
        }

        $this->stdout("   ✓ Created " . count($categories) . " categories\n", \yii\helpers\Console::FG_GREEN);
    }

    /**
     * Seed payment terms
     */
    public function actionPaymentTerms()
    {
        $this->stdout("💳 Seeding payment terms...\n");

        $terms = [
            ['name' => 'Net 15', 'days' => 15, 'description' => 'Payment due in 15 days'],
            ['name' => 'Net 30', 'days' => 30, 'description' => 'Payment due in 30 days'],
            ['name' => 'Net 45', 'days' => 45, 'description' => 'Payment due in 45 days'],
            ['name' => 'Net 60', 'days' => 60, 'description' => 'Payment due in 60 days'],
            ['name' => 'Due on Receipt', 'days' => 0, 'description' => 'Payment due immediately'],
            ['name' => 'Net 90', 'days' => 90, 'description' => 'Payment due in 90 days'],
        ];

        foreach ($terms as $term) {
            $model = new PaymentTerm();
            $this->saveModel($model, $term);
        }

        $this->stdout("   ✓ Created " . count($terms) . " payment terms\n", \yii\helpers\Console::FG_GREEN);
    }

    /**
     * Seed bank accounts
     */
    public function actionBankAccounts()
    {
        $this->stdout("🏦 Seeding bank accounts...\n");

        $banks = [
            ['name' => 'Commercial Bank', 'account_number' => '1234567890', 'type' => 'current'],
            ['name' => 'Bank of Ceylon', 'account_number' => '0987654321', 'type' => 'savings'],
            ['name' => 'Sampath Bank', 'account_number' => '5555444433', 'type' => 'current'],
        ];

        foreach ($banks as $bank) {
            $account = new BankAccount();
            $this->saveModel($account, [
                'account_name' => 'My Business Ltd.',
                'account_number' => $bank['account_number'],
                'bank_name' => $bank['name'],
                'branch_name' => $this->faker->city . ' Branch',
                'account_type' => $bank['type'],
                'currency' => 'LKR',
                'is_active' => 1,
                'notes' => $this->faker->optional(0.3)->sentence,
            ]);
        }

        $this->stdout("   ✓ Created " . count($banks) . " bank accounts\n", \yii\helpers\Console::FG_GREEN);
    }

    /**
     * Seed customers
     */
    public function actionCustomers($count = 20)
    {
        $this->stdout("👥 Seeding customers...\n");

        for ($i = 0; $i < $count; $i++) {
            $customer = new Customer();
            $customerId = $this->saveModel($customer, [
                'company_name' => $this->faker->company,
                'contact_person' => $this->faker->name,
                'email' => $this->faker->unique()->companyEmail,
                'phone' => $this->faker->phoneNumber,
                'address' => $this->faker->streetAddress,
                'city' => $this->faker->city,
                'state' => $this->faker->state,
                'postal_code' => $this->faker->postcode,
                'country' => $this->faker->randomElement(['Sri Lanka', 'India', 'Singapore', 'USA', 'UK']),
                'tax_number' => $this->faker->optional(0.7)->numerify('VAT-#########'),
                'website' => $this->faker->optional(0.6)->domainName,
                'notes' => $this->faker->optional(0.3)->sentence,
                'status' => Customer::STATUS_ACTIVE,
                'default_currency' => $this->faker->randomElement(['LKR', 'USD', 'EUR', 'GBP']),
            ]);

            // Add customer emails
            $emailTypes = ['to', 'cc', 'bcc'];
            $emailCount = rand(1, 3);
            for ($j = 0; $j < $emailCount; $j++) {
                $custEmail = new CustomerEmail();
                $this->saveModel($custEmail, [
                    'customer_id' => $customer->id,
                    'email' => $this->faker->unique()->companyEmail,
                    'type' => $emailTypes[$j % count($emailTypes)],
                ]);
            }
        }

        $this->stdout("   ✓ Created $count customers\n", \yii\helpers\Console::FG_GREEN);
    }

    /**
     * Seed vendors
     */
    public function actionVendors($count = 15)
    {
        $this->stdout("🏪 Seeding vendors...\n");

        for ($i = 0; $i < $count; $i++) {
            $vendor = new Vendor([
                'name' => $this->faker->company,
                'contact' => $this->faker->name,
                'email' => $this->faker->companyEmail,
                'address' => $this->faker->address,
                'currency_code' => 'LKR',
                'created_at' => time() - rand(0, 60 * 60 * 24 * 365),
                'updated_at' => time(),
                'created_by' => $this->userId,
                'updated_by' => $this->userId,
            ]);
            $vendor->save(false);
        }

        $this->stdout("   ✓ Created $count vendors\n", \yii\helpers\Console::FG_GREEN);
    }

    /**
     * Seed employees
     */
    public function actionEmployees($count = 12)
    {
        $this->stdout("👔 Seeding employees...\n");

        $positions = ['Software Engineer', 'Accountant', 'Sales Manager', 'HR Manager', 'Marketing Specialist',
                     'Project Manager', 'Designer', 'Developer', 'Business Analyst', 'Admin Officer'];
        $departments = ['IT', 'Finance', 'Sales', 'HR', 'Marketing', 'Operations', 'Admin'];

        for ($i = 0; $i < $count; $i++) {
            $hireDate = date('Y-m-d', strtotime('-' . rand(1, 1800) . ' days'));

            $employee = new Employee([
                'first_name' => $this->faker->firstName,
                'last_name' => $this->faker->lastName,
                'nic' => $this->generateNIC(),
                'phone' => $this->faker->phoneNumber,
                'position' => $this->faker->randomElement($positions),
                'department' => $this->faker->randomElement($departments),
                'hire_date' => $hireDate,
                'salary' => rand(50000, 200000),
                'created_at' => time(),
                'updated_at' => time(),
                'created_by' => $this->userId,
                'updated_by' => $this->userId,
            ]);

            if ($employee->save(false)) {
                // Add payroll details
                $payroll = new EmployeePayrollDetails([
                    'employee_id' => $employee->id,
                    'epf_number' => 'EPF' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                    'etf_number' => 'ETF' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                    'bank_account' => $this->faker->numerify('##########'),
                    'bank_name' => $this->faker->randomElement(['Commercial Bank', 'BOC', 'Sampath Bank', 'HNB']),
                    'tax_file_number' => 'TIN' . $this->faker->numerify('#########'),
                    'created_at' => time(),
                    'updated_at' => time(),
                ]);
                $payroll->save(false);
            }
        }

        $this->stdout("   ✓ Created $count employees\n", \yii\helpers\Console::FG_GREEN);
    }

    /**
     * Seed invoices with items
     */
    public function actionInvoices($count = 30)
    {
        $this->stdout("🧾 Seeding invoices...\n");

        $customers = Customer::find()->select('id')->column();
        $paymentTerms = PaymentTerm::find()->select('id')->column();

        if (empty($customers)) {
            $this->stdout("   ⚠ No customers found. Skipping invoices.\n", \yii\helpers\Console::FG_YELLOW);
            return;
        }

        $statuses = [Invoice::STATUS_PENDING, Invoice::STATUS_PAID, Invoice::STATUS_CANCELLED];
        $currencies = ['LKR', 'USD', 'EUR'];
        $exchangeRates = ['LKR' => 1, 'USD' => 320, 'EUR' => 350];

        for ($i = 0; $i < $count; $i++) {
            $invoiceDate = date('Y-m-d', strtotime('-' . rand(0, 180) . ' days'));
            $dueDate = date('Y-m-d', strtotime($invoiceDate . ' +30 days'));
            $currency = $this->faker->randomElement($currencies);
            $status = $this->faker->randomElement($statuses);

            $invoice = new Invoice([
                'customer_id' => $this->faker->randomElement($customers),
                'invoice_number' => 'INV-' . date('Y') . '-' . str_pad($i + 1, 5, '0', STR_PAD_LEFT),
                'invoice_date' => $invoiceDate,
                'due_date' => $dueDate,
                'payment_date' => $status === Invoice::STATUS_PAID ? date('Y-m-d', strtotime($dueDate . ' +' . rand(0, 10) . ' days')) : null,
                'payment_term_id' => $this->faker->randomElement($paymentTerms),
                'currency_code' => $currency,
                'exchange_rate' => $exchangeRates[$currency],
                'status' => $status,
                'notes' => $this->faker->optional(0.3)->sentence,
                'created_at' => strtotime($invoiceDate),
                'updated_at' => time(),
                'created_by' => $this->userId,
                'updated_by' => $this->userId,
            ]);

            // Calculate totals
            $subtotal = 0;
            $itemsCount = rand(2, 6);
            $items = [];

            for ($j = 0; $j < $itemsCount; $j++) {
                $quantity = rand(1, 10);
                $unitPrice = rand(1000, 50000);
                $total = $quantity * $unitPrice;
                $subtotal += $total;

                $items[] = [
                    'description' => $this->faker->randomElement([
                        'Web Development Services',
                        'Mobile App Development',
                        'UI/UX Design',
                        'Consulting Services',
                        'Software License',
                        'Support & Maintenance',
                        'Cloud Hosting',
                        'SEO Services',
                    ]),
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total' => $total,
                ];
            }

            $taxRate = 0.18; // 18% tax
            $taxAmount = $subtotal * $taxRate;
            $discount = rand(0, 5000);
            $totalAmount = $subtotal + $taxAmount - $discount;

            $invoice->subtotal = $subtotal;
            $invoice->tax_amount = $taxAmount;
            $invoice->discount = $discount;
            $invoice->total_amount = $totalAmount;
            $invoice->total_amount_lkr = $totalAmount * $exchangeRates[$currency];

            if ($invoice->save(false)) {
                // Add invoice items
                foreach ($items as $itemData) {
                    $item = new InvoiceItem([
                        'invoice_id' => $invoice->id,
                        'description' => $itemData['description'],
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'total' => $itemData['total'],
                        'created_at' => time(),
                        'updated_at' => time(),
                    ]);
                    $item->save(false);
                }
            }
        }

        $this->stdout("   ✓ Created $count invoices with items\n", \yii\helpers\Console::FG_GREEN);
    }

    /**
     * Seed expenses
     */
    public function actionExpenses($count = 40)
    {
        $this->stdout("💰 Seeding expenses...\n");

        $categories = ExpenseCategory::find()->select('id')->column();
        $vendors = Vendor::find()->select('id')->column();

        if (empty($categories)) {
            $this->stdout("   ⚠ No categories found. Skipping expenses.\n", \yii\helpers\Console::FG_YELLOW);
            return;
        }

        $paymentMethods = ['cash', 'bank_transfer', 'credit_card', 'cheque'];
        $statuses = ['pending', 'approved', 'paid', 'rejected'];

        for ($i = 0; $i < $count; $i++) {
            $expenseDate = date('Y-m-d', strtotime('-' . rand(0, 180) . ' days'));
            $amount = rand(5000, 200000);

            $expense = new Expense([
                'expense_category_id' => $this->faker->randomElement($categories),
                'expense_date' => $expenseDate,
                'title' => $this->faker->randomElement([
                    'Office supplies purchase',
                    'Monthly software subscription',
                    'Utility bill payment',
                    'Equipment repair',
                    'Business travel expense',
                    'Marketing campaign cost',
                    'Professional services fee',
                    'Insurance premium',
                    'Vehicle maintenance',
                    'Training workshop fee',
                ]),
                'description' => $this->faker->optional(0.6)->sentence,
                'amount' => $amount,
                'currency_code' => 'LKR',
                'exchange_rate' => 1,
                'amount_lkr' => $amount,
                'tax_amount' => $amount * 0.18,
                'receipt_number' => 'RCP-' . $this->faker->numerify('######'),
                'receipt_date' => $expenseDate,
                'payment_method' => $this->faker->randomElement($paymentMethods),
                'payment_date' => date('Y-m-d', strtotime($expenseDate . ' +' . rand(1, 15) . ' days')),
                'status' => $this->faker->randomElement($statuses),
                'vendor_id' => $this->faker->optional(0.7)->randomElement($vendors),
                'created_at' => strtotime($expenseDate),
                'updated_at' => time(),
                'created_by' => $this->userId,
                'updated_by' => $this->userId,
            ]);
            $expense->save(false);
        }

        $this->stdout("   ✓ Created $count expenses\n", \yii\helpers\Console::FG_GREEN);
    }

    /**
     * Seed tax records
     */
    public function actionTaxRecords($count = 12)
    {
        $this->stdout("📊 Seeding tax records...\n");

        $months = ['January', 'February', 'March', 'April', 'May', 'June',
                  'July', 'August', 'September', 'October', 'November', 'December'];

        $currentYear = date('Y');
        $lastYear = $currentYear - 1;

        foreach ($months as $index => $month) {
            $monthNum = $index + 1;
            $year = $monthNum <= date('n') ? $currentYear : $lastYear;

            $taxRecord = new TaxRecord([
                'tax_year' => $year,
                'tax_period' => $month,
                'total_income_lkr' => rand(800000, 2000000),
                'total_expenses_lkr' => rand(300000, 800000),
                'taxable_income_lkr' => rand(400000, 1200000),
                'tax_amount_lkr' => rand(50000, 200000),
                'status' => $this->faker->randomElement(['draft', 'submitted', 'approved']),
                'tax_code' => 'TC-' . $year . '-' . str_pad($monthNum, 2, '0', STR_PAD_LEFT),
                'created_at' => time(),
                'updated_at' => time(),
                'created_by' => $this->userId,
                'updated_by' => $this->userId,
            ]);

            if ($taxRecord->save(false)) {
                // Add tax payment if submitted
                if ($taxRecord->status === 'submitted' || $taxRecord->status === 'approved') {
                    $payment = new TaxPayment([
                        'tax_record_id' => $taxRecord->id,
                        'amount_lkr' => $taxRecord->tax_amount_lkr,
                        'payment_date' => date('Y-m-d', strtotime("$year-$monthNum-15")),
                        'payment_method' => $this->faker->randomElement(['bank_transfer', 'cheque']),
                        'payment_reference' => 'PAY-' . $this->faker->numerify('########'),
                        'created_at' => time(),
                        'updated_at' => time(),
                    ]);
                    $payment->save(false);
                }
            }
        }

        $this->stdout("   ✓ Created $count tax records\n", \yii\helpers\Console::FG_GREEN);
    }

    /**
     * Seed capital assets
     */
    public function actionCapitalAssets($count = 10)
    {
        $this->stdout("🏢 Seeding capital assets...\n");

        $assetTypes = ['Computer', 'Vehicle', 'Furniture', 'Office Equipment', 'Machinery', 'Building'];

        for ($i = 0; $i < $count; $i++) {
            $purchaseDate = date('Y-m-d', strtotime('-' . rand(365, 1825) . ' days')); // 1-5 years ago
            $purchasePrice = rand(100000, 5000000);

            $asset = new CapitalAsset([
                'asset_name' => $this->faker->randomElement($assetTypes) . ' ' . $this->faker->numberBetween(1, 100),
                'asset_type' => $this->faker->randomElement($assetTypes),
                'purchase_date' => $purchaseDate,
                'purchase_price_lkr' => $purchasePrice,
                'depreciation_rate' => rand(5, 25), // 5-25%
                'useful_life_years' => rand(3, 20),
                'current_value_lkr' => $purchasePrice * (rand(40, 90) / 100),
                'description' => $this->faker->sentence,
                'status' => $this->faker->randomElement(['active', 'disposed', 'sold']),
                'created_at' => strtotime($purchaseDate),
                'updated_at' => time(),
                'created_by' => $this->userId,
                'updated_by' => $this->userId,
            ]);
            $asset->save(false);
        }

        $this->stdout("   ✓ Created $count capital assets\n", \yii\helpers\Console::FG_GREEN);
    }

    /**
     * Clear all seeded data
     */
    public function actionClear()
    {
        $this->stdout("🗑️  Clearing all seeded data...\n");

        if (!$this->confirm('This will delete ALL data except the admin user. Continue?')) {
            return ExitCode::OK;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Delete in reverse order of dependencies
            InvoiceItem::deleteAll();
            Invoice::deleteAll();
            Expense::deleteAll();
            TaxPayment::deleteAll();
            TaxRecord::deleteAll();
            FinancialTransaction::deleteAll();
            Paysheet::deleteAll();
            EmployeePayrollDetails::deleteAll();
            Employee::deleteAll();
            CustomerEmail::deleteAll();
            Customer::deleteAll();
            Vendor::deleteAll();
            CapitalAsset::deleteAll();
            BankAccount::deleteAll();
            ExpenseCategory::deleteAll();
            PaymentTerm::deleteAll();
            TaxConfig::deleteAll();

            $transaction->commit();
            $this->stdout("✅ All data cleared successfully!\n", \yii\helpers\Console::FG_GREEN);
            return ExitCode::OK;
        } catch (\Exception $e) {
            $transaction->rollBack();
            $this->stderr("❌ Error: " . $e->getMessage() . "\n", \yii\helpers\Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Save model with proper user attribution
     * This method ensures created_by and updated_by are set correctly in console mode
     */
    private function saveModel($model, $attributes = [])
    {
        // Detach both timestamp and blameable behaviors
        $model->detachBehaviors();

        // Set all attributes
        foreach ($attributes as $key => $value) {
            $model->$key = $value;
        }

        // Manually set timestamps
        $time = time();
        if ($model->hasAttribute('created_at') && empty($model->created_at)) {
            $model->created_at = $time;
        }
        if ($model->hasAttribute('updated_at')) {
            $model->updated_at = $time;
        }

        // Manually set user attribution
        if ($model->hasAttribute('created_by')) {
            $model->created_by = $this->userId;
        }
        if ($model->hasAttribute('updated_by')) {
            $model->updated_by = $this->userId;
        }

        return $model->save(false);
    }

    /**
     * Show summary of seeded data
     */
    private function showSummary()
    {
        $this->stdout("\n📈 Seeding Summary:\n", \yii\helpers\Console::FG_CYAN);
        $this->stdout("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n");

        $counts = [
            'Customers' => Customer::find()->count(),
            'Customer Emails' => CustomerEmail::find()->count(),
            'Vendors' => Vendor::find()->count(),
            'Employees' => Employee::find()->count(),
            'Invoices' => Invoice::find()->count(),
            'Invoice Items' => InvoiceItem::find()->count(),
            'Expenses' => Expense::find()->count(),
            'Expense Categories' => ExpenseCategory::find()->count(),
            'Tax Records' => TaxRecord::find()->count(),
            'Tax Payments' => TaxPayment::find()->count(),
            'Capital Assets' => CapitalAsset::find()->count(),
            'Bank Accounts' => BankAccount::find()->count(),
            'Payment Terms' => PaymentTerm::find()->count(),
        ];

        foreach ($counts as $label => $count) {
            $this->stdout(sprintf("  %-20s : %d\n", $label, $count));
        }

        $this->stdout("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n");
    }

    /**
     * Generate valid Sri Lankan NIC
     */
    private function generateNIC()
    {
        // Generate old format NIC (9 digits + V)
        if (rand(0, 1)) {
            return $this->faker->numerify('#########') . 'V';
        }
        // Generate new format NIC (12 digits)
        return $this->faker->numerify('############');
    }
}

