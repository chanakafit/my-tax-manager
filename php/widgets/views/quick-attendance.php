<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\EmployeeAttendance $model */
/** @var array $employees */
/** @var array $attendanceTypes */
/** @var app\models\EmployeeAttendance[] $todayAttendance */
?>

<div class="quick-attendance-widget card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-calendar-check"></i> Quick Attendance - <?= date('F d, Y') ?>
        </h5>
    </div>
    <div class="card-body">
        <?php $form = ActiveForm::begin([
            'id' => 'quick-attendance-form',
            'action' => Url::to(['/employee-attendance/quick-add']),
            'options' => ['class' => 'mb-3'],
        ]); ?>

        <div class="row">
            <div class="col-md-5">
                <?= $form->field($model, 'employee_id')->dropDownList($employees, [
                    'prompt' => 'Select Employee...',
                    'class' => 'form-control',
                ]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'attendance_type')->dropDownList($attendanceTypes, [
                    'class' => 'form-control',
                ]) ?>
            </div>
            <div class="col-md-3">
                <label>&nbsp;</label>
                <?= Html::submitButton('<i class="fas fa-plus"></i> Add', [
                    'class' => 'btn btn-success btn-block',
                    'id' => 'quick-add-btn',
                ]) ?>
            </div>
        </div>

        <?= $form->field($model, 'attendance_date')->hiddenInput(['value' => date('Y-m-d')])->label(false) ?>

        <?php ActiveForm::end(); ?>

        <div id="quick-attendance-message"></div>

        <?php if (!empty($todayAttendance)): ?>
            <div class="today-attendance mt-3">
                <h6 class="border-bottom pb-2">Today's Attendance (<?= count($todayAttendance) ?> records)</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Type</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($todayAttendance as $attendance): ?>
                                <tr>
                                    <td>
                                        <?= Html::a(
                                            Html::encode($attendance->employee->fullName),
                                            ['/employee/view', 'id' => $attendance->employee_id],
                                            ['target' => '_blank']
                                        ) ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $attendance->attendance_type === 'full_day' ? 'success' : ($attendance->attendance_type === 'half_day' ? 'warning' : 'info') ?>">
                                            <?= Html::encode($attendance->attendanceTypeLabel) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= Html::a('<i class="fas fa-edit"></i>', ['/employee-attendance/update', 'id' => $attendance->id], [
                                            'class' => 'btn btn-sm btn-primary',
                                            'title' => 'Edit',
                                        ]) ?>
                                        <?= Html::a('<i class="fas fa-trash"></i>', ['/employee-attendance/delete', 'id' => $attendance->id], [
                                            'class' => 'btn btn-sm btn-danger',
                                            'title' => 'Delete',
                                            'data' => [
                                                'confirm' => 'Are you sure you want to delete this attendance record?',
                                                'method' => 'post',
                                            ],
                                        ]) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info mt-3">
                <i class="fas fa-info-circle"></i> No attendance records for today yet.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$this->registerJs(<<<JS
    $('#quick-attendance-form').on('beforeSubmit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = $('#quick-add-btn');
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Adding...');
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#quick-attendance-message').html(
                        '<div class="alert alert-success alert-dismissible fade show">' +
                        '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                        '<i class="fas fa-check-circle"></i> ' + response.message +
                        '</div>'
                    );
                    // Reload page to update the list
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    var errorMsg = response.message;
                    if (response.errors) {
                        errorMsg += '<ul>';
                        for (var field in response.errors) {
                            response.errors[field].forEach(function(error) {
                                errorMsg += '<li>' + error + '</li>';
                            });
                        }
                        errorMsg += '</ul>';
                    }
                    $('#quick-attendance-message').html(
                        '<div class="alert alert-danger alert-dismissible fade show">' +
                        '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                        '<i class="fas fa-exclamation-circle"></i> ' + errorMsg +
                        '</div>'
                    );
                }
            },
            error: function() {
                $('#quick-attendance-message').html(
                    '<div class="alert alert-danger alert-dismissible fade show">' +
                    '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                    '<i class="fas fa-exclamation-circle"></i> An error occurred. Please try again.' +
                    '</div>'
                );
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-plus"></i> Add');
            }
        });
        
        return false;
    });
JS
);
?>

