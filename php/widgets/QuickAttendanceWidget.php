<?php

namespace app\widgets;

use app\models\Employee;
use app\models\EmployeeAttendance;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Quick Attendance Widget
 * Displays a quick form to add today's attendance on the homepage
 */
class QuickAttendanceWidget extends Widget
{
    public function run()
    {
        $model = new EmployeeAttendance();
        $model->attendance_date = date('Y-m-d');

        $employees = Employee::getList();
        $attendanceTypes = EmployeeAttendance::getAttendanceTypes();

        // Get today's attendance records
        $todayAttendance = EmployeeAttendance::find()
            ->where(['attendance_date' => date('Y-m-d')])
            ->with('employee')
            ->all();

        return $this->render('quick-attendance', [
            'model' => $model,
            'employees' => $employees,
            'attendanceTypes' => $attendanceTypes,
            'todayAttendance' => $todayAttendance,
        ]);
    }
}

