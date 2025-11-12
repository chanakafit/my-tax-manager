<?php

namespace app\controllers;

use app\models\PaysheetSuggestion;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use Yii;

/**
 * PaysheetSuggestionController implements the CRUD actions for PaysheetSuggestion model.
 */
class PaysheetSuggestionController extends Controller
{
    /**
     * {@inheritdoc}
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
                    'approve' => ['GET', 'POST'],
                    'reject' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all pending PaysheetSuggestion models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => PaysheetSuggestion::find()
                ->with(['employee'])
                ->where(['status' => PaysheetSuggestion::STATUS_PENDING])
                ->orderBy(['suggested_month' => SORT_DESC, 'employee_id' => SORT_ASC]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists all approved/rejected PaysheetSuggestion models.
     * @return mixed
     */
    public function actionHistory()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => PaysheetSuggestion::find()
                ->with(['employee', 'actionedBy'])
                ->where(['in', 'status', [PaysheetSuggestion::STATUS_APPROVED, PaysheetSuggestion::STATUS_REJECTED]])
                ->orderBy(['actioned_at' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('history', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single PaysheetSuggestion model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Updates an existing PaysheetSuggestion model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if (!$model->canEdit()) {
            Yii::$app->session->setFlash('error', 'This suggestion cannot be edited.');
            return $this->redirect(['index']);
        }

        if ($model->load(Yii::$app->request->post())) {
            // Recalculate net salary
            $model->net_salary = $model->basic_salary + $model->allowances - $model->deductions - $model->tax_amount;

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Paysheet suggestion updated successfully.');
                return $this->redirect(['index']);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Approve a PaysheetSuggestion and create actual Paysheet.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionApprove($id)
    {
        $model = $this->findModel($id);

        if (!$model->canEdit()) {
            Yii::$app->session->setFlash('error', 'This suggestion cannot be approved.');
            return $this->redirect(['index']);
        }

        // If GET request, show the approve form
        if (Yii::$app->request->isGet) {
            return $this->render('approve', [
                'model' => $model,
            ]);
        }

        // If POST request, process the approval
        if (Yii::$app->request->isPost) {
            $paymentDate = Yii::$app->request->post('payment_date', date('Y-m-d'));
            $paymentMethod = Yii::$app->request->post('payment_method', 'Bank Transfer');
            $paymentReference = Yii::$app->request->post('payment_reference');
            $notes = Yii::$app->request->post('notes', 'Generated from paysheet suggestion');

            $paysheet = $model->approveWithDetails(
                Yii::$app->user->id,
                $paymentDate,
                $paymentMethod,
                $paymentReference,
                $notes
            );

            if ($paysheet) {
                Yii::$app->session->setFlash('success',
                    'Paysheet approved successfully and created for ' . $model->employee->fullName . '.');
                return $this->redirect(['paysheet/view', 'id' => $paysheet->id]);
            } else {
                Yii::$app->session->setFlash('error', 'Failed to approve paysheet suggestion.');
                return $this->redirect(['index']);
            }
        }

        return $this->redirect(['index']);
    }

    /**
     * Reject a PaysheetSuggestion.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionReject($id)
    {
        $model = $this->findModel($id);

        if (!$model->canEdit()) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ['success' => false, 'message' => 'This suggestion cannot be rejected.'];
            }
            Yii::$app->session->setFlash('error', 'This suggestion cannot be rejected.');
            return $this->redirect(['index']);
        }

        // Get rejection reason from POST if provided
        $reason = Yii::$app->request->post('reason');

        if ($model->reject(Yii::$app->user->id, $reason)) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ['success' => true, 'message' => 'Paysheet suggestion rejected.'];
            }
            Yii::$app->session->setFlash('success', 'Paysheet suggestion rejected.');
        } else {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ['success' => false, 'message' => 'Failed to reject paysheet suggestion.'];
            }
            Yii::$app->session->setFlash('error', 'Failed to reject paysheet suggestion.');
        }

        return $this->redirect(['index']);
    }

    /**
     * Deletes an existing PaysheetSuggestion model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if (!$model->canDelete()) {
            Yii::$app->session->setFlash('error', 'This suggestion cannot be deleted.');
            return $this->redirect(['index']);
        }

        if ($model->delete()) {
            Yii::$app->session->setFlash('success', 'Paysheet suggestion deleted successfully.');
        } else {
            Yii::$app->session->setFlash('error', 'Failed to delete paysheet suggestion.');
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the PaysheetSuggestion model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return PaysheetSuggestion the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PaysheetSuggestion::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}

