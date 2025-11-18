<?php

namespace app\controllers;

use app\models\EmployeeSalaryAdvance;
use app\models\EmployeeSalaryAdvanceSearch;
use app\models\Employee;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * EmployeeSalaryAdvanceController implements the CRUD actions for EmployeeSalaryAdvance model.
 */
class EmployeeSalaryAdvanceController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        [
                            'allow' => true,
                            'roles' => ['@'],
                        ],
                    ],
                ],
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
     * Lists all EmployeeSalaryAdvance models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new EmployeeSalaryAdvanceSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists all EmployeeSalaryAdvance models for a specific employee.
     *
     * @param int $employeeId
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionEmployeeIndex($employeeId)
    {
        $employee = $this->findEmployee($employeeId);

        $searchModel = new EmployeeSalaryAdvanceSearch();
        $searchModel->employee_id = $employeeId;
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('employee-index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'employee' => $employee,
        ]);
    }

    /**
     * Displays a single EmployeeSalaryAdvance model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new EmployeeSalaryAdvance model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @param int|null $employeeId
     * @return string|\yii\web\Response
     */
    public function actionCreate($employeeId = null)
    {
        $model = new EmployeeSalaryAdvance();

        if ($employeeId !== null) {
            $employee = $this->findEmployee($employeeId);
            $model->employee_id = $employeeId;
        }

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                Yii::$app->session->setFlash('success', 'Salary advance created successfully.');

                if ($employeeId !== null) {
                    return $this->redirect(['employee-index', 'employeeId' => $employeeId]);
                }
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
            $model->advance_date = date('Y-m-d');
        }

        return $this->render('create', [
            'model' => $model,
            'employee' => isset($employee) ? $employee : null,
        ]);
    }

    /**
     * Updates an existing EmployeeSalaryAdvance model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Salary advance updated successfully.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing EmployeeSalaryAdvance model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $employeeId = $model->employee_id;

        $model->delete();
        Yii::$app->session->setFlash('success', 'Salary advance deleted successfully.');

        // Check if we came from employee-index
        $referrer = Yii::$app->request->referrer;
        if ($referrer && strpos($referrer, 'employee-index') !== false) {
            return $this->redirect(['employee-index', 'employeeId' => $employeeId]);
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the EmployeeSalaryAdvance model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return EmployeeSalaryAdvance the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = EmployeeSalaryAdvance::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested salary advance does not exist.');
    }

    /**
     * Finds the Employee model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Employee the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findEmployee($id)
    {
        if (($model = Employee::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested employee does not exist.');
    }
}

