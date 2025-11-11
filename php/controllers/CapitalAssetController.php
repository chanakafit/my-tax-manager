<?php

namespace app\controllers;

use Yii;
use app\models\CapitalAsset;
use app\models\CapitalAllowance;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;

class CapitalAssetController extends BaseController
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

    public function actionIndex()
    {
        $assets = CapitalAsset::find()->orderBy(['purchase_date' => SORT_DESC])->all();

        return $this->render('index', [
            'assets' => $assets,
        ]);
    }

    public function actionCreate()
    {
        $model = new CapitalAsset();

        if ($model->load(Yii::$app->request->post())) {
            $model->uploadedFile = UploadedFile::getInstance($model, 'uploadedFile');

            if ($model->save()) {
                // After saving the asset, calculate and save initial allowance
                $allowance = $model->calculateAllowance($model->initial_tax_year);
                if ($allowance && $allowance->save() && $model->save()) {
                    Yii::$app->session->setFlash('success', 'Capital asset added successfully.');
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        $allowances = CapitalAllowance::find()
            ->where(['capital_asset_id' => $id])
            ->orderBy(['year_number' => SORT_ASC])
            ->all();

        return $this->render('view', [
            'model' => $model,
            'allowances' => $allowances,
        ]);
    }

    public function actionCalculateAllowance($id)
    {
        $asset = $this->findModel($id);
        $taxYear = Yii::$app->request->post('taxYear');

        if (!$taxYear) {
            Yii::$app->session->setFlash('error', 'Tax year is required.');
            return $this->redirect(['view', 'id' => $id]);
        }

        $allowance = $asset->calculateAllowance($taxYear);
        if ($allowance && $allowance->save() && $asset->save()) {
            Yii::$app->session->setFlash('success', 'Capital allowance calculated and saved successfully.');
        } else {
            Yii::$app->session->setFlash('error', 'Failed to calculate capital allowance. Asset might have used all allowances.');
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionDispose($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            $model->status = 'disposed';
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Asset marked as disposed successfully.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('dispose', [
            'model' => $model,
        ]);
    }

    protected function findModel($id)
    {
        if (($model = CapitalAsset::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested capital asset does not exist.');
    }
}
