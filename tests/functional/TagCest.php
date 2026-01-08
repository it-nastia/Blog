<?php

class TagCest
{
    public function _before(FunctionalTester $I)
    {
    }

    public function tagFiltersArticles(FunctionalTester $I)
    {
        // prepare author, category and tags
        $I->haveRecord('app\\models\\User', [
            'id' => 201,
            'username' => 'author201',
            'email' => 'author201@example.com',
            'password_hash' => 'test',
            'role' => 'author',
            'status' => 1,
        ]);

        $catId = $I->haveRecord('app\\models\\Category', [
            'name' => 'TagCat',
            'slug' => 'tagcat',
        ]);

        $tag1 = $I->haveRecord('app\\models\\Tag', [
            'name' => 'Tech',
            'slug' => 'tech',
        ]);

        $tag2 = $I->haveRecord('app\\models\\Tag', [
            'name' => 'Life',
            'slug' => 'life',
        ]);

        $article1 = $I->haveRecord('app\\models\\Article', [
            'title' => 'Tech Article',
            'slug' => 'tech-article',
            'content' => 'Tech content',
            'category_id' => $catId,
            'author_id' => 201,
            'status' => 'published',
            'views' => 0,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $article2 = $I->haveRecord('app\\models\\Article', [
            'title' => 'Life Article',
            'slug' => 'life-article',
            'content' => 'Life content',
            'category_id' => $catId,
            'author_id' => 201,
            'status' => 'published',
            'views' => 0,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        // link tags via junction model
        $I->haveRecord('app\\models\\ArticleTag', ['article_id' => $article1, 'tag_id' => $tag1]);
        $I->haveRecord('app\\models\\ArticleTag', ['article_id' => $article2, 'tag_id' => $tag2]);

        $I->amOnRoute('article/index', ['tag_slug' => 'tech']);
        $I->see('Tech Article');
        $I->dontSee('Life Article');
    }

    public function tagLinkFilters(FunctionalTester $I)
    {
        // create tag and article, click tag link from article view
        $I->haveRecord('app\\models\\User', [
            'id' => 202,
            'username' => 'author202',
            'email' => 'author202@example.com',
            'password_hash' => 'test',
            'role' => 'author',
            'status' => 1,
        ]);

        $catId = $I->haveRecord('app\\models\\Category', [
            'name' => 'TagSingle',
            'slug' => 'tagsingle',
        ]);

        $tag = $I->haveRecord('app\\models\\Tag', [
            'name' => 'Gadgets',
            'slug' => 'gadgets',
        ]);

        $article = $I->haveRecord('app\\models\\Article', [
            'title' => 'Gadget article',
            'slug' => 'gadget-article',
            'content' => 'Gadget content',
            'category_id' => $catId,
            'author_id' => 202,
            'status' => 'published',
            'views' => 0,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $I->haveRecord('app\\models\\ArticleTag', ['article_id' => $article, 'tag_id' => $tag]);

        $I->amOnRoute('article/view', ['slug' => 'gadget-article']);
        $I->see('#Gadgets');
        $I->click('#Gadgets');
        $I->see('Gadget article');
    }

    public function authorCanAccessTagIndex(\FunctionalTester $I)
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

        $I->haveRecord('app\\models\\Tag', [
            'name' => 'Test Tag',
            'slug' => 'test-tag',
        ]);

        $I->amLoggedInAs(100);
        $I->amOnRoute('tag/index');
        $I->see('Test Tag');
    }

    public function authorCanDeleteEmptyTag(\FunctionalTester $I)
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

        $tagId = $I->haveRecord('app\\models\\Tag', [
            'name' => 'To Delete',
            'slug' => 'to-delete',
        ]);

        $I->amLoggedInAs(100);
        $I->sendAjaxPostRequest('/index-test.php?r=tag%2Fdelete&id=' . $tagId, []);
        $I->amOnRoute('tag/index');
        $I->see('Tag deleted successfully');
        
        $deleted = $I->grabRecord('app\\models\\Tag', ['id' => $tagId]);
        \PHPUnit\Framework\Assert::assertNull($deleted);
    }

    public function readerCannotAccessTagManagement(\FunctionalTester $I)
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
        $I->amOnRoute('tag/index');
        $I->seeResponseCodeIs(403);
    }
}

