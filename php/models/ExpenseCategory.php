<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "expense_category".
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property float|null $budget_limit
 * @property int|null $is_active
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 * @property int $updated_by
 *
 * @property Expense[] A$expenses
 */
class ExpenseCategory extends BaseModel
{


    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%expense_category}}';
    }

    public static function getList(): array
    {
        return self::find()
            ->select(['id', 'name'])
            ->where(['is_active' => 1])
            ->orderBy(['name' => SORT_ASC])
            ->all();
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['description'], 'default', 'value' => null],
            [['budget_limit'], 'default', 'value' => 0.00],
            [['is_active'], 'default', 'value' => 1],
            [['name'], 'required'],
            [['description'], 'string'],
            [['budget_limit'], 'number'],
            [['is_active'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'description' => 'Description',
            'budget_limit' => 'Budget Limit',
            'is_active' => 'Is Active',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[Expenses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getExpenses(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Expense::class, ['expense_category_id' => 'id']);
    }

}
