<?php

namespace tests\functional;

use app\models\TaxYearSnapshot;
use app\models\TaxYearBankBalance;
use app\models\OwnerBankAccount;
use FunctionalTester;

/**
 * Test the tax return manage balances functionality
 */
class TaxReturnManageBalancesCest
{
    protected function _before(FunctionalTester $I)
    {
        // Clean up test data
        TaxYearBankBalance::deleteAll(['bank_account_id' => [8888, 8889]]);
        TaxYearSnapshot::deleteAll(['tax_year' => 2098]);
        OwnerBankAccount::deleteAll(['id' => [8888, 8889]]);
    }

    protected function _after(FunctionalTester $I)
    {
        // Clean up test data
        TaxYearBankBalance::deleteAll(['bank_account_id' => [8888, 8889]]);
        TaxYearSnapshot::deleteAll(['tax_year' => 2098]);
        OwnerBankAccount::deleteAll(['id' => [8888, 8889]]);
    }

    /**
     * Test that multiple bank balances can be added
     */
    public function testAddMultipleBankBalances(FunctionalTester $I)
    {
        // Create test bank accounts
        $account1 = new OwnerBankAccount();
        $account1->id = 8888;
        $account1->account_name = 'Test Savings Account';
        $account1->account_number = '888-888-888';
        $account1->bank_name = 'Test Bank';
        $account1->currency = 'LKR';
        $account1->is_active = 1;
        $account1->save();

        $account2 = new OwnerBankAccount();
        $account2->id = 8889;
        $account2->account_name = 'Test Current Account';
        $account2->account_number = '889-889-889';
        $account2->bank_name = 'Test Bank';
        $account2->currency = 'USD';
        $account2->is_active = 1;
        $account2->save();

        // Submit balances for both accounts
        $I->amOnPage('/tax-return/manage-balances?year=2098');

        // Fill in first account balance
        $I->fillField('BankBalance[8888][balance]', '100000');
        $I->fillField('BankBalance[8888][balance_lkr]', '100000');

        // Fill in second account balance
        $I->fillField('BankBalance[8889][balance]', '500');
        $I->fillField('BankBalance[8889][balance_lkr]', '150000');

        $I->click('Save Balances');

        // Verify both balances were saved
        $balances = TaxYearBankBalance::find()
            ->joinWith('taxYearSnapshot')
            ->where(['tax_year' => 2098])
            ->all();

        $I->assertCount(2, $balances);
    }

    /**
     * Test that updating a balance preserves the file when no new file is uploaded
     */
    public function testUpdateBalancePreservesFile(FunctionalTester $I)
    {
        // Create test bank account
        $account = new OwnerBankAccount();
        $account->id = 8888;
        $account->account_name = 'Test Account';
        $account->account_number = '888-888-888';
        $account->bank_name = 'Test Bank';
        $account->currency = 'LKR';
        $account->is_active = 1;
        $account->save();

        // Create initial snapshot and balance
        $snapshot = TaxYearSnapshot::getOrCreate(2098);
        $balance = new TaxYearBankBalance();
        $balance->tax_year_snapshot_id = $snapshot->id;
        $balance->bank_account_id = $account->id;
        $balance->balance = 50000;
        $balance->balance_lkr = 50000;
        $balance->supporting_document = 'uploads/bank-statements/test_original.pdf';
        $balance->save();

        $originalDoc = $balance->supporting_document;

        // Update the balance without uploading a new file
        $I->amOnPage('/tax-return/manage-balances?year=2098');
        $I->seeInField('BankBalance[8888][balance]', '50000');

        // Change the balance amount
        $I->fillField('BankBalance[8888][balance]', '75000');
        $I->fillField('BankBalance[8888][balance_lkr]', '75000');

        // Don't upload a new file
        $I->click('Save Balances');

        // Verify the balance was updated but file was preserved
        $updatedBalance = TaxYearBankBalance::findOne(['bank_account_id' => 8888]);
        $I->assertEquals(75000, $updatedBalance->balance);
        $I->assertEquals($originalDoc, $updatedBalance->supporting_document);
    }

    /**
     * Test that removing a balance entry deletes it
     */
    public function testRemovingBalanceDeletesIt(FunctionalTester $I)
    {
        // Create test bank accounts
        $account1 = new OwnerBankAccount();
        $account1->id = 8888;
        $account1->account_name = 'Test Account 1';
        $account1->account_number = '888-888-888';
        $account1->bank_name = 'Test Bank';
        $account1->currency = 'LKR';
        $account1->is_active = 1;
        $account1->save();

        $account2 = new OwnerBankAccount();
        $account2->id = 8889;
        $account2->account_name = 'Test Account 2';
        $account2->account_number = '889-889-889';
        $account2->bank_name = 'Test Bank';
        $account2->currency = 'LKR';
        $account2->is_active = 1;
        $account2->save();

        // Create initial balances for both accounts
        $snapshot = TaxYearSnapshot::getOrCreate(2098);

        $balance1 = new TaxYearBankBalance();
        $balance1->tax_year_snapshot_id = $snapshot->id;
        $balance1->bank_account_id = $account1->id;
        $balance1->balance = 10000;
        $balance1->balance_lkr = 10000;
        $balance1->save();

        $balance2 = new TaxYearBankBalance();
        $balance2->tax_year_snapshot_id = $snapshot->id;
        $balance2->bank_account_id = $account2->id;
        $balance2->balance = 20000;
        $balance2->balance_lkr = 20000;
        $balance2->save();

        // Verify both exist
        $I->assertEquals(2, TaxYearBankBalance::find()
            ->where(['tax_year_snapshot_id' => $snapshot->id])
            ->count());

        // Update: only submit balance for account 1 (remove account 2)
        $I->amOnPage('/tax-return/manage-balances?year=2098');

        // Only fill in first account
        $I->fillField('BankBalance[8888][balance]', '15000');
        $I->fillField('BankBalance[8888][balance_lkr]', '15000');

        // Leave second account empty (simulating removal)
        $I->click('Save Balances');

        // Verify only first balance exists
        $balances = TaxYearBankBalance::find()
            ->where(['tax_year_snapshot_id' => $snapshot->id])
            ->all();

        $I->assertCount(1, $balances);
        $I->assertEquals(8888, $balances[0]->bank_account_id);
        $I->assertEquals(15000, $balances[0]->balance);
    }
}

