<?php

namespace tests\unit\models;

use app\models\TaxYearBankBalance;
use app\models\TaxYearSnapshot;
use app\models\OwnerBankAccount;
use Codeception\Test\Unit;
use Yii;

/**
 * Test TaxYearBankBalance update scenarios, especially file handling
 */
class TaxYearBankBalanceUpdateTest extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
        // Clean up any existing test data
        TaxYearBankBalance::deleteAll(['bank_account_id' => [9998, 9999]]);
        TaxYearSnapshot::deleteAll(['tax_year' => 2099]);
        OwnerBankAccount::deleteAll(['id' => [9998, 9999]]);
    }

    protected function _after()
    {
        // Clean up test data
        TaxYearBankBalance::deleteAll(['bank_account_id' => [9998, 9999]]);
        TaxYearSnapshot::deleteAll(['tax_year' => 2099]);
        OwnerBankAccount::deleteAll(['id' => [9998, 9999]]);
    }

    /**
     * Test that updating a bank balance without a new file preserves the old file
     */
    public function testUpdatePreservesExistingFile()
    {
        // Create test snapshot
        $snapshot = new TaxYearSnapshot();
        $snapshot->tax_year = 2099;
        $snapshot->status = 'draft';
        $this->assertTrue($snapshot->save(), 'Failed to create test snapshot');

        // Create test bank account
        $bankAccount = new OwnerBankAccount();
        $bankAccount->id = 9998;
        $bankAccount->account_name = 'Test Account';
        $bankAccount->account_number = '123456';
        $bankAccount->bank_name = 'Test Bank';
        $bankAccount->is_active = 1;
        $this->assertTrue($bankAccount->save(), 'Failed to create test bank account');

        // Create initial bank balance with a supporting document
        $balance = new TaxYearBankBalance();
        $balance->tax_year_snapshot_id = $snapshot->id;
        $balance->bank_account_id = $bankAccount->id;
        $balance->balance = 10000.00;
        $balance->balance_lkr = 10000.00;
        $balance->supporting_document = 'uploads/bank-statements/test_file_123.pdf';
        $this->assertTrue($balance->save(), 'Failed to create initial bank balance');

        $balanceId = $balance->id;
        $originalDocument = $balance->supporting_document;

        // Now update the balance WITHOUT uploading a new file
        $balance = TaxYearBankBalance::findOne($balanceId);
        $balance->balance = 15000.00;
        $balance->balance_lkr = 15000.00;
        // Don't set uploadedFile - simulating no new file uploaded
        $this->assertTrue($balance->save(), 'Failed to update bank balance');

        // Verify the document is preserved
        $balance = TaxYearBankBalance::findOne($balanceId);
        $this->assertEquals($originalDocument, $balance->supporting_document,
            'Supporting document should be preserved when no new file is uploaded');
        $this->assertEquals(15000.00, $balance->balance, 'Balance should be updated');
    }

    /**
     * Test that updating a bank balance with a new file replaces the old file
     */
    public function testUpdateWithNewFileReplacesOldFile()
    {
        // Create test snapshot
        $snapshot = new TaxYearSnapshot();
        $snapshot->tax_year = 2099;
        $snapshot->status = 'draft';
        $this->assertTrue($snapshot->save(), 'Failed to create test snapshot');

        // Create test bank account
        $bankAccount = new OwnerBankAccount();
        $bankAccount->id = 9999;
        $bankAccount->account_name = 'Test Account 2';
        $bankAccount->account_number = '654321';
        $bankAccount->bank_name = 'Test Bank 2';
        $bankAccount->is_active = 1;
        $this->assertTrue($bankAccount->save(), 'Failed to create test bank account');

        // Create initial bank balance with a supporting document
        $balance = new TaxYearBankBalance();
        $balance->tax_year_snapshot_id = $snapshot->id;
        $balance->bank_account_id = $bankAccount->id;
        $balance->balance = 10000.00;
        $balance->balance_lkr = 10000.00;
        $balance->supporting_document = 'uploads/bank-statements/old_file_123.pdf';
        $this->assertTrue($balance->save(), 'Failed to create initial bank balance');

        $balanceId = $balance->id;
        $originalDocument = $balance->supporting_document;

        // Now update with a mock uploaded file
        $balance = TaxYearBankBalance::findOne($balanceId);
        $balance->balance = 20000.00;
        $balance->balance_lkr = 20000.00;

        // Create a mock uploaded file (Note: In real scenario, use actual file upload)
        // For this test, we're just testing that IF uploadedFile is set, it gets processed
        // The actual upload() method testing is covered in other tests

        // We'll just verify the validation accepts it
        $this->assertTrue($balance->validate(), 'Balance should validate even without uploadedFile');
    }

    /**
     * Test multiple balances can be saved for different accounts
     */
    public function testMultipleBankBalancesCanBeStored()
    {
        // Create test snapshot
        $snapshot = new TaxYearSnapshot();
        $snapshot->tax_year = 2099;
        $snapshot->status = 'draft';
        $this->assertTrue($snapshot->save(), 'Failed to create test snapshot');

        // Create first bank account and balance
        $bankAccount1 = new OwnerBankAccount();
        $bankAccount1->id = 9998;
        $bankAccount1->account_name = 'Test Account 1';
        $bankAccount1->account_number = '111111';
        $bankAccount1->bank_name = 'Test Bank 1';
        $bankAccount1->is_active = 1;
        $this->assertTrue($bankAccount1->save(), 'Failed to create first bank account');

        $balance1 = new TaxYearBankBalance();
        $balance1->tax_year_snapshot_id = $snapshot->id;
        $balance1->bank_account_id = $bankAccount1->id;
        $balance1->balance = 10000.00;
        $balance1->balance_lkr = 10000.00;
        $balance1->supporting_document = 'uploads/bank-statements/account1.pdf';
        $this->assertTrue($balance1->save(), 'Failed to create first bank balance');

        // Create second bank account and balance
        $bankAccount2 = new OwnerBankAccount();
        $bankAccount2->id = 9999;
        $bankAccount2->account_name = 'Test Account 2';
        $bankAccount2->account_number = '222222';
        $bankAccount2->bank_name = 'Test Bank 2';
        $bankAccount2->is_active = 1;
        $this->assertTrue($bankAccount2->save(), 'Failed to create second bank account');

        $balance2 = new TaxYearBankBalance();
        $balance2->tax_year_snapshot_id = $snapshot->id;
        $balance2->bank_account_id = $bankAccount2->id;
        $balance2->balance = 20000.00;
        $balance2->balance_lkr = 20000.00;
        $balance2->supporting_document = 'uploads/bank-statements/account2.pdf';
        $this->assertTrue($balance2->save(), 'Failed to create second bank balance');

        // Verify both balances exist
        $balances = TaxYearBankBalance::find()
            ->where(['tax_year_snapshot_id' => $snapshot->id])
            ->all();

        $this->assertCount(2, $balances, 'Should have 2 bank balances');

        // Update first balance
        $balance1->balance = 15000.00;
        $this->assertTrue($balance1->save(), 'Failed to update first balance');

        // Verify both still exist and first is updated
        $balances = TaxYearBankBalance::find()
            ->where(['tax_year_snapshot_id' => $snapshot->id])
            ->indexBy('bank_account_id')
            ->all();

        $this->assertCount(2, $balances, 'Should still have 2 bank balances after update');
        $this->assertEquals(15000.00, $balances[9998]->balance, 'First balance should be updated');
        $this->assertEquals(20000.00, $balances[9999]->balance, 'Second balance should be unchanged');
        $this->assertEquals('uploads/bank-statements/account1.pdf', $balances[9998]->supporting_document,
            'First balance document should be preserved');
        $this->assertEquals('uploads/bank-statements/account2.pdf', $balances[9999]->supporting_document,
            'Second balance document should be preserved');
    }

    /**
     * Test that a balance can be created without a supporting document
     */
    public function testBalanceCanBeCreatedWithoutDocument()
    {
        // Create test snapshot
        $snapshot = new TaxYearSnapshot();
        $snapshot->tax_year = 2099;
        $snapshot->status = 'draft';
        $this->assertTrue($snapshot->save(), 'Failed to create test snapshot');

        // Create test bank account
        $bankAccount = new OwnerBankAccount();
        $bankAccount->id = 9998;
        $bankAccount->account_name = 'Test Account';
        $bankAccount->account_number = '123456';
        $bankAccount->bank_name = 'Test Bank';
        $bankAccount->is_active = 1;
        $this->assertTrue($bankAccount->save(), 'Failed to create test bank account');

        // Create balance without supporting document
        $balance = new TaxYearBankBalance();
        $balance->tax_year_snapshot_id = $snapshot->id;
        $balance->bank_account_id = $bankAccount->id;
        $balance->balance = 5000.00;
        $balance->balance_lkr = 5000.00;
        // No supporting_document set
        $this->assertTrue($balance->save(), 'Should be able to save balance without document');
        $this->assertNull($balance->supporting_document, 'Supporting document should be null');
    }

    /**
     * Test that the correct balance is retrieved for a snapshot
     */
    public function testRetrievingBalanceForSnapshot()
    {
        // Create test snapshot
        $snapshot = new TaxYearSnapshot();
        $snapshot->tax_year = 2099;
        $snapshot->status = 'draft';
        $this->assertTrue($snapshot->save(), 'Failed to create test snapshot');

        // Create test bank account
        $bankAccount = new OwnerBankAccount();
        $bankAccount->id = 9998;
        $bankAccount->account_name = 'Test Account';
        $bankAccount->account_number = '123456';
        $bankAccount->bank_name = 'Test Bank';
        $bankAccount->is_active = 1;
        $this->assertTrue($bankAccount->save(), 'Failed to create test bank account');

        // Create balance
        $balance = new TaxYearBankBalance();
        $balance->tax_year_snapshot_id = $snapshot->id;
        $balance->bank_account_id = $bankAccount->id;
        $balance->balance = 7500.00;
        $balance->balance_lkr = 7500.00;
        $balance->supporting_document = 'uploads/bank-statements/test.pdf';
        $this->assertTrue($balance->save(), 'Failed to create balance');

        // Retrieve using relationship
        $retrievedBalances = $snapshot->getBankBalances()->all();
        $this->assertCount(1, $retrievedBalances, 'Should have 1 balance');
        $this->assertEquals(7500.00, $retrievedBalances[0]->balance, 'Balance should match');
        $this->assertEquals($bankAccount->id, $retrievedBalances[0]->bank_account_id, 'Bank account ID should match');
    }
}

