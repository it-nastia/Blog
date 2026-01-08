<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\SignupForm;
use app\models\Article;
use app\models\Category;
use app\models\Tag;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        // Масив зображень для Hero-секции 
        $heroImages = [
            'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=1920',
            'https://images.unsplash.com/photo-1536440136628-849c177e76a1?w=1920',
            'https://i.pinimg.com/736x/8a/cc/4b/8acc4b364fe67af871f5d09e0c5e06b0.jpg',
            'https://i.pinimg.com/1200x/55/61/7a/55617a4fc7d4dd5d9364d1c1f753e632.jpg',
            'https://i.pinimg.com/736x/1b/6e/28/1b6e28c16a121bf4391a29bd4d05b4d8.jpg',
            'https://i.pinimg.com/1200x/a3/30/c6/a330c692ca95485b8b107391fc7ace60.jpg',
            'https://i.pinimg.com/originals/55/ca/8d/55ca8d728efd3c2d4410a73a89008a71.gif',
            'https://i.pinimg.com/originals/d4/05/1d/d4051d8b9f3284ad8a5c609566b97fbf.gif',
            'https://i.pinimg.com/originals/39/11/67/3911670998d047b7cd509b495af00ffa.gif',
            'https://i.pinimg.com/originals/0e/7e/5f/0e7e5f02fa5c620c076d624d51d5f993.gif',
            ''
        ];
        
        $heroImageIndex = 3; 
        
        $heroImage = $heroImages[$heroImageIndex] ?? $heroImages[0];
        
        // Текст для Hero-секції
        $heroTitle = 'Stories behind the screen';
        $heroSubtitle = 'One blog to see them all';
        
        // Отримуємо 3 найпопулярніші статті (за кількістю переглядів)
        $popularArticles = Article::findPublished()
            ->with(['category', 'author', 'tags'])
            ->orderBy(['views' => SORT_DESC])
            ->limit(3)
            ->all();
        
        // Отримуємо 4 останні статті
        $latestArticles = Article::findPublished()
            ->with(['category', 'author', 'tags'])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(4)
            ->all();
        
        // Отримуємо всі категорії для каруселі
        $categories = Category::find()
            ->orderBy(['name' => SORT_ASC])
            ->all();
        
        // Отримуємо всі теги
        $tags = Tag::find()
            ->orderBy(['name' => SORT_ASC])
            ->all();
        
        // Початкове зображення для секції "Випадковий фільм"
        $randomMovieInitialImage = "https://i.pinimg.com/originals/3c/9f/d5/3c9fd5f19bd672cbfbcc537b9c896ce7.gif"; 
        
        return $this->render('index', [
            'heroImage' => $heroImage,
            'heroTitle' => $heroTitle,
            'heroSubtitle' => $heroSubtitle,
            'popularArticles' => $popularArticles,
            'latestArticles' => $latestArticles,
            'categories' => $categories,
            'tags' => $tags,
            'randomMovieInitialImage' => $randomMovieInitialImage,
        ]);
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionRegister()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new SignupForm();
        
        if ($model->load(Yii::$app->request->post())) {
            if ($model->signup()) {
                Yii::$app->session->setFlash('success', 'Thank you for registration. Please login with your credentials.');
                return $this->redirect(['login']);
            } else {
                Yii::$app->session->setFlash('error', 'Registration failed. Please check the form for errors.');
            }
        }

        return $this->render('register', [
            'model' => $model,
        ]);
    }

    /**
     * Contact page with form and email sending (used by functional tests)
     * @return string|\yii\web\Response
     */
    public function actionContact()
    {
        $model = new \app\models\ContactForm();

        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            return $this->refresh();
        }

        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Отримати випадкову статтю (для секції "Віпадковий фільм")
     * @return Response
     */
    public function actionRandomArticle()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        // Отримуємо випадкову опубліковану статтю з зображенням
        $articles = Article::findPublished()
            ->where(['not', ['image' => null]])
            ->andWhere(['!=', 'image', ''])
            ->all();
        
        if (empty($articles)) {
            return [
                'success' => false,
                'message' => 'No articles with images available.',
            ];
        }
        
        // Вибираємо випадкову статтю
        $randomArticle = $articles[array_rand($articles)];
        
        return [
            'success' => true,
            'article' => [
                'id' => $randomArticle->id,
                'title' => $randomArticle->title,
                'image' => $randomArticle->image,
                'slug' => $randomArticle->slug,
                'excerpt' => $randomArticle->getExcerpt(150),
            ],
        ];
    }

}
