<?php

namespace tests\unit\models;

use app\models\Tag;

class TagTest extends \Codeception\Test\Unit
{
    public function testSlugGenerationUniqueness()
    {
        // Use names that slugify to the same base to trigger uniqueness suffix
        // Create a tag with explicit slug to force a collision
        $tag1 = new Tag(['name' => 'TagA']);
        $tag1->slug = 'same-slug';
        verify($tag1->save(false))->true();
        verify($tag1->slug)->equals('same-slug');

        // Create another tag whose generated slug would be 'same-slug'
        $tag2 = new Tag(['name' => 'Same Slug']);
        verify($tag2->save(false))->true();
        verify($tag2->slug)->notEquals($tag1->slug);
    }

    public function testTagValidation()
    {
        $tag = new Tag();
        verify($tag->validate())->false();
        verify($tag->errors)->arrayHasKey('name');

        $tag->name = 'Valid Tag';
        verify($tag->validate())->true();
    }

    public function testTagUniqueName()
    {
        $tag1 = new Tag(['name' => 'Unique Tag Name']);
        verify($tag1->save(false))->true();

        $tag2 = new Tag(['name' => 'Unique Tag Name']);
        verify($tag2->validate())->false();
        verify($tag2->errors)->arrayHasKey('name');
    }

    public function testTagArticlesRelation()
    {
        $user = \app\models\User::findOne(['username' => 'admin']) ?: new \app\models\User([
            'username' => 'admin',
            'email' => 'admin@example.com',
            'auth_key' => 'test100key',
            'role' => 'author',
            'status' => \app\models\User::STATUS_ACTIVE,
        ]);
        if ($user->isNewRecord) { $user->setPassword('admin'); $user->save(false); }

        $category = new \app\models\Category(['name' => 'Tag Cat']);
        $category->save(false);

        $tag = new Tag(['name' => 'Articles Tag']);
        $tag->save(false);

        $article1 = new \app\models\Article([
            'title' => 'Tagged Article 1',
            'content' => 'Content',
            'category_id' => $category->id,
            'author_id' => $user->id,
            'status' => \app\models\Article::STATUS_PUBLISHED,
        ]);
        $article1->save(false);

        $article2 = new \app\models\Article([
            'title' => 'Tagged Article 2',
            'content' => 'Content',
            'category_id' => $category->id,
            'author_id' => $user->id,
            'status' => \app\models\Article::STATUS_PUBLISHED,
        ]);
        $article2->save(false);

        $link1 = new \app\models\ArticleTag(['article_id' => $article1->id, 'tag_id' => $tag->id]);
        $link1->save(false);
        $link2 = new \app\models\ArticleTag(['article_id' => $article2->id, 'tag_id' => $tag->id]);
        $link2->save(false);

        $tag->refresh();
        verify($tag->getArticles()->count())->equals(2);
        verify($tag->getArticlesCount())->equals(2);
    }

}
