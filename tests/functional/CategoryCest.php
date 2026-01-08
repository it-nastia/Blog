<?php

class CategoryCest
{
    public function _before(FunctionalTester $I)
    {
    }

    public function categoryFiltersArticles(FunctionalTester $I)
    {
        // prepare author and categories
        $I->haveRecord('app\\models\\User', [
            'id' => 200,
            'username' => 'author200',
            'email' => 'author200@example.com',
            'password_hash' => 'test',
            'role' => 'author',
            'status' => 1,
        ]);

        $catA = $I->haveRecord('app\\models\\Category', [
            'name' => 'Cat A',
            'slug' => 'cat-a',
        ]);

        $catB = $I->haveRecord('app\\models\\Category', [
            'name' => 'Cat B',
            'slug' => 'cat-b',
        ]);

        $I->haveRecord('app\\models\\Article', [
            'title' => 'Article A',
            'slug' => 'article-a',
            'content' => 'Content A',
            'category_id' => $catA,
            'author_id' => 200,
            'status' => 'published',
            'views' => 0,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $I->haveRecord('app\\models\\Article', [
            'title' => 'Article B',
            'slug' => 'article-b',
            'content' => 'Content B',
            'category_id' => $catB,
            'author_id' => 200,
            'status' => 'published',
            'views' => 0,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $I->amOnRoute('article/index', ['category_slug' => 'cat-a']);
        $I->see('Article A');
        $I->dontSee('Article B');
    }

    public function categoryLinkFilters(FunctionalTester $I)
    {
        // create category and article and check clicking the category link filters
        $I->haveRecord('app\\models\\User', [
            'id' => 201,
            'username' => 'author201',
            'email' => 'author201@example.com',
            'password_hash' => 'test',
            'role' => 'author',
            'status' => 1,
        ]);

        $cat = $I->haveRecord('app\\models\\Category', [
            'name' => 'Guides',
            'slug' => 'guides-filter',
        ]);

        $I->haveRecord('app\\models\\Article', [
            'title' => 'Article in guides',
            'slug' => 'article-guides',
            'content' => 'Guides content',
            'category_id' => $cat,
            'author_id' => 201,
            'status' => 'published',
            'views' => 0,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $I->amOnRoute('article/index');
        $I->seeLink('Guides');
        $I->click('Guides');
        $I->see('Article in guides');
    }

    public function authorCanAccessCategoryIndex(\FunctionalTester $I)
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

        $I->haveRecord('app\\models\\Category', [
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);

        $I->amLoggedInAs(100);
        $I->amOnRoute('category/index');
        $I->see('Test Category');
    }

    public function authorCanDeleteEmptyCategory(\FunctionalTester $I)
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
            'name' => 'To Delete',
            'slug' => 'to-delete',
        ]);

        $I->amLoggedInAs(100);
        $I->sendAjaxPostRequest('/index-test.php?r=category%2Fdelete&id=' . $categoryId, []);
        $I->amOnRoute('category/index');
        $I->see('Category deleted successfully');
        
        $deleted = $I->grabRecord('app\\models\\Category', ['id' => $categoryId]);
        \PHPUnit\Framework\Assert::assertNull($deleted);
    }

    public function readerCannotAccessCategoryManagement(\FunctionalTester $I)
    {
        $hash = \Yii::$app->getSecurity()->generatePasswordHash('password');
        $I->haveRecord('app\\models\\User', [
            'id' => 200,
            'username' => 'reader',
            'email' => 'reader@example.com',
            'password_hash' => $hash,
            'role' => 'reader',
            'status' => 1,
        ]);

        $I->amLoggedInAs(200);
        $I->amOnRoute('category/index');
        $I->seeResponseCodeIs(403);
    }
}

