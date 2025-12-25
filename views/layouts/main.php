<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\widgets\Alert;
use app\models\Category;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;
use yii\bootstrap5\ActiveForm;

AppAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? '']);
$this->registerMetaTag(['name' => 'keywords', 'content' => $this->params['meta_keywords'] ?? '']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/favicon.ico')]);
// Google Fonts - Montserrat
$this->registerLinkTag(['rel' => 'preconnect', 'href' => 'https://fonts.googleapis.com']);
$this->registerLinkTag(['rel' => 'preconnect', 'href' => 'https://fonts.gstatic.com', 'crossorigin' => '']);
$this->registerLinkTag(['rel' => 'stylesheet', 'href' => 'https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap']);
// Bootstrap Icons
$this->registerLinkTag(['rel' => 'stylesheet', 'href' => 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css']);
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
    // Отримуємо категорії для випадаючого меню
    try {
        $categories = Category::find()->orderBy(['name' => SORT_ASC])->all();
    } catch (\Exception $e) {
        $categories = [];
    }
    
    $categoryItems = [];
    foreach ($categories as $category) {
        $categoryItems[] = [
            'label' => Html::encode($category->name),
            'url' => ['/article/index', 'category_id' => $category->id]
        ];
    }
    
    NavBar::begin([
        'brandLabel' => 'MovieBlog',
        'brandUrl' => Yii::$app->homeUrl,
        'options' => ['class' => 'navbar-expand-lg navbar-dark']
    ]);
    
    //  навігаційне меню
    $navItems = [
        ['label' => 'Articles', 'url' => ['/article/index']],
    ];
    
    // Випадаюче меню "Категорії"
    if (!empty($categoryItems)) {
        $navItems[] = [
            'label' => 'Categories',
            'items' => $categoryItems,
            'dropdown' => true
        ];
    }
    
    // Посилання "Про блог"
    $navItems[] = ['label' => 'About', 'url' => ['/site/about']];
    
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav me-auto'],
        'items' => $navItems
    ]);
    
    // Форма пошуку
    $searchForm = ActiveForm::begin([
        'action' => ['/article/index'],
        'method' => 'get',
        'options' => ['class' => 'd-flex me-3']
    ]);
    echo Html::textInput('search', Yii::$app->request->get('search'), [
        'class' => 'form-control me-2',
        'type' => 'search',
        'placeholder' => 'Search articles...',
        'aria-label' => 'Search',
        'style' => 'width: 200px;'
    ]);
    echo Html::submitButton('Search', ['class' => 'btn btn-outline-light']);
    ActiveForm::end();
    
    // Блок користувача
    if (Yii::$app->user->isGuest) {
        // Для неавторизованих користувачів
        echo Html::a('Login', ['/site/login'], ['class' => 'btn btn-outline-light me-2']);
        echo Html::a('Register', ['/site/register'], ['class' => 'btn btn-primary']);
    } else {
        // Для авторизованих користувачів
        $user = Yii::$app->user->identity;
        echo Html::a(
            Html::encode($user->username),
            ['/user/profile', 'id' => $user->id],
            ['class' => 'btn btn-outline-light me-2']
        );
        echo Html::beginForm(['/site/logout'], 'post', ['class' => 'd-inline'])
            . Html::submitButton('Logout', ['class' => 'btn btn-outline-light'])
            . Html::endForm();
    }
    
    NavBar::end();
    ?>
</header>

<main id="main" class="flex-shrink-0" role="main">
    <div class="container">
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</main>

<footer id="footer" class="mt-auto py-3">
    <div class="container">
        <div class="row text-muted">
            <div class="col-md-6 text-center text-md-start">&copy; MovieBlog <?= date('Y') ?></div>
            <div class="col-md-6 text-center text-md-end"><?= Yii::powered() ?></div>
        </div>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
