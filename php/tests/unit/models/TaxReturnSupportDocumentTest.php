<?php

namespace tests\unit\models;

use app\models\TaxReturnSupportDocument;
use app\models\TaxYearSnapshot;
use Codeception\Test\Unit;
use tests\fixtures\TaxYearSnapshotFixture;
use Yii;

/**
 * Test TaxReturnSupportDocument model
 */
class TaxReturnSupportDocumentTest extends Unit
{
    protected $tester;

    /**
     * Load fixtures
     */
    public function _fixtures()
    {
        return [
            'snapshots' => [
                'class' => TaxYearSnapshotFixture::class,
            ],
        ];
    }

    /**
     * Test model instantiation
     */
    public function testModelInstantiation()
    {
        $model = new TaxReturnSupportDocument();
        verify($model)->instanceOf(TaxReturnSupportDocument::class);
    }

    /**
     * Test table name
     */
    public function testTableName()
    {
        verify(TaxReturnSupportDocument::tableName())->equals('{{%tax_return_support_document}}');
    }

    /**
     * Test required fields validation
     */
    public function testRequiredFields()
    {
        $model = new TaxReturnSupportDocument();
        $model->validate();

        verify($model->hasErrors('tax_year_snapshot_id'))->true();
        verify($model->hasErrors('document_title'))->true();
    }

    /**
     * Test valid model with required fields
     */
    public function testValidModelWithRequiredFields()
    {
        $snapshot = $this->tester->grabFixture('snapshots', 'year_2024');

        $model = new TaxReturnSupportDocument();
        $model->detachBehaviors();

        $model->tax_year_snapshot_id = $snapshot->id;
        $model->document_title = 'Test Document';
        $model->file_path = 'uploads/tax-return-documents/test.pdf';
        $model->file_name = 'test.pdf';
        $model->file_size = 1024;
        $model->created_at = time();
        $model->updated_at = time();
        $model->created_by = 1;
        $model->updated_by = 1;

        verify($model->validate())->true();
    }

    /**
     * Test document title max length
     */
    public function testDocumentTitleMaxLength()
    {
        $model = new TaxReturnSupportDocument();
        $model->document_title = str_repeat('a', 256); // 256 chars - exceeds max

        $model->validate(['document_title']);
        verify($model->hasErrors('document_title'))->true();

        $model->document_title = str_repeat('a', 255); // 255 chars - valid
        $model->validate(['document_title']);
        verify($model->hasErrors('document_title'))->false();
    }

    /**
     * Test file path max length
     */
    public function testFilePathMaxLength()
    {
        $model = new TaxReturnSupportDocument();
        $model->file_path = str_repeat('a', 501); // 501 chars - exceeds max

        $model->validate(['file_path']);
        verify($model->hasErrors('file_path'))->true();

        $model->file_path = str_repeat('a', 500); // 500 chars - valid
        $model->validate(['file_path']);
        verify($model->hasErrors('file_path'))->false();
    }

    /**
     * Test relationship to tax year snapshot
     */
    public function testTaxYearSnapshotRelationship()
    {
        $snapshot = $this->tester->grabFixture('snapshots', 'year_2024');

        $model = new TaxReturnSupportDocument();
        $model->detachBehaviors();

        $model->tax_year_snapshot_id = $snapshot->id;
        $model->document_title = 'Test Document';
        $model->file_path = 'uploads/test.pdf';
        $model->file_name = 'test.pdf';
        $model->file_size = 1024;
        $model->created_at = time();
        $model->updated_at = time();
        $model->created_by = 1;
        $model->updated_by = 1;

        verify($model->save(false))->true();

        // Test relationship
        $loadedModel = TaxReturnSupportDocument::findOne($model->id);
        verify($loadedModel->taxYearSnapshot)->notNull();
        verify($loadedModel->taxYearSnapshot->id)->equals($snapshot->id);
    }

    /**
     * Test getFormattedFileSize method
     */
    public function testGetFormattedFileSize()
    {
        $model = new TaxReturnSupportDocument();

        // Test bytes
        $model->file_size = 500;
        verify($model->getFormattedFileSize())->equals('500 bytes');

        // Test KB
        $model->file_size = 2048; // 2 KB
        verify($model->getFormattedFileSize())->equals('2.00 KB');

        // Test MB
        $model->file_size = 2097152; // 2 MB
        verify($model->getFormattedFileSize())->equals('2.00 MB');

        // Test GB
        $model->file_size = 2147483648; // 2 GB
        verify($model->getFormattedFileSize())->equals('2.00 GB');
    }

    /**
     * Test getFileExtension method
     */
    public function testGetFileExtension()
    {
        $model = new TaxReturnSupportDocument();

        $model->file_name = 'document.pdf';
        verify($model->getFileExtension())->equals('pdf');

        $model->file_name = 'image.jpg';
        verify($model->getFileExtension())->equals('jpg');

        $model->file_name = 'spreadsheet.xlsx';
        verify($model->getFileExtension())->equals('xlsx');

        $model->file_name = 'file.with.dots.docx';
        verify($model->getFileExtension())->equals('docx');
    }

    /**
     * Test attribute labels
     */
    public function testAttributeLabels()
    {
        $model = new TaxReturnSupportDocument();
        $labels = $model->attributeLabels();

        verify($labels['document_title'])->equals('Document Title');
        verify($labels['document_description'])->equals('Description');
        verify($labels['file_path'])->equals('File Path');
        verify($labels['file_name'])->equals('File Name');
        verify($labels['file_size'])->equals('File Size');
        verify($labels['uploadedFile'])->equals('Upload Document');
    }

    /**
     * Test cascade delete when snapshot is deleted
     */
    public function testCascadeDeleteOnSnapshotDelete()
    {
        $snapshot = $this->tester->grabFixture('snapshots', 'year_2024');

        $model = new TaxReturnSupportDocument();
        $model->detachBehaviors();

        $model->tax_year_snapshot_id = $snapshot->id;
        $model->document_title = 'Test Document for Delete';
        $model->file_path = 'uploads/test-delete.pdf';
        $model->file_name = 'test-delete.pdf';
        $model->file_size = 1024;
        $model->created_at = time();
        $model->updated_at = time();
        $model->created_by = 1;
        $model->updated_by = 1;

        verify($model->save(false))->true();
        $documentId = $model->id;

        // Delete snapshot
        $snapshot->delete();

        // Document should be deleted due to CASCADE
        verify(TaxReturnSupportDocument::findOne($documentId))->null();
    }

    /**
     * Test file size is integer
     */
    public function testFileSizeIsInteger()
    {
        $model = new TaxReturnSupportDocument();
        $model->file_size = 'not_a_number';

        $model->validate(['file_size']);
        verify($model->hasErrors('file_size'))->true();

        $model->file_size = 1024;
        $model->validate(['file_size']);
        verify($model->hasErrors('file_size'))->false();
    }

    /**
     * Test description field accepts long text
     */
    public function testDescriptionAcceptsLongText()
    {
        $model = new TaxReturnSupportDocument();
        $longText = str_repeat('This is a long description. ', 100); // ~2800 chars

        $model->document_description = $longText;
        $model->validate(['document_description']);

        verify($model->hasErrors('document_description'))->false();
    }

    /**
     * Test optional description field
     */
    public function testDescriptionIsOptional()
    {
        $snapshot = $this->tester->grabFixture('snapshots', 'year_2024');

        $model = new TaxReturnSupportDocument();
        $model->detachBehaviors();

        $model->tax_year_snapshot_id = $snapshot->id;
        $model->document_title = 'Test Without Description';
        $model->file_path = 'uploads/test.pdf';
        $model->file_name = 'test.pdf';
        $model->file_size = 1024;
        $model->document_description = null; // null description
        $model->created_at = time();
        $model->updated_at = time();
        $model->created_by = 1;
        $model->updated_by = 1;

        verify($model->validate())->true();
        verify($model->save(false))->true();
    }

    /**
     * Test mime type is optional
     */
    public function testMimeTypeIsOptional()
    {
        $snapshot = $this->tester->grabFixture('snapshots', 'year_2024');

        $model = new TaxReturnSupportDocument();
        $model->detachBehaviors();

        $model->tax_year_snapshot_id = $snapshot->id;
        $model->document_title = 'Test Without MIME Type';
        $model->file_path = 'uploads/test.pdf';
        $model->file_name = 'test.pdf';
        $model->file_size = 1024;
        $model->mime_type = null;
        $model->created_at = time();
        $model->updated_at = time();
        $model->created_by = 1;
        $model->updated_by = 1;

        verify($model->validate())->true();
    }

    /**
     * Test multiple documents for same snapshot
     */
    public function testMultipleDocumentsForSameSnapshot()
    {
        $snapshot = $this->tester->grabFixture('snapshots', 'year_2024');

        // Create first document
        $doc1 = new TaxReturnSupportDocument();
        $doc1->detachBehaviors();
        $doc1->tax_year_snapshot_id = $snapshot->id;
        $doc1->document_title = 'Document 1';
        $doc1->file_path = 'uploads/doc1.pdf';
        $doc1->file_name = 'doc1.pdf';
        $doc1->file_size = 1024;
        $doc1->created_at = time();
        $doc1->updated_at = time();
        $doc1->created_by = 1;
        $doc1->updated_by = 1;
        verify($doc1->save(false))->true();

        // Create second document
        $doc2 = new TaxReturnSupportDocument();
        $doc2->detachBehaviors();
        $doc2->tax_year_snapshot_id = $snapshot->id;
        $doc2->document_title = 'Document 2';
        $doc2->file_path = 'uploads/doc2.pdf';
        $doc2->file_name = 'doc2.pdf';
        $doc2->file_size = 2048;
        $doc2->created_at = time();
        $doc2->updated_at = time();
        $doc2->created_by = 1;
        $doc2->updated_by = 1;
        verify($doc2->save(false))->true();

        // Verify both exist
        $documents = TaxReturnSupportDocument::find()
            ->where(['tax_year_snapshot_id' => $snapshot->id])
            ->all();

        verify(count($documents))->greaterThan(1);
    }
}

