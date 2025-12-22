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
                        'actions' => ['profile', 'update', 'category-create', 'category-update', 'category-delete', 'tag-create', 'tag-update', 'tag-delete'],
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

        // Обробка створення категорії
        $categoryModel = new Category();
        if (Yii::$app->request->post('Category') && Yii::$app->request->post('create-category')) {
            if ($categoryModel->load(Yii::$app->request->post()) && $categoryModel->save()) {
                Yii::$app->session->setFlash('success', 'Category created successfully.');
                return $this->refresh();
            } else {
                Yii::$app->session->setFlash('error', 'Failed to create category.');
            }
        }

        return $this->render('profile', [
            'user' => $user,
            'model' => $model,
            'stats' => $stats,
            'categoryModel' => $categoryModel,
        ]);
    }

    /**
     * Creates a new category from profile page.
     * @return Response
     */
    public function actionCategoryCreate()
    {
        if (!Yii::$app->user->identity->isAuthor()) {
            throw new NotFoundHttpException('Only authors can create categories.');
        }

        $model = new Category();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Category created successfully.');
        } else {
            $errors = $model->getFirstErrors();
            $errorMessage = !empty($errors) ? implode(', ', $errors) : 'Failed to create category.';
            Yii::$app->session->setFlash('error', $errorMessage);
        }

        return $this->redirect(['profile', 'id' => Yii::$app->user->id, '#' => 'categories-section']);
    }

    /**
     * Updates an existing category from profile page.
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionCategoryUpdate($id)
    {
        if (!Yii::$app->user->identity->isAuthor()) {
            throw new NotFoundHttpException('Only authors can update categories.');
        }

        $model = Category::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('Category not found.');
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Category updated successfully.');
        } else {
            $errors = $model->getFirstErrors();
            $errorMessage = !empty($errors) ? implode(', ', $errors) : 'Failed to update category.';
            Yii::$app->session->setFlash('error', $errorMessage);
        }

        return $this->redirect(['profile', 'id' => Yii::$app->user->id, '#' => 'categories-section']);
    }

    /**
     * Deletes an existing category from profile page.
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionCategoryDelete($id)
    {
        if (!Yii::$app->user->identity->isAuthor()) {
            throw new NotFoundHttpException('Only authors can delete categories.');
        }

        $model = Category::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('Category not found.');
        }

        // Перевіряємо, чи є статті в цій категорії
        if ($model->getArticlesCount() > 0) {
            Yii::$app->session->setFlash('error', 'Cannot delete category with articles. Please remove or reassign articles first.');
        } else {
            $model->delete();
            Yii::$app->session->setFlash('success', 'Category deleted successfully.');
        }

        return $this->redirect(['profile', 'id' => Yii::$app->user->id, '#' => 'categories-section']);
    }

    /**
     * Creates a new tag from profile page.
     * @return Response
     */
    public function actionTagCreate()
    {
        if (!Yii::$app->user->identity->isAuthor()) {
            throw new NotFoundHttpException('Only authors can create tags.');
        }

        $model = new Tag();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Tag created successfully.');
        } else {
            $errors = $model->getFirstErrors();
            $errorMessage = !empty($errors) ? implode(', ', $errors) : 'Failed to create tag.';
            Yii::$app->session->setFlash('error', $errorMessage);
        }

        return $this->redirect(['profile', 'id' => Yii::$app->user->id, '#' => 'tags-section']);
    }

    /**
     * Updates an existing tag from profile page.
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionTagUpdate($id)
    {
        if (!Yii::$app->user->identity->isAuthor()) {
            throw new NotFoundHttpException('Only authors can update tags.');
        }

        $model = Tag::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('Tag not found.');
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Tag updated successfully.');
        } else {
            $errors = $model->getFirstErrors();
            $errorMessage = !empty($errors) ? implode(', ', $errors) : 'Failed to update tag.';
            Yii::$app->session->setFlash('error', $errorMessage);
        }

        return $this->redirect(['profile', 'id' => Yii::$app->user->id, '#' => 'tags-section']);
    }

    /**
     * Deletes an existing tag from profile page.
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionTagDelete($id)
    {
        if (!Yii::$app->user->identity->isAuthor()) {
            throw new NotFoundHttpException('Only authors can delete tags.');
        }

        $model = Tag::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('Tag not found.');
        }

        // Перевіряємо, чи є статті з цим тегом
        if ($model->getArticlesCount() > 0) {
            Yii::$app->session->setFlash('error', 'Cannot delete tag with articles. Please remove tag from articles first.');
        } else {
            $model->delete();
            Yii::$app->session->setFlash('success', 'Tag deleted successfully.');
        }

        return $this->redirect(['profile', 'id' => Yii::$app->user->id, '#' => 'tags-section']);
    }
}

