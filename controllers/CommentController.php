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
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'create' => ['POST'],
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
        
        // Получаем article_id из POST для редиректа в случае ошибки
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
                // Приводим к int для корректного сравнения
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
                // Логируем ошибки валидации для отладки
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
}

