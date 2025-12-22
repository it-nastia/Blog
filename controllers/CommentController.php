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

        if ($model->load(Yii::$app->request->post())) {
            // Перевіряємо, що стаття існує
            $article = Article::findOne($model->article_id);
            if (!$article) {
                throw new NotFoundHttpException('Article not found.');
            }

            // Встановлюємо користувача, якщо не встановлено
            if (empty($model->user_id)) {
                $model->user_id = Yii::$app->user->id;
            }

            // Перевіряємо, що користувач може коментувати
            if ($model->user_id != Yii::$app->user->id) {
                throw new NotFoundHttpException('You do not have permission to create this comment.');
            }

            // Якщо є parent_id, перевіряємо що батьківський коментар існує
            if ($model->parent_id) {
                $parent = Comment::findOne($model->parent_id);
                if (!$parent || $parent->article_id != $model->article_id) {
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
                Yii::$app->session->setFlash('error', 'Failed to post comment.');
            }
        } else {
            Yii::$app->session->setFlash('error', 'Invalid comment data.');
        }

        // Перенаправляємо назад на статтю
        $article = Article::findOne($model->article_id);
        return $this->redirect(['article/view', 'slug' => $article->slug]);
    }
}

