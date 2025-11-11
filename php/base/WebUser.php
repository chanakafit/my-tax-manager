<?php

namespace app\base;

use app\helpers\RequestHelper;
use Yii;
use yii\web\User;

class WebUser extends User
{

    public function getIsGuest(): bool
    {
        // Check if the user has access to the current subdomain
        $subdomain = RequestHelper::getSubDomain();
        if ($subdomain) {
            $allowedTenants = Yii::$app->session->get('allowedDomains');
            if (empty($allowedTenants) || !in_array($subdomain, $allowedTenants)) {
                return true; // User has no access to this subdomain, treat as guest
            }
        }

        // Call the parent implementation for standard guest check
        return parent::getIsGuest();
    }

    public function can($permissionName, $params = [], $allowCaching = true)
    {
        return parent::can($permissionName, $params, $allowCaching);
    }

}