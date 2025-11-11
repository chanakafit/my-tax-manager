<?php

use yii\helpers\Html;
use app\widgets\BGridView as GridView;;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\models\PaysheetSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Paysheets';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="paysheet-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Generate Paysheets', ['generate'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <?php Pjax::begin(); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'attribute' => 'employee_name',
                'value' => function($model) {
                    return $model->employee ? $model->employee->fullName : 'N/A';
                },
            ],
            [
                'attribute' => 'pay_period_start',
                'format' => 'date',
            ],
            [
                'attribute' => 'pay_period_end',
                'format' => 'date',
            ],
            [
                'attribute' => 'basic_salary',
                'format' => ['decimal', 2],
                'contentOptions' => ['class' => 'text-end'],
            ],
            [
                'attribute' => 'net_salary',
                'format' => ['decimal', 2],
                'contentOptions' => ['class' => 'text-end'],
            ],
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::tag('span',
                        ucfirst($model->status),
                        ['class' => 'badge bg-' . ($model->status === 'paid' ? 'success' : 'warning')]
                    );
                },
                'filter' => ['pending' => 'Pending', 'paid' => 'Paid'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{actions}',
                'contentOptions' => ['class' => 'action-column text-nowrap'],
                'buttons' => [
                    'actions' => function ($url, $model) {
                        $buttons = '<div class="btn-group" role="group">';

                        // View button
                        $buttons .= Html::a(
                            '<i class="fas fa-eye"></i>',
                            ['view', 'id' => $model->id],
                            ['class' => 'btn btn-sm btn-info', 'title' => 'View']
                        );

                        // Update button
                        $buttons .= Html::a(
                            '<i class="fas fa-pencil-alt"></i>',
                            ['update', 'id' => $model->id],
                            ['class' => 'btn btn-sm btn-primary', 'title' => 'Update']
                        );

                        // Mark as paid button
                        if ($model->status === 'pending') {
                            $buttons .= Html::a(
                                '<i class="fas fa-money-bill"></i>',
                                ['mark-as-paid', 'id' => $model->id],
                                [
                                    'class' => 'btn btn-sm btn-success',
                                    'title' => 'Mark as Paid',
                                    'data-toggle' => 'tooltip'
                                ]
                            );
                        }

                        $buttons .= '</div>';
                        return $buttons;
                    }
                ],
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>

</div>
