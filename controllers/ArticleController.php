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
                        'actions' => ['create', 'update', 'delete', 'manage'],
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
        $categorySlug = Yii::$app->request->get('category_slug');
        $tagSlug = Yii::$app->request->get('tag_slug');
        $search = Yii::$app->request->get('search');
        $sort = Yii::$app->request->get('sort', 'newest'); // По умолчанию: новейшие

        $query = Article::findPublished()
            ->with(['category', 'author', 'tags']);

        // Применяем сортировку
        switch ($sort) {
            case 'oldest':
                $query->orderBy(['created_at' => SORT_ASC]);
                break;
            case 'popular':
                $query->orderBy(['views' => SORT_DESC]);
                break;
            case 'title_asc':
                $query->orderBy(['title' => SORT_ASC]);
                break;
            case 'title_desc':
                $query->orderBy(['title' => SORT_DESC]);
                break;
            case 'newest':
            default:
                $query->orderBy(['created_at' => SORT_DESC]);
                break;
        }

        // Фільтрація за категорією (по slug)
        if ($categorySlug) {
            $query = Article::findByCategorySlug($categorySlug)
                ->with(['category', 'author', 'tags']);
            // Применяем сортировку снова после findByCategorySlug
            switch ($sort) {
                case 'oldest':
                    $query->orderBy(['created_at' => SORT_ASC]);
                    break;
                case 'popular':
                    $query->orderBy(['views' => SORT_DESC]);
                    break;
                case 'title_asc':
                    $query->orderBy(['title' => SORT_ASC]);
                    break;
                case 'title_desc':
                    $query->orderBy(['title' => SORT_DESC]);
                    break;
                case 'newest':
                default:
                    $query->orderBy(['created_at' => SORT_DESC]);
                    break;
            }
        }

        // Фільтрація за тегом (по slug)
        if ($tagSlug) {
            $query = Article::findByTagSlug($tagSlug)
                ->with(['category', 'author', 'tags']);
            // Применяем сортировку снова после findByTagSlug
            switch ($sort) {
                case 'oldest':
                    $query->orderBy(['created_at' => SORT_ASC]);
                    break;
                case 'popular':
                    $query->orderBy(['views' => SORT_DESC]);
                    break;
                case 'title_asc':
                    $query->orderBy(['title' => SORT_ASC]);
                    break;
                case 'title_desc':
                    $query->orderBy(['title' => SORT_DESC]);
                    break;
                case 'newest':
                default:
                    $query->orderBy(['created_at' => SORT_DESC]);
                    break;
            }
        }

        // Пошук за заголовком, вмістом та тегами
        if ($search) {
            // Используем подзапрос для поиска по тегам, чтобы избежать проблем с join'ами
            $tagSubquery = (new \yii\db\Query())
                ->select('article_id')
                ->from('article_tag')
                ->innerJoin('tags', 'article_tag.tag_id = tags.id')
                ->where(['like', 'tags.name', $search]);
            
            $query->andWhere(['or',
                ['like', 'title', $search],
                ['like', 'content', $search],
                ['in', 'id', $tagSubquery]
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
            'selectedCategorySlug' => $categorySlug,
            'selectedTagSlug' => $tagSlug,
            'search' => $search,
            'sort' => $sort,
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
                
                // Перевіряємо чи є returnUrl для перенаправлення
                $returnUrl = Yii::$app->request->post('returnUrl');
                if ($returnUrl) {
                    return $this->redirect($returnUrl);
                }
                
                return $this->redirect(['view', 'slug' => $model->slug]);
            } else {
                // Логуємо помилки валідації для діагностики тестів
                $errors = $model->getFirstErrors();
                $message = 'Failed to create article.';
                if (!empty($errors)) {
                    $message .= ' ' . implode(' ', $errors);
                }
                Yii::error('Article save failed: ' . json_encode($model->errors), __METHOD__);
                Yii::$app->session->setFlash('error', $message);
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
                
                // Перевіряємо чи є returnUrl для перенаправлення
                $returnUrl = Yii::$app->request->post('returnUrl');
                if ($returnUrl) {
                    return $this->redirect($returnUrl);
                }
                
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
        
        // Перевіряємо чи є returnUrl
        $returnUrl = Yii::$app->request->get('returnUrl');
        if ($returnUrl) {
            return $this->redirect($returnUrl);
        }
        
        return $this->redirect(['index']);
    }

    /**
     * Lists all Article models for administration (all statuses, only author's articles).
     * @return string
     */
    public function actionManage()
    {
        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => Article::find()
                ->where(['author_id' => Yii::$app->user->id])
                ->with(['category', 'tags'])
                ->orderBy(['created_at' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('manage', [
            'dataProvider' => $dataProvider,
        ]);
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

