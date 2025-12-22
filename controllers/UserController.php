<?php

namespace app\controllers;

use Yii;
use app\models\User;
use app\models\ProfileForm;
use app\models\Article;
use app\models\Category;
use app\models\Tag;
use app\models\Comment;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\web\Response;

/**
 * UserController handles user profile pages.
 */
class UserController extends Controller
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
                        'actions' => ['profile', 'update'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Displays user profile.
     *
     * @param int $id
     * @return string
     * @throws NotFoundHttpException if the user cannot be found
     */
    public function actionProfile($id)
    {
        // Користувач може бачити тільки свій профіль
        if (Yii::$app->user->id != $id) {
            throw new NotFoundHttpException('You can only view your own profile.');
        }

        $user = User::findOne($id);
        
        if ($user === null) {
            throw new NotFoundHttpException('User not found.');
        }

        // Отримуємо статистику для авторів
        $stats = [];
        if ($user->isAuthor()) {
            $stats['articles'] = Article::find()->where(['author_id' => $user->id])->count();
            $stats['categories'] = Category::find()->count();
            $stats['tags'] = Tag::find()->count();
            $stats['comments'] = Comment::find()->count();
        }

        $model = new ProfileForm();
        $model->loadUserData();

        // Обробка збереження профілю
        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Profile updated successfully.');
                return $this->refresh();
            } else {
                Yii::$app->session->setFlash('error', 'Failed to update profile. Please check the form for errors.');
            }
        }

        return $this->render('profile', [
            'user' => $user,
            'model' => $model,
            'stats' => $stats,
        ]);
    }
}

