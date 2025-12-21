<?php

namespace app\controllers;

use Yii;
use app\models\User;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * UserController handles user profile pages.
 */
class UserController extends Controller
{
    /**
     * Displays user profile.
     *
     * @param int $id
     * @return string
     * @throws NotFoundHttpException if the user cannot be found
     */
    public function actionProfile($id)
    {
        $user = User::findOne($id);
        
        if ($user === null) {
            throw new NotFoundHttpException('User not found.');
        }

        return $this->render('profile', [
            'user' => $user,
        ]);
    }
}

