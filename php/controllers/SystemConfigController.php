<?php

namespace app\controllers;

use app\models\SystemConfig;
use app\models\SystemConfigSearch;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * SystemConfigController implements the CRUD actions for SystemConfig model.
 */
class SystemConfigController extends BaseController
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
     * Lists all SystemConfig models.
     *
     * @return string
     */
    public function actionIndex(): string
    {
        $searchModel = new SystemConfigSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays configurations by category
     *
     * @param string|null $category
     * @return string
     */
    public function actionByCategory(?string $category = null): string
    {
        $searchModel = new SystemConfigSearch();
        $params = $this->request->queryParams;

        if ($category) {
            $params['SystemConfigSearch']['category'] = $category;
        }

        $dataProvider = $searchModel->search($params);

        $categories = SystemConfig::find()
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->column();

        return $this->render('by-category', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'categories' => $categories,
            'selectedCategory' => $category,
        ]);
    }

    /**
     * Displays a single SystemConfig model.
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
     * Creates a new SystemConfig model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|Response
     */
    public function actionCreate()
    {
        $model = new SystemConfig();

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::info("Create POST received", __METHOD__);

                // For new configs, we need full validation
                if ($model->save()) {
                    Yii::info("Successfully created config: {$model->config_key}", __METHOD__);
                    Yii::$app->session->setFlash('success', 'Configuration created successfully.');
                    SystemConfig::clearCache();
                    return $this->redirect(['view', 'id' => $model->id]);
                } else {
                    Yii::error("Failed to create config. Errors: " . print_r($model->errors, true), __METHOD__);
                    Yii::$app->session->setFlash('error', 'Failed to create configuration: ' . implode(', ', $model->getErrorSummary(false)));
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing SystemConfig model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                Yii::info("Update POST received for ID {$id}: {$model->config_key}", __METHOD__);
                Yii::info("New value: {$model->config_value}", __METHOD__);

                // Use save(false) to skip validation of required fields that aren't being edited
                // Only config_value is being updated
                if ($model->save(false)) {
                    Yii::info("Successfully saved config ID {$id}", __METHOD__);
                    Yii::$app->session->setFlash('success', 'Configuration updated successfully.');
                    SystemConfig::clearCache(); // Clear cache after save
                    return $this->redirect(['view', 'id' => $model->id]);
                } else {
                    Yii::error("Failed to save config ID {$id}", __METHOD__);
                    Yii::$app->session->setFlash('error', 'Failed to save configuration.');
                }
            } else {
                Yii::error("Failed to load POST data for config ID {$id}", __METHOD__);
                Yii::$app->session->setFlash('error', 'Failed to load form data.');
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing SystemConfig model.
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

        if (!$model->is_editable) {
            Yii::$app->session->setFlash('error', 'This configuration cannot be deleted.');
            return $this->redirect(['index']);
        }

        $model->delete();
        Yii::$app->session->setFlash('success', 'Configuration deleted successfully.');

        return $this->redirect(['index']);
    }

    /**
     * Bulk update configurations
     *
     * @return string|Response
     */
    public function actionBulkUpdate()
    {
        $category = Yii::$app->request->get('category');

        $configs = SystemConfig::find()
            ->where($category ? ['category' => $category] : [])
            ->orderBy(['category' => SORT_ASC, 'config_key' => SORT_ASC])
            ->all();

        Yii::info("actionBulkUpdate called. Method: " . Yii::$app->request->method, __METHOD__);
        Yii::info("Is POST: " . (Yii::$app->request->isPost ? 'YES' : 'NO'), __METHOD__);

        // Dump ALL POST data for debugging
        $allPost = Yii::$app->request->post();
        Yii::info("ALL POST data: " . print_r($allPost, true), __METHOD__);

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post('SystemConfig', []);
            $success = true;
            $errors = [];
            $updated = 0;

            // Debug logging
            Yii::info("Bulk update POST data received. SystemConfig keys: " . count($post), __METHOD__);
            Yii::info("Total configs to check: " . count($configs), __METHOD__);
            Yii::info("POST SystemConfig data: " . print_r($post, true), __METHOD__);

            foreach ($configs as $config) {
                if (isset($post[$config->id])) {
                    Yii::info("Processing config ID {$config->id}: {$config->config_key}", __METHOD__);

                    if (!$config->is_editable) {
                        Yii::info("Config {$config->config_key} is not editable, skipping", __METHOD__);
                        continue;
                    }

                    $oldValue = $config->config_value;
                    $newValue = $post[$config->id];

                    Yii::info("Config {$config->config_key}: old='{$oldValue}', new='{$newValue}'", __METHOD__);

                    // Only update if value actually changed
                    if ($oldValue !== $newValue) {
                        $config->config_value = $newValue;

                        // Try to save
                        if ($config->save(false)) {
                            $updated++;
                            Yii::info("Successfully saved {$config->config_key}", __METHOD__);
                        } else {
                            $success = false;
                            $errorMsg = $config->hasErrors() ? implode(', ', $config->getErrorSummary(false)) : 'Unknown save error';
                            $errors[] = "{$config->config_key}: {$errorMsg}";
                            Yii::error("Failed to save {$config->config_key}: {$errorMsg}", __METHOD__);
                        }
                    } else {
                        Yii::info("No change for {$config->config_key}, skipping", __METHOD__);
                    }
                }
            }

            // Clear cache after updates
            if ($updated > 0) {
                SystemConfig::clearCache();
                Yii::info("Cleared config cache after updating {$updated} configs", __METHOD__);
            }

            if ($success && $updated > 0) {
                Yii::$app->session->setFlash('success', "Successfully updated {$updated} configuration(s).");
            } elseif ($updated > 0) {
                Yii::$app->session->setFlash('warning', "Updated {$updated} configuration(s), but some failed: " . implode('; ', $errors));
            } elseif ($updated === 0 && empty($errors)) {
                Yii::$app->session->setFlash('info', 'No changes were made.');
            } else {
                Yii::$app->session->setFlash('error', 'Failed to update configurations: ' . implode('; ', $errors));
            }

            return $this->redirect(['bulk-update', 'category' => $category]);
        }

        $categories = SystemConfig::find()
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->column();

        return $this->render('bulk-update', [
            'configs' => $configs,
            'categories' => $categories,
            'selectedCategory' => $category,
        ]);
    }

    /**
     * Clear configuration cache
     *
     * @return Response
     */
    public function actionClearCache(): Response
    {
        SystemConfig::clearCache();
        Yii::$app->session->setFlash('success', 'Configuration cache cleared successfully.');
        return $this->redirect(['index']);
    }

    /**
     * Test form for debugging
     */
    public function actionTestForm()
    {
        return $this->render('test-form');
    }

    /**
     * Manage signature settings (name, title, and upload image)
     *
     * @return string|Response
     */
    public function actionSignature()
    {
        $signatureImage = SystemConfig::findOne(['config_key' => 'signatureImage']);
        $signatureName = SystemConfig::findOne(['config_key' => 'signatureName']);
        $signatureTitle = SystemConfig::findOne(['config_key' => 'signatureTitle']);

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $success = true;
            $errors = [];

            // Handle signature name
            if (isset($post['signature_name']) && $signatureName) {
                $signatureName->config_value = $post['signature_name'];
                if (!$signatureName->save(false)) {
                    $success = false;
                    $errors[] = 'Failed to save signature name';
                }
            }

            // Handle signature title
            if (isset($post['signature_title']) && $signatureTitle) {
                $signatureTitle->config_value = $post['signature_title'];
                if (!$signatureTitle->save(false)) {
                    $success = false;
                    $errors[] = 'Failed to save signature title';
                }
            }

            // Handle file upload
            $uploadedFile = \yii\web\UploadedFile::getInstanceByName('signature_image_file');
            if ($uploadedFile) {
                // Create uploads directory if it doesn't exist
                $uploadDir = Yii::getAlias('@app/web/uploads/signatures');
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                // Generate unique filename
                $filename = 'signature_' . time() . '.' . $uploadedFile->extension;
                $filePath = $uploadDir . '/' . $filename;

                // Save file
                if ($uploadedFile->saveAs($filePath)) {
                    // Delete old signature file if exists
                    if ($signatureImage->config_value) {
                        $oldFile = Yii::getAlias('@app/web') . $signatureImage->config_value;
                        if (file_exists($oldFile)) {
                            @unlink($oldFile);
                        }
                    }

                    // Save relative path in database
                    $signatureImage->config_value = '/uploads/signatures/' . $filename;
                    if (!$signatureImage->save(false)) {
                        $success = false;
                        $errors[] = 'Failed to save signature image path';
                    }
                } else {
                    $success = false;
                    $errors[] = 'Failed to upload signature image';
                }
            }

            // Clear cache
            if ($success) {
                SystemConfig::clearCache();
                Yii::$app->session->setFlash('success', 'Signature settings updated successfully.');
            } else {
                Yii::$app->session->setFlash('error', 'Failed to update signature settings: ' . implode(', ', $errors));
            }

            return $this->refresh();
        }

        return $this->render('signature', [
            'signatureImage' => $signatureImage,
            'signatureName' => $signatureName,
            'signatureTitle' => $signatureTitle,
        ]);
    }

    /**
     * Finds the SystemConfig model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return SystemConfig the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id): SystemConfig
    {
        if (($model = SystemConfig::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested configuration does not exist.');
    }
}

