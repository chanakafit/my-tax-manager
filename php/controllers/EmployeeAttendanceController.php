<?php

namespace app\controllers;

use app\models\EmployeeAttendance;
use app\models\EmployeeAttendanceSearch;
use app\models\Employee;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * EmployeeAttendanceController implements the CRUD actions for EmployeeAttendance model.
 */
class EmployeeAttendanceController extends BaseController
{
    /**
     * @inheritDoc
     */
    public function behaviors(): array
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all EmployeeAttendance models.
     *
     * @return string
     */
    public function actionIndex(): string
    {
        $searchModel = new EmployeeAttendanceSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists attendance for a specific employee
     *
     * @param int $employeeId
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionEmployeeIndex(int $employeeId): string
    {
        $employee = Employee::findOne($employeeId);
        if (!$employee) {
            throw new NotFoundHttpException('Employee not found.');
        }

        $searchModel = new EmployeeAttendanceSearch();
        $searchModel->employee_id = $employeeId;
        $dataProvider = $searchModel->search($this->request->queryParams);

        // Get current month and year for summary
        $year = Yii::$app->request->get('year', date('Y'));
        $month = Yii::$app->request->get('month', date('m'));

        $monthlySummary = EmployeeAttendance::getMonthlySummary($employeeId, $year, $month);
        $yearlySummary = EmployeeAttendance::getYearlySummary($employeeId, $year);

        return $this->render('employee-index', [
            'employee' => $employee,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'monthlySummary' => $monthlySummary,
            'yearlySummary' => $yearlySummary,
            'selectedYear' => $year,
            'selectedMonth' => $month,
        ]);
    }

    /**
     * Displays a single EmployeeAttendance model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id): string
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new EmployeeAttendance model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @param int|null $employeeId
     * @return string|Response
     */
    public function actionCreate($employeeId = null)
    {
        $model = new EmployeeAttendance();

        if ($employeeId) {
            $model->employee_id = $employeeId;
        }

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                Yii::$app->session->setFlash('success', 'Attendance record created successfully.');

                // Redirect back to employee attendance page if employee_id is set
                if ($model->employee_id) {
                    return $this->redirect(['employee-index', 'employeeId' => $model->employee_id]);
                }

                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
            // Set default date to today
            $model->attendance_date = date('Y-m-d');
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing EmployeeAttendance model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Attendance record updated successfully.');

            // Redirect back to employee attendance page if employee_id is set
            if ($model->employee_id) {
                return $this->redirect(['employee-index', 'employeeId' => $model->employee_id]);
            }

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing EmployeeAttendance model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return Response
     * @throws NotFoundHttpException if the model cannot be found
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id): Response
    {
        $model = $this->findModel($id);
        $employeeId = $model->employee_id;

        $model->delete();
        Yii::$app->session->setFlash('success', 'Attendance record deleted successfully.');

        // Redirect back to employee attendance page if employee_id is set
        if ($employeeId) {
            return $this->redirect(['employee-index', 'employeeId' => $employeeId]);
        }

        return $this->redirect(['index']);
    }

    /**
     * Quick add attendance for today via AJAX
     *
     * @return array
     */
    public function actionQuickAdd(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = new EmployeeAttendance();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                return [
                    'success' => true,
                    'message' => 'Attendance recorded successfully.',
                    'data' => [
                        'id' => $model->id,
                        'employee' => $model->employee->fullName,
                        'date' => $model->attendance_date,
                        'type' => $model->attendanceTypeLabel,
                    ],
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to record attendance.',
                    'errors' => $model->errors,
                ];
            }
        }

        return [
            'success' => false,
            'message' => 'Invalid request.',
        ];
    }

    /**
     * Get monthly summary via AJAX
     *
     * @param int $employeeId
     * @param string $year
     * @param string $month
     * @return array
     */
    public function actionMonthlySummary(int $employeeId, string $year, string $month): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $summary = EmployeeAttendance::getMonthlySummary($employeeId, $year, $month);

        return [
            'success' => true,
            'data' => $summary,
        ];
    }

    /**
     * Finds the EmployeeAttendance model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return EmployeeAttendance the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id): EmployeeAttendance
    {
        if (($model = EmployeeAttendance::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested attendance record does not exist.');
    }
}

