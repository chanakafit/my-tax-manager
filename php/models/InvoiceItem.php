<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "invoice_item".
 *
 * @property int $id
 * @property int $invoice_id
 * @property string $item_name
 * @property string|null $description
 * @property float $quantity
 * @property float $unit_price
 * @property float|null $tax_rate
 * @property float|null $tax_amount
 * @property float|null $discount
 * @property float $total_amount
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 * @property int $updated_by
 *
 * @property Invoice $invoice
 */
class InvoiceItem extends BaseModel
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%invoice_item}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['description'], 'default', 'value' => null],
            [['discount'], 'default', 'value' => 0.00],
            [['invoice_id', 'item_name', 'quantity', 'unit_price', 'total_amount'], 'required'],
            [['invoice_id'], 'integer'],
            [['description'], 'string'],
            [['quantity', 'unit_price', 'tax_rate', 'tax_amount', 'discount', 'total_amount'], 'number'],
            [['item_name'], 'string', 'max' => 255],
            [['invoice_id'], 'exist', 'skipOnError' => true, 'targetClass' => Invoice::class, 'targetAttribute' => ['invoice_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'invoice_id' => 'Invoice ID',
            'item_name' => 'Item Name',
            'description' => 'Description',
            'quantity' => 'Quantity',
            'unit_price' => 'Unit Price',
            'tax_rate' => 'Tax Rate',
            'tax_amount' => 'Tax Amount',
            'discount' => 'Discount',
            'total_amount' => 'Total Amount',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[Invoice]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInvoice()
    {
        return $this->hasOne(Invoice::class, ['id' => 'invoice_id']);
    }

}

