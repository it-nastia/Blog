<?php

namespace tests\unit\models;

use app\models\Article;
use app\models\Category;
use app\models\User;

class ArticleTest extends \Codeception\Test\Unit
{
    public function testValidation()
    {
        $article = new Article();
        // Empty required fields
        verify($article->validate())->false();
        verify($article->errors)->arrayHasKey('title');
        verify($article->errors)->arrayHasKey('content');
        verify($article->errors)->arrayHasKey('category_id');
        verify($article->errors)->arrayHasKey('author_id');
    }

    public function testSlugGenerationAndIncrement()
    {
        // Create minimal user and category
        $user = User::findOne(['username' => 'admin']) ?: new User([
            'username' => 'admin',
            'email' => 'admin@example.com',
            'auth_key' => 'test100key',
            'role' => 'author',
            'status' => User::STATUS_ACTIVE,
        ]);
        if ($user->isNewRecord) { $user->setPassword('admin'); $user->save(false); }

        $category = new Category(['name' => 'UnitCat', 'slug' => 'unitcat']);
        $category->save(false);

        $article = new Article();
        $article->title = 'My Test Article';
        $article->content = 'Some content';
        $article->category_id = $category->id;
        $article->author_id = $user->id;
        $article->status = Article::STATUS_PUBLISHED;

        verify($article->save())->true();
        verify($article->slug)->notEmpty();

        $initialViews = $article->views;
        $article->incrementViews();
        $article->refresh();
        verify($article->views)->equals($initialViews + 1);
    }

    public function testArticleStatus()
    {
        $user = User::findOne(['username' => 'admin']) ?: new User([
            'username' => 'admin',
            'email' => 'admin@example.com',
            'auth_key' => 'test100key',
            'role' => 'author',
            'status' => User::STATUS_ACTIVE,
        ]);
        if ($user->isNewRecord) { $user->setPassword('admin'); $user->save(false); }

        $category = new Category(['name' => 'StatusCat', 'slug' => 'statuscat']);
        $category->save(false);

        $draft = new Article();
        $draft->title = 'Draft Article';
        $draft->content = 'Content';
        $draft->category_id = $category->id;
        $draft->author_id = $user->id;
        $draft->status = Article::STATUS_DRAFT;
        $draft->save(false);

        verify($draft->isDraft())->true();
        verify($draft->isPublished())->false();

        $published = new Article();
        $published->title = 'Published Article';
        $published->content = 'Content';
        $published->category_id = $category->id;
        $published->author_id = $user->id;
        $published->status = Article::STATUS_PUBLISHED;
        $published->save(false);

        verify($published->isPublished())->true();
        verify($published->isDraft())->false();
    }

    public function testArticleRelations()
    {
        $user = User::findOne(['username' => 'admin']) ?: new User([
            'username' => 'admin',
            'email' => 'admin@example.com',
            'auth_key' => 'test100key',
            'role' => 'author',
            'status' => User::STATUS_ACTIVE,
        ]);
        if ($user->isNewRecord) { $user->setPassword('admin'); $user->save(false); }

        $category = new Category(['name' => 'RelCat', 'slug' => 'relcat']);
        $category->save(false);

        $article = new Article();
        $article->title = 'Relation Test';
        $article->content = 'Content';
        $article->category_id = $category->id;
        $article->author_id = $user->id;
        $article->status = Article::STATUS_PUBLISHED;
        $article->save(false);

        verify($article->category)->notEmpty();
        verify($article->category->name)->equals('RelCat');
        verify($article->author)->notEmpty();
        verify($article->author->username)->equals('admin');
    }


    public function testFindPublished()
    {
        $user = User::findOne(['username' => 'admin']) ?: new User([
            'username' => 'admin',
            'email' => 'admin@example.com',
            'auth_key' => 'test100key',
            'role' => 'author',
            'status' => User::STATUS_ACTIVE,
        ]);
        if ($user->isNewRecord) { $user->setPassword('admin'); $user->save(false); }

        $category = new Category(['name' => 'FindCat', 'slug' => 'findcat']);
        $category->save(false);

        $published = new Article();
        $published->title = 'Published Find';
        $published->content = 'Content';
        $published->category_id = $category->id;
        $published->author_id = $user->id;
        $published->status = Article::STATUS_PUBLISHED;
        $published->save(false);

        $draft = new Article();
        $draft->title = 'Draft Find';
        $draft->content = 'Content';
        $draft->category_id = $category->id;
        $draft->author_id = $user->id;
        $draft->status = Article::STATUS_DRAFT;
        $draft->save(false);

        $publishedArticles = Article::findPublished()->all();
        verify(count($publishedArticles))->greaterThan(0);
        
        $found = false;
        foreach ($publishedArticles as $art) {
            if ($art->id === $published->id) {
                $found = true;
                break;
            }
        }
        verify($found)->true();
    }

}
