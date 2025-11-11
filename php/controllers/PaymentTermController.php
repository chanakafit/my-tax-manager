<?php

namespace app\controllers;

use Yii;
use app\models\PaymentTerm;
use app\models\PaymentTermSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;

/**
 * PaymentTermController implements the CRUD actions for PaymentTerm model.
 */
class PaymentTermController extends BaseController
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return [
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
                    'get-days' => ['GET'],
                ],
            ],
        ];
    }

    /**
     * Lists all PaymentTerm models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new PaymentTermSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single PaymentTerm model.
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
     * Creates a new PaymentTerm model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new PaymentTerm();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing PaymentTerm model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing PaymentTerm model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the PaymentTerm model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return PaymentTerm the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PaymentTerm::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Gets the number of days for a payment term via AJAX
     * @param int $id Payment Term ID
     * @return int|string Number of days or error message
     */
    public function actionGetDays($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $model = $this->findModel($id);
            return $model->days;
        } catch (NotFoundHttpException $e) {
            Yii::$app->response->statusCode = 404;
            return 'Payment term not found';
        }
    }

    /**
     * Gets the due date based on payment term and invoice date via AJAX
     * @param int $id Payment Term ID
     * @param string $invoiceDate Invoice date in Y-m-d format
     * @return array Response containing the calculated due date
     */
    public function actionGetDueDate($id, $invoiceDate)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $model = $this->findModel($id);

            if (!$model || !$invoiceDate) {
                throw new \Exception('Invalid parameters');
            }

            // Parse and validate the invoice date
            $date = \DateTime::createFromFormat('Y-m-d', $invoiceDate);
            if (!$date) {
                throw new \Exception('Invalid date format');
            }

            // Calculate due date
            $date->modify("+{$model->days} days");

            return [
                'success' => true,
                'dueDate' => $date->format('Y-m-d')
            ];

        } catch (\Exception $e) {
            Yii::$app->response->statusCode = 400;
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
