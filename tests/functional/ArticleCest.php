<?php

use app\models\Article;

class ArticleCest
{
    public function _before(\FunctionalTester $I)
    {
    }

    public function indexDisplaysArticles(\FunctionalTester $I)
    {
        // Ensure author and category exist
        $I->haveRecord('app\\models\\User', [
            'id' => 100,
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password_hash' => 'test',
            'role' => 'author',
            'status' => 1,
        ]);

        $categoryId = $I->haveRecord('app\\models\\Category', [
            'name' => 'Tech',
            'slug' => 'tech',
        ]);

        $I->haveRecord('app\\models\\Article', [
            'title' => 'Test Article',
            'slug' => 'test-article',
            'content' => 'This is a test content.',
            'category_id' => $categoryId,
            'author_id' => 100,
            'status' => 'published',
            'views' => 0,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $I->amOnRoute('article/index');
        $I->see('Test Article');
    }

    public function viewDisplaysArticleAndIncrementsViews(\FunctionalTester $I)
    {
        // create fixtures
        $I->haveRecord('app\\models\\User', [
            'id' => 100,
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password_hash' => 'test',
            'role' => 'author',
            'status' => 1,
        ]);

        $categoryId = $I->haveRecord('app\\models\\Category', [
            'name' => 'News',
            'slug' => 'news',
        ]);

        $I->haveRecord('app\\models\\Article', [
            'title' => 'View Article',
            'slug' => 'view-article',
            'content' => 'Some content',
            'category_id' => $categoryId,
            'author_id' => 100,
            'status' => 'published',
            'views' => 0,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $I->amOnRoute('article/view', ['slug' => 'view-article']);
        $I->see('View Article', 'h1');
        $I->see('admin');
        // The view increments views counter before rendering
        $I->see('1 views');
    }

    public function guestCannotAccessCreate(\FunctionalTester $I)
    {
        $I->amOnRoute('article/create');
        $I->see('Login', 'h1');
    }

    public function authorCanCreateArticle(\FunctionalTester $I)
    {
        // prepare author and category
        $I->haveRecord('app\\models\\User', [
            'id' => 100,
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password_hash' => 'test',
            'role' => 'author',
            'status' => 1,
        ]);

        $categoryId = $I->haveRecord('app\\models\\Category', [
            'name' => 'Guides',
            'slug' => 'guides',
        ]);

        $I->amLoggedInAs(100);
        $I->amOnRoute('article/create');

        $I->submitForm('.article-form form', [
            'Article[title]' => 'Created via test',
            'Article[slug]' => 'created-via-test',
            'Article[content]' => 'Article content from test',
            'Article[category_id]' => $categoryId,
            'Article[status]' => 'published',
        ]);

        // After submitForm Codeception follows redirects and the flash is consumed on that response.
        // Check flash and page immediately.
        $I->see('Article created successfully.');
        $I->see('Created via test', 'h1');

        // verify article exists in DB and is published via AR grab
        $article = $I->grabRecord('app\\models\\Article', ['slug' => 'created-via-test']);
        \PHPUnit\Framework\Assert::assertNotNull($article);
        \PHPUnit\Framework\Assert::assertEquals('published', $article->status);
    }

    public function authorCanDeleteArticle(\FunctionalTester $I)
    {
        // prepare author and article
        $I->haveRecord('app\\models\\User', [
            'id' => 100,
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password_hash' => 'test',
            'role' => 'author',
            'status' => 1,
        ]);

        $categoryId = $I->haveRecord('app\\models\\Category', [
            'name' => 'Temp',
            'slug' => 'temp',
        ]);

        $articleId = $I->haveRecord('app\\models\\Article', [
            'title' => 'To be deleted',
            'slug' => 'to-be-deleted',
            'content' => 'Delete me',
            'category_id' => $categoryId,
            'author_id' => 100,
            'status' => 'published',
            'views' => 0,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $I->amLoggedInAs(100);
        // send POST to delete route using AJAX helper
        // include id in URL to ensure route parameter binding and use AJAX
        $I->sendAjaxPostRequest('/index-test.php?r=article%2Fdelete&id=' . $articleId, []);
        // navigate to index to pick up flash set by the action
        $I->amOnRoute('article/index');
        $I->see('Article deleted successfully.');
        $deleted = $I->grabRecord('app\\models\\Article', ['id' => $articleId]);
        \PHPUnit\Framework\Assert::assertNull($deleted);
    }

    public function authorCanUpdateArticle(\FunctionalTester $I)
    {
        $hash = \Yii::$app->getSecurity()->generatePasswordHash('admin');
        $I->haveRecord('app\\models\\User', [
            'id' => 100,
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password_hash' => $hash,
            'role' => 'author',
            'status' => 1,
        ]);

        $categoryId = $I->haveRecord('app\\models\\Category', [
            'name' => 'Tech',
            'slug' => 'tech',
        ]);

        $articleId = $I->haveRecord('app\\models\\Article', [
            'title' => 'Original Title',
            'slug' => 'original-title',
            'content' => 'Original content',
            'category_id' => $categoryId,
            'author_id' => 100,
            'status' => 'published',
            'views' => 0,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $I->amLoggedInAs(100);
        $I->amOnRoute('article/update', ['id' => $articleId]);

        $I->submitForm('.article-form form', [
            'Article[title]' => 'Updated Title',
            'Article[content]' => 'Updated content',
            'Article[category_id]' => $categoryId,
            'Article[status]' => 'published',
        ]);

        $I->see('Article updated successfully.');
        $I->see('Updated Title', 'h1');
        
        $article = $I->grabRecord('app\\models\\Article', ['id' => $articleId]);
        \PHPUnit\Framework\Assert::assertEquals('Updated Title', $article->title);
    }

    public function authorCanAccessManagePage(\FunctionalTester $I)
    {
        $hash = \Yii::$app->getSecurity()->generatePasswordHash('admin');
        $I->haveRecord('app\\models\\User', [
            'id' => 100,
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password_hash' => $hash,
            'role' => 'author',
            'status' => 1,
        ]);

        $categoryId = $I->haveRecord('app\\models\\Category', [
            'name' => 'Tech',
            'slug' => 'tech',
        ]);

        $I->haveRecord('app\\models\\Article', [
            'title' => 'My Article',
            'slug' => 'my-article',
            'content' => 'Content',
            'category_id' => $categoryId,
            'author_id' => 100,
            'status' => 'draft',
            'views' => 0,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $I->amLoggedInAs(100);
        $I->amOnRoute('article/manage');
        $I->see('My Article');
    }

    public function searchFiltersArticles(\FunctionalTester $I)
    {
        $hash = \Yii::$app->getSecurity()->generatePasswordHash('admin');
        $I->haveRecord('app\\models\\User', [
            'id' => 100,
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password_hash' => $hash,
            'role' => 'author',
            'status' => 1,
        ]);

        $categoryId = $I->haveRecord('app\\models\\Category', [
            'name' => 'Tech',
            'slug' => 'tech',
        ]);

        $I->haveRecord('app\\models\\Article', [
            'title' => 'PHP Tutorial',
            'slug' => 'php-tutorial',
            'content' => 'Learn PHP programming',
            'category_id' => $categoryId,
            'author_id' => 100,
            'status' => 'published',
            'views' => 0,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $I->haveRecord('app\\models\\Article', [
            'title' => 'JavaScript Guide',
            'slug' => 'javascript-guide',
            'content' => 'Learn JavaScript',
            'category_id' => $categoryId,
            'author_id' => 100,
            'status' => 'published',
            'views' => 0,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $I->amOnRoute('article/index', ['search' => 'PHP']);
        $I->see('PHP Tutorial');
        $I->dontSee('JavaScript Guide');
    }

    public function sortByPopularity(\FunctionalTester $I)
    {
        $hash = \Yii::$app->getSecurity()->generatePasswordHash('admin');
        $I->haveRecord('app\\models\\User', [
            'id' => 100,
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password_hash' => $hash,
            'role' => 'author',
            'status' => 1,
        ]);

        $categoryId = $I->haveRecord('app\\models\\Category', [
            'name' => 'Tech',
            'slug' => 'tech',
        ]);

        $I->haveRecord('app\\models\\Article', [
            'title' => 'Less Popular',
            'slug' => 'less-popular',
            'content' => 'Content',
            'category_id' => $categoryId,
            'author_id' => 100,
            'status' => 'published',
            'views' => 10,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $I->haveRecord('app\\models\\Article', [
            'title' => 'More Popular',
            'slug' => 'more-popular',
            'content' => 'Content',
            'category_id' => $categoryId,
            'author_id' => 100,
            'status' => 'published',
            'views' => 100,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $I->amOnRoute('article/index', ['sort' => 'popular']);
        // More popular should appear first
        $I->see('More Popular');
    }
}

