<?php

namespace app\controllers;

use Yii;
use app\models\TaxPayment;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

class TaxPaymentController extends BaseController
{
    public function behaviors()
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

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            $model->uploadedFile = \yii\web\UploadedFile::getInstance($model, 'uploadedFile');

            if ($model->validate()) {
                try {
                    if ($model->save()) {
                        Yii::$app->session->setFlash('success', 'Tax payment updated successfully.');
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                } catch (\Exception $e) {
                    Yii::error('Error updating tax payment: ' . $e->getMessage());
                    Yii::$app->session->setFlash('error', 'Error updating tax payment. Please try again.');
                }
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionDownloadReceipt($id)
    {
        $model = $this->findModel($id);

        if (!$model->receipt_file) {
            throw new NotFoundHttpException('No receipt file found.');
        }

        $filePath = Yii::getAlias('@webroot/' . $model->receipt_file);
        if (!file_exists($filePath)) {
            throw new NotFoundHttpException('Receipt file not found.');
        }

        return Yii::$app->response->sendFile($filePath);
    }

    protected function findModel($id)
    {
        if (($model = TaxPayment::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested tax payment does not exist.');
    }
}
