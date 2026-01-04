<?php

namespace app\controllers;

use Yii;
use app\models\Comment;
use app\models\Article;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;

/**
 * CommentController handles comment creation and management.
 */
class CommentController extends Controller
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
                        'actions' => ['create'],
                        'roles' => ['@'], // Тільки авторизованим
                    ],
                    [
                        'allow' => true,
                        'actions' => ['index', 'view', 'update', 'delete'],
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            // Тільки автори можуть переглядати, оновлювати та видаляти коментарі
                            return !Yii::$app->user->isGuest && 
                                   Yii::$app->user->identity->isAuthor();
                        },
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'create' => ['POST'],
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Creates a new comment.
     * If creation is successful, redirects back to the article.
     * @return Response
     */
    public function actionCreate()
    {
        $model = new Comment();
        
        // Отримуємо article_id з POST для перенаправлення в разі помилки
        $postData = Yii::$app->request->post();
        $articleId = $postData['Comment']['article_id'] ?? null;

        if ($model->load($postData)) {
            // Перевіряємо, що стаття існує
            $article = Article::findOne($model->article_id);
            if (!$article) {
                Yii::$app->session->setFlash('error', 'Article not found.');
                if ($articleId) {
                    $article = Article::findOne($articleId);
                    if ($article) {
                        return $this->redirect(['article/view', 'slug' => $article->slug]);
                    }
                }
                return $this->goHome();
            }

            // Встановлюємо користувача, якщо не встановлено
            if (empty($model->user_id)) {
                $model->user_id = Yii::$app->user->id;
            }

            // Перевіряємо, що користувач може коментувати
            if ($model->user_id != Yii::$app->user->id) {
                Yii::$app->session->setFlash('error', 'You do not have permission to create this comment.');
                return $this->redirect(['article/view', 'slug' => $article->slug]);
            }

            // Нормалізуємо parent_id: якщо пустий або 0, встановлюємо null
            if (empty($model->parent_id)) {
                $model->parent_id = null;
            }

            // Якщо є parent_id, перевіряємо що батьківський коментар існує
            if ($model->parent_id) {
                $parent = Comment::findOne($model->parent_id);
                if (!$parent) {
                    Yii::$app->session->setFlash('error', 'Parent comment not found.');
                    return $this->redirect(['article/view', 'slug' => $article->slug]);
                }
                // Приводимо до int для коректного порівняння
                $parentArticleId = (int)$parent->article_id;
                $currentArticleId = (int)$model->article_id;
                if ($parentArticleId !== $currentArticleId) {
                    Yii::$app->session->setFlash('error', 'Invalid parent comment.');
                    return $this->redirect(['article/view', 'slug' => $article->slug]);
                }
            }

            // Встановлюємо статус: для авторів - одразу схвалено, для читачів - на модерації
            $user = Yii::$app->user->identity;
            if ($user->isAuthor()) {
                $model->status = Comment::STATUS_APPROVED;
            } else {
                $model->status = Comment::STATUS_PENDING;
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 
                    $user->isAuthor() 
                        ? 'Comment posted successfully.' 
                        : 'Comment submitted and is pending moderation.'
                );
            } else {
                // Логуємо помилки валідації для відладки
                $errors = $model->getFirstErrors();
                $errorMessage = 'Failed to post comment.';
                if (!empty($errors)) {
                    $errorMessage .= ' ' . implode(' ', $errors);
                }
                Yii::$app->session->setFlash('error', $errorMessage);
                Yii::error('Comment save failed: ' . json_encode($model->errors), __METHOD__);
            }
        } else {
            Yii::$app->session->setFlash('error', 'Invalid comment data.');
            Yii::error('Comment load failed. POST data: ' . json_encode($postData), __METHOD__);
        }

        // Перенаправляємо назад на статтю
        if ($articleId) {
            $article = Article::findOne($articleId);
            if ($article) {
                return $this->redirect(['article/view', 'slug' => $article->slug]);
            }
        }
        
        return $this->goHome();
    }

    /**
     * Updates an existing Comment model.
     * Updates only the status (for moderation).
     * If update is successful, the browser will be redirected to returnUrl if provided.
     * @param int $id
     * @return string|Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = Comment::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('Comment not found.');
        }

        // Перевіряємо, чи є коментар до статті автора
        $article = Article::findOne($model->article_id);
        if ($article === null || $article->author_id !== Yii::$app->user->id) {
            throw new NotFoundHttpException('You do not have permission to update this comment.');
        }

        // Оновлюємо тільки статус
        $post = Yii::$app->request->post('Comment');
        if ($post && isset($post['status'])) {
            $model->status = $post['status'];
            if ($model->save(false)) {
                Yii::$app->session->setFlash('success', 'Comment status updated successfully.');
                
                // Перевіряємо чи є returnUrl для перенаправлення
                $returnUrl = Yii::$app->request->post('returnUrl');
                if ($returnUrl) {
                    return $this->redirect($returnUrl);
                }

                // Якщо немає returnUrl, перенаправляємо на список коментарів або статтю
                return $this->redirect(['index']);
            } else {
                $errors = $model->getFirstErrors();
                $errorMessage = !empty($errors) ? implode(', ', $errors) : 'Failed to update comment status.';
                Yii::$app->session->setFlash('error', $errorMessage);
            }
        }

        // Рендеримо форму для GET запиту
        $returnUrl = Yii::$app->request->get('returnUrl');
        return $this->render('update', [
            'model' => $model,
            'returnUrl' => $returnUrl,
        ]);
    }

    /**
     * Deletes an existing Comment model.
     * If deletion is successful, the browser will be redirected to returnUrl if provided.
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = Comment::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('Comment not found.');
        }

        // Перевіряємо, чи є коментар до статті автора
        $article = Article::findOne($model->article_id);
        if ($article === null || $article->author_id !== Yii::$app->user->id) {
            throw new NotFoundHttpException('You do not have permission to delete this comment.');
        }

        $model->delete();
        Yii::$app->session->setFlash('success', 'Comment deleted successfully.');

        // Перевіряємо чи є returnUrl
        $returnUrl = Yii::$app->request->get('returnUrl');
        if ($returnUrl) {
            return $this->redirect($returnUrl);
        }

        // Якщо немає returnUrl, перенаправляємо на статтю
        if ($article) {
            return $this->redirect(['article/view', 'slug' => $article->slug]);
        }

        return $this->goHome();
    }

    /**
     * Lists all comments for articles written by the current author.
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => Comment::find()
                ->joinWith('article')
                ->where(['articles.author_id' => Yii::$app->user->id])
                ->with(['user', 'article'])
                ->orderBy(['created_at' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Comment model.
     * @param int $id
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = Comment::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('Comment not found.');
        }

        // Перевіряємо, чи є коментар до статті автора
        $article = Article::findOne($model->article_id);
        if ($article === null || $article->author_id !== Yii::$app->user->id) {
            throw new NotFoundHttpException('You do not have permission to view this comment.');
        }

        return $this->render('view', [
            'model' => $model,
        ]);
    }
}

