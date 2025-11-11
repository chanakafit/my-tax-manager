<?php

namespace app\controllers;

use Yii;
use app\models\InvoiceLink;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;

class PublicInvoiceController extends Controller
{
    public $layout = 'public';

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['view', 'download'],
                        'roles' => ['?', '@'], // Allow both guests and authenticated users
                    ],
                ],
            ],
        ];
    }

    public function actionView($token)
    {
        $link = InvoiceLink::findOne(['token' => $token]);

        if (!$link || $link->isExpired()) {
            throw new NotFoundHttpException('The requested invoice link is invalid or has expired.');
        }

        return $this->render('view', [
            'model' => $link->invoice,
            'token' => $token
        ]);
    }

    public function actionDownload($token)
    {
        $link = InvoiceLink::findOne(['token' => $token]);

        if (!$link || $link->isExpired()) {
            throw new NotFoundHttpException('The requested invoice link is invalid or has expired.');
        }

        $pdfGenerator = new \app\components\InvoicePdfGenerator();
        return $pdfGenerator->generatePdf($link->invoice);
    }
}
