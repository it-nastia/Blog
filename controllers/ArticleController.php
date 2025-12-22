<?php

namespace app\controllers;

use Yii;
use app\models\Article;
use app\models\Category;
use app\models\Tag;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;
use yii\helpers\ArrayHelper;

/**
 * ArticleController implements the CRUD actions for Article model.
 */
class ArticleController extends Controller
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
                        'actions' => ['index', 'view'],
                        'roles' => ['?', '@'], // Доступно всім (гостям та авторизованим)
                    ],
                    [
                        'allow' => true,
                        'actions' => ['create', 'update', 'delete'],
                        'roles' => ['@'], // Тільки авторизованим
                        'matchCallback' => function ($rule, $action) {
                            // Перевіряємо, чи є користувач автором
                            return !Yii::$app->user->isGuest && 
                                   Yii::$app->user->identity->isAuthor();
                        },
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all published articles.
     * @return string
     */
    public function actionIndex()
    {
        $categoryId = Yii::$app->request->get('category_id');
        $tagId = Yii::$app->request->get('tag_id');
        $search = Yii::$app->request->get('search');

        $query = Article::findPublished()
            ->with(['category', 'author', 'tags'])
            ->orderBy(['created_at' => SORT_DESC]);

        // Фільтрація за категорією
        if ($categoryId) {
            $query->andWhere(['category_id' => $categoryId]);
        }

        // Фільтрація за тегом
        if ($tagId) {
            $query = Article::findByTag($tagId)
                ->with(['category', 'author', 'tags'])
                ->orderBy(['created_at' => SORT_DESC]);
        }

        // Пошук за заголовком та вмістом
        if ($search) {
            $query->andWhere(['or',
                ['like', 'title', $search],
                ['like', 'content', $search]
            ]);
        }

        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        // Отримуємо категорії для фільтра
        try {
            $categories = Category::find()->orderBy(['name' => SORT_ASC])->all();
        } catch (\Exception $e) {
            $categories = [];
            Yii::error('Error loading categories: ' . $e->getMessage());
        }

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'categories' => $categories,
            'selectedCategoryId' => $categoryId,
            'selectedTagId' => $tagId,
            'search' => $search,
        ]);
    }

    /**
     * Displays a single Article model.
     * @param string $slug
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($slug)
    {
        $model = Article::find()
            ->where(['slug' => $slug, 'status' => Article::STATUS_PUBLISHED])
            ->with(['category', 'author', 'tags', 'comments'])
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException('The requested article does not exist.');
        }

        // Збільшуємо лічильник переглядів
        $model->incrementViews();

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new Article model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|Response
     */
    public function actionCreate()
    {
        $model = new Article();
        $model->author_id = Yii::$app->user->id;
        $model->status = Article::STATUS_DRAFT; // За замовчуванням чернетка

        if ($model->load(Yii::$app->request->post())) {
            // Обробка тегів
            $tagIds = Yii::$app->request->post('Article')['tagIds'] ?? [];
            
            if ($model->save()) {
                // Зберігаємо теги
                $model->unlinkAll('tags', true);
                if (!empty($tagIds)) {
                    foreach ($tagIds as $tagId) {
                        $tag = Tag::findOne($tagId);
                        if ($tag) {
                            $model->link('tags', $tag);
                        }
                    }
                }

                Yii::$app->session->setFlash('success', 'Article created successfully.');
                return $this->redirect(['view', 'slug' => $model->slug]);
            }
        }

        $categories = ArrayHelper::map(Category::find()->all(), 'id', 'name');
        $tags = Tag::find()->orderBy(['name' => SORT_ASC])->all();

        return $this->render('create', [
            'model' => $model,
            'categories' => $categories,
            'tags' => $tags,
        ]);
    }

    /**
     * Updates an existing Article model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id
     * @return string|Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // Перевіряємо, чи є користувач автором статті
        if ($model->author_id !== Yii::$app->user->id) {
            throw new NotFoundHttpException('You do not have permission to update this article.');
        }

        if ($model->load(Yii::$app->request->post())) {
            // Обробка тегів
            $tagIds = Yii::$app->request->post('Article')['tagIds'] ?? [];
            
            if ($model->save()) {
                // Зберігаємо теги
                $model->unlinkAll('tags', true);
                if (!empty($tagIds)) {
                    foreach ($tagIds as $tagId) {
                        $tag = Tag::findOne($tagId);
                        if ($tag) {
                            $model->link('tags', $tag);
                        }
                    }
                }

                Yii::$app->session->setFlash('success', 'Article updated successfully.');
                return $this->redirect(['view', 'slug' => $model->slug]);
            }
        }

        $categories = ArrayHelper::map(Category::find()->all(), 'id', 'name');
        $tags = Tag::find()->orderBy(['name' => SORT_ASC])->all();
        
        // Отримуємо поточні теги статті
        $selectedTagIds = ArrayHelper::getColumn($model->tags, 'id');

        return $this->render('update', [
            'model' => $model,
            'categories' => $categories,
            'tags' => $tags,
            'selectedTagIds' => $selectedTagIds,
        ]);
    }

    /**
     * Deletes an existing Article model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        // Перевіряємо, чи є користувач автором статті
        if ($model->author_id !== Yii::$app->user->id) {
            throw new NotFoundHttpException('You do not have permission to delete this article.');
        }

        $model->delete();

        Yii::$app->session->setFlash('success', 'Article deleted successfully.');
        return $this->redirect(['index']);
    }

    /**
     * Finds the Article model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id
     * @return Article the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Article::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested article does not exist.');
    }
}

