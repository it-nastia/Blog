<?php

namespace tests\unit\models;

use app\models\Article;
use app\models\Tag;
use app\models\ArticleTag;
use app\models\Category;
use app\models\User;

class ArticleTagTest extends \Codeception\Test\Unit
{
    public function testArticleTagLinking()
    {
        $user = User::findOne(['username' => 'admin']) ?: new User([
            'username' => 'admin',
            'email' => 'admin@example.com',
            'auth_key' => 'test100key',
            'role' => 'author',
            'status' => User::STATUS_ACTIVE,
        ]);
        if ($user->isNewRecord) { $user->setPassword('admin'); $user->save(false); }

        $cat = new Category(['name' => 'TaggingCat']); $cat->save(false);

        $article = new Article(['title' => 'TagArticle', 'slug' => 'tagarticle', 'content' => 'c', 'category_id' => $cat->id, 'author_id' => $user->id, 'status' => Article::STATUS_PUBLISHED]); $article->save(false);

        $tag = new Tag(['name' => 'LinkTag']); $tag->save(false);

        $link = new ArticleTag(['article_id' => $article->id, 'tag_id' => $tag->id]);
        verify($link->save())->true();

        $tag->refresh();
        verify($tag->getArticlesCount())->equals(1);
    }

    public function testMultipleTagsOnArticle()
    {
        $user = User::findOne(['username' => 'admin']) ?: new User([
            'username' => 'admin',
            'email' => 'admin@example.com',
            'auth_key' => 'test100key',
            'role' => 'author',
            'status' => User::STATUS_ACTIVE,
        ]);
        if ($user->isNewRecord) { $user->setPassword('admin'); $user->save(false); }

        $cat = new Category(['name' => 'MultiTagCat']);
        $cat->save(false);

        $article = new Article(['title' => 'MultiTag Article', 'slug' => 'multitag-article', 'content' => 'c', 'category_id' => $cat->id, 'author_id' => $user->id, 'status' => Article::STATUS_PUBLISHED]);
        $article->save(false);

        $tag1 = new Tag(['name' => 'Tag1']);
        $tag1->save(false);
        $tag2 = new Tag(['name' => 'Tag2']);
        $tag2->save(false);
        $tag3 = new Tag(['name' => 'Tag3']);
        $tag3->save(false);

        $link1 = new ArticleTag(['article_id' => $article->id, 'tag_id' => $tag1->id]);
        $link1->save(false);
        $link2 = new ArticleTag(['article_id' => $article->id, 'tag_id' => $tag2->id]);
        $link2->save(false);
        $link3 = new ArticleTag(['article_id' => $article->id, 'tag_id' => $tag3->id]);
        $link3->save(false);

        $article->refresh();
        verify($article->getTags()->count())->equals(3);
    }

}
