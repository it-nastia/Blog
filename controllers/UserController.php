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
                        'actions' => ['profile', 'update', 'category-create', 'category-update', 'category-delete', 'tag-create', 'tag-update', 'tag-delete', 'article-create', 'article-update', 'article-delete', 'comment-update', 'comment-delete'],
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
            // Коментарі до статей автора
            $stats['comments'] = Comment::find()
                ->joinWith('article')
                ->where(['articles.author_id' => $user->id])
                ->count();
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

    /**
     * Creates a new article from profile page.
     * @return Response
     */
    public function actionArticleCreate()
    {
        if (!Yii::$app->user->identity->isAuthor()) {
            throw new NotFoundHttpException('Only authors can create articles.');
        }

        $model = new Article();
        $model->author_id = Yii::$app->user->id;
        $model->status = Article::STATUS_DRAFT;

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
            } else {
                $errors = $model->getFirstErrors();
                $errorMessage = !empty($errors) ? implode(', ', $errors) : 'Failed to create article.';
                Yii::$app->session->setFlash('error', $errorMessage);
            }
        } else {
            $errors = $model->getFirstErrors();
            if (!empty($errors)) {
                $errorMessage = implode(', ', $errors);
                Yii::$app->session->setFlash('error', $errorMessage);
            }
        }

        return $this->redirect(['profile', 'id' => Yii::$app->user->id, '#' => 'articles-section']);
    }

    /**
     * Updates an existing article from profile page.
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionArticleUpdate($id)
    {
        if (!Yii::$app->user->identity->isAuthor()) {
            throw new NotFoundHttpException('Only authors can update articles.');
        }

        $model = Article::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('Article not found.');
        }

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
            } else {
                $errors = $model->getFirstErrors();
                $errorMessage = !empty($errors) ? implode(', ', $errors) : 'Failed to update article.';
                Yii::$app->session->setFlash('error', $errorMessage);
            }
        }

        return $this->redirect(['profile', 'id' => Yii::$app->user->id, '#' => 'articles-section']);
    }

    /**
     * Deletes an existing article from profile page.
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionArticleDelete($id)
    {
        if (!Yii::$app->user->identity->isAuthor()) {
            throw new NotFoundHttpException('Only authors can delete articles.');
        }

        $model = Article::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('Article not found.');
        }

        // Перевіряємо, чи є користувач автором статті
        if ($model->author_id !== Yii::$app->user->id) {
            throw new NotFoundHttpException('You do not have permission to delete this article.');
        }

        $model->delete();
        Yii::$app->session->setFlash('success', 'Article deleted successfully.');

        return $this->redirect(['profile', 'id' => Yii::$app->user->id, '#' => 'articles-section']);
    }

    /**
     * Updates an existing comment from profile page.
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionCommentUpdate($id)
    {
        if (!Yii::$app->user->identity->isAuthor()) {
            throw new NotFoundHttpException('Only authors can update comments.');
        }

        $model = Comment::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('Comment not found.');
        }

        // Перевіряємо, чи є коментар до статті автора
        $article = Article::findOne($model->article_id);
        if ($article === null || $article->author_id !== Yii::$app->user->id) {
            throw new NotFoundHttpException('You do not have permission to update this comment.');
        }

        // Обновляємо тільки статус
        $post = Yii::$app->request->post('Comment');
        if ($post && isset($post['status'])) {
            $model->status = $post['status'];
            if ($model->save(false)) {
                Yii::$app->session->setFlash('success', 'Comment status updated successfully.');
            } else {
                $errors = $model->getFirstErrors();
                $errorMessage = !empty($errors) ? implode(', ', $errors) : 'Failed to update comment status.';
                Yii::$app->session->setFlash('error', $errorMessage);
            }
        }

        return $this->redirect(['profile', 'id' => Yii::$app->user->id, '#' => 'comments-section']);
    }

    /**
     * Deletes an existing comment from profile page.
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionCommentDelete($id)
    {
        if (!Yii::$app->user->identity->isAuthor()) {
            throw new NotFoundHttpException('Only authors can delete comments.');
        }

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

        return $this->redirect(['profile', 'id' => Yii::$app->user->id, '#' => 'comments-section']);
    }
}

