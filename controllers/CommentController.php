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
                        'roles' => ['@'], // Только авторизованным
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
            // Проверяем, что статья существует
            $article = Article::findOne($model->article_id);
            if (!$article) {
                throw new NotFoundHttpException('Article not found.');
            }

            // Устанавливаем пользователя, если не установлен
            if (empty($model->user_id)) {
                $model->user_id = Yii::$app->user->id;
            }

            // Проверяем, что пользователь может комментировать
            if ($model->user_id != Yii::$app->user->id) {
                throw new NotFoundHttpException('You do not have permission to create this comment.');
            }

            // Если есть parent_id, проверяем что родительский комментарий существует
            if ($model->parent_id) {
                $parent = Comment::findOne($model->parent_id);
                if (!$parent || $parent->article_id != $model->article_id) {
                    Yii::$app->session->setFlash('error', 'Invalid parent comment.');
                    return $this->redirect(['article/view', 'slug' => $article->slug]);
                }
            }

            // Устанавливаем статус: для авторов - сразу одобрен, для читателей - на модерации
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

        // Перенаправляем обратно на статью
        $article = Article::findOne($model->article_id);
        return $this->redirect(['article/view', 'slug' => $article->slug]);
    }
}

