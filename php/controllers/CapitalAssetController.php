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
                        'delete-allowance' => ['POST'],
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
                Yii::$app->session->setFlash('success', 'Capital asset added successfully. You can now add capital allowances manually.');
                return $this->redirect(['view', 'id' => $model->id]);
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
        $percentage = Yii::$app->request->post('percentage');

        if (!$taxYear) {
            Yii::$app->session->setFlash('error', 'Tax year is required.');
            return $this->redirect(['view', 'id' => $id]);
        }

        if (!$percentage || $percentage <= 0 || $percentage > 100) {
            Yii::$app->session->setFlash('error', 'Valid percentage (1-100) is required.');
            return $this->redirect(['view', 'id' => $id]);
        }

        // Check if allowance already exists for this tax year
        $existingAllowance = CapitalAllowance::findOne([
            'capital_asset_id' => $asset->id,
            'tax_year' => $taxYear
        ]);

        if ($existingAllowance) {
            Yii::$app->session->setFlash('error', 'A capital allowance for tax year ' . $taxYear . ' already exists for this asset. Please delete the existing one first if you want to change it.');
            return $this->redirect(['view', 'id' => $id]);
        }

        $allowance = $asset->calculateAllowance($taxYear, $percentage);
        if ($allowance && $allowance->save()) {
            // Update the asset's written down value
            $asset->current_written_down_value = $allowance->written_down_value;
            if ($asset->save(false)) {
                Yii::$app->session->setFlash('success', 'Capital allowance added successfully. Written down value updated to ' .
                    Yii::$app->formatter->asCurrency($allowance->written_down_value, 'LKR'));
            } else {
                Yii::$app->session->setFlash('warning', 'Capital allowance saved but failed to update written down value.');
            }
        } else {
            $errors = $allowance ? implode(', ', $allowance->getFirstErrors()) : 'Unknown error';
            Yii::$app->session->setFlash('error', 'Failed to add capital allowance: ' . $errors);
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionDeleteAllowance($id)
    {
        $allowance = CapitalAllowance::findOne($id);

        if (!$allowance) {
            throw new NotFoundHttpException('The requested capital allowance does not exist.');
        }

        $asset = $allowance->capitalAsset;
        $assetId = $asset->id;

        // Delete the allowance
        if ($allowance->delete()) {
            // Recalculate written down value for the asset
            $this->recalculateWrittenDownValue($asset);

            Yii::$app->session->setFlash('success', 'Capital allowance deleted successfully. Written down value recalculated.');
        } else {
            Yii::$app->session->setFlash('error', 'Failed to delete capital allowance.');
        }

        return $this->redirect(['view', 'id' => $assetId]);
    }

    /**
     * Recalculate written down value after allowance deletion
     *
     * @param CapitalAsset $asset
     */
    protected function recalculateWrittenDownValue($asset)
    {
        // Get all remaining allowances ordered by year
        $allowances = CapitalAllowance::find()
            ->where(['capital_asset_id' => $asset->id])
            ->orderBy(['year_number' => SORT_ASC])
            ->all();

        // Reset to purchase cost
        $currentWDV = $asset->purchase_cost;

        // Recalculate each allowance's written down value
        foreach ($allowances as $index => $allowance) {
            // Update year number
            $allowance->year_number = $index + 1;

            // Recalculate allowance amount based on percentage of ORIGINAL ASSET VALUE (purchase cost)
            $allowance->allowance_amount = $asset->purchase_cost * ($allowance->percentage_claimed / 100);

            // Calculate new WDV (subtract this allowance from current WDV)
            $currentWDV = $currentWDV - $allowance->allowance_amount;
            $allowance->written_down_value = $currentWDV;

            $allowance->save(false);
        }

        // Update asset's current written down value
        $asset->current_written_down_value = $currentWDV;
        $asset->save(false);
    }



    public function actionDispose($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            $model->status = 'disposed';

            // Validate only disposal-specific fields
            if (empty($model->disposal_date)) {
                $model->addError('disposal_date', 'Disposal date is required.');
            }

            // Save with validation disabled to avoid required field issues
            // The model is already loaded from database with all required fields populated
            if (!$model->hasErrors() && $model->save(false)) {
                Yii::$app->session->setFlash('success', 'Asset marked as disposed successfully.');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                // Show validation errors
                if ($model->hasErrors()) {
                    $errors = implode('<br>', $model->getErrorSummary(true));
                    Yii::$app->session->setFlash('error', 'Failed to dispose asset: ' . $errors);
                } else {
                    Yii::$app->session->setFlash('error', 'Failed to dispose asset. Please try again.');
                }
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
