<?php

namespace tests\unit\models;

use app\models\Category;

class CategoryTest extends \Codeception\Test\Unit
{
    public function testSlugGenerationAndUniqueness()
    {
        // Create a category with an explicit slug to create a collision
        $cat1 = new Category(['name' => 'Unique Cat A']);
        $cat1->slug = 'same-slug';
        verify($cat1->save(false))->true();
        verify($cat1->slug)->equals('same-slug');

        // Create another category whose generated slug would be 'same-slug'
        $cat2 = new Category(['name' => 'Same Slug', ]);
        verify($cat2->save(false))->true();
        // The beforeSave should detect the existing 'same-slug' and generate a different one
        verify($cat2->slug)->notEquals($cat1->slug);
    }

    public function testCategoryValidation()
    {
        $category = new Category();
        verify($category->validate())->false();
        verify($category->errors)->arrayHasKey('name');

        $category->name = 'Valid Category';
        verify($category->validate())->true();
    }

    public function testCategoryUniqueName()
    {
        $cat1 = new Category(['name' => 'Unique Name']);
        verify($cat1->save(false))->true();

        $cat2 = new Category(['name' => 'Unique Name']);
        verify($cat2->validate())->false();
        verify($cat2->errors)->arrayHasKey('name');
    }

    public function testCategoryArticlesRelation()
    {
        $user = \app\models\User::findOne(['username' => 'admin']) ?: new \app\models\User([
            'username' => 'admin',
            'email' => 'admin@example.com',
            'auth_key' => 'test100key',
            'role' => 'author',
            'status' => \app\models\User::STATUS_ACTIVE,
        ]);
        if ($user->isNewRecord) { $user->setPassword('admin'); $user->save(false); }

        $category = new Category(['name' => 'Articles Cat']);
        $category->save(false);

        $article1 = new \app\models\Article([
            'title' => 'Article 1',
            'content' => 'Content',
            'category_id' => $category->id,
            'author_id' => $user->id,
            'status' => \app\models\Article::STATUS_PUBLISHED,
        ]);
        $article1->save(false);

        $article2 = new \app\models\Article([
            'title' => 'Article 2',
            'content' => 'Content',
            'category_id' => $category->id,
            'author_id' => $user->id,
            'status' => \app\models\Article::STATUS_PUBLISHED,
        ]);
        $article2->save(false);

        verify($category->getArticles()->count())->equals(2);
        verify($category->getArticlesCount())->equals(2);
    }

}
