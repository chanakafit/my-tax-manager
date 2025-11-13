<?php

/** @var yii\web\View $this */

/** @var string $content */

use app\assets\AppAsset;
use app\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;

AppAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? '']);
$this->registerMetaTag(['name' => 'keywords', 'content' => $this->params['meta_keywords'] ?? '']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/favicon.ico')]);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">
<head>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body class="d-flex flex-column h-100">
<?php $this->beginBody() ?>

<header id="header">
    <?php
    NavBar::begin([
            'brandLabel' => Yii::$app->name,
            'brandUrl' => Yii::$app->homeUrl,
            'options' => ['class' => 'navbar-expand-md navbar-dark bg-dark fixed-top']
    ]);
    echo Nav::widget([
            'options' => ['class' => 'navbar-nav'],
            'items' => [
                    ['label' => 'Dashboard', 'url' => ['/site/dashboard']],
                    [
                        'label' => 'Sales',
                        'items' => [
                            ['label' => 'Invoices', 'url' => ['/invoice/index']],
                            ['label' => 'Customers', 'url' => ['/customer/index']],
                            ['label' => 'Payment Terms', 'url' => ['/payment-term/index']],
                        ]
                    ],
                    [
                        'label' => 'Expenses',
                        'items' => [
                            ['label' => 'All Expenses', 'url' => ['/expense/index']],
                            [
                                'label' => 'Expense Suggestions <span class="badge bg-warning text-dark ms-1" id="expense-suggestions-badge" style="display:none;">0</span>',
                                'url' => ['/expense-suggestion/index'],
                                'encode' => false,
                            ],
                            ['label' => 'Categories', 'url' => ['/expense-category/index']],
                            ['label' => 'Vendors', 'url' => ['/vendor/index']],
                        ]
                    ],
                    [
                        'label' => 'Assets & Banking',
                        'items' => [
                            ['label' => 'Capital Assets', 'url' => ['/capital-asset/index']],
                            ['label' => 'Liabilities', 'url' => ['/liability/index']],
                            '<div class="dropdown-divider"></div>',
                            ['label' => 'Owner Bank Accounts', 'url' => ['/owner-bank-account/index']],
                            ['label' => 'Business Bank Accounts', 'url' => ['/bank-account/index']],
                            ['label' => 'Financial Transactions', 'url' => ['/financial-transaction/index']],
                        ]
                    ],
                    [
                        'label' => 'HR & Payroll',
                        'items' => [
                            ['label' => 'Employees', 'url' => ['/employee/index']],
                            ['label' => 'Paysheets', 'url' => ['/paysheet/index']],
                            ['label' => 'Paysheet Suggestions', 'url' => ['/paysheet-suggestion/index']],
                            '<div class="dropdown-divider"></div>',
                            ['label' => 'Calculate Paysheets', 'url' => ['/paysheet/calculate']],
                        ]
                    ],
                    [
                        'label' => 'Tax & Compliance',
                        'items' => [
                            ['label' => 'Tax Years Overview', 'url' => ['/tax-year/index']],
                            ['label' => 'Tax Records', 'url' => ['/tax-record/index']],
                            '<div class="dropdown-divider"></div>',
                            ['label' => 'Tax Return Submissions', 'url' => ['/tax-return/list']],
                            ['label' => 'Prepare Tax Return', 'url' => ['/tax-return/index']],
                        ]
                    ],
                    Yii::$app->user->isGuest
                            ? ['label' => 'Login', 'url' => ['/site/login']]
                            : '<li class="nav-item">'
                            . Html::beginForm(['/site/logout'])
                            . Html::submitButton(
                                    'Logout (' . Yii::$app->user->identity->username . ')',
                                    ['class' => 'nav-link btn btn-link logout']
                            )
                            . Html::endForm()
                            . '</li>'
            ]
    ]);
    NavBar::end();
    ?>
</header>

<main id="main" class="flex-shrink-0" role="main">
    <div class="container">
        <?php if (!empty($this->params['breadcrumbs'])): ?>
            <?= Breadcrumbs::widget(['links' => $this->params['breadcrumbs']]) ?>
        <?php endif ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</main>

<footer id="footer" class="mt-auto py-3 bg-light">
    <div class="container">
        <div class="row text-muted">
            <div class="col-md-6 text-center text-md-start">&copy; My Company <?= date('Y') ?></div>
            <div class="col-md-6 text-center text-md-end"><?= Yii::powered() ?></div>
        </div>
    </div>
</footer>

<?php if (!Yii::$app->user->isGuest): ?>
<script>
// Update expense suggestions badge
function updateExpenseSuggestionsBadge() {
    fetch('<?= \yii\helpers\Url::to(['/expense-suggestion/get-pending-suggestions']) ?>')
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('expense-suggestions-badge');
            if (badge && data.count > 0) {
                badge.textContent = data.count;
                badge.style.display = 'inline-block';
                badge.classList.add('bg-warning', 'text-dark');
            }
        })
        .catch(error => console.error('Error fetching expense suggestions:', error));
}

// Update on page load
document.addEventListener('DOMContentLoaded', function() {
    updateExpenseSuggestionsBadge();
    // Update every 5 minutes
    setInterval(updateExpenseSuggestionsBadge, 300000);
});
</script>
<?php endif; ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
