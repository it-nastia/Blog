<?php

namespace tests\unit\models;

use app\models\Comment;
use app\models\Article;
use app\models\User;
use app\models\Category;

class CommentTest extends \Codeception\Test\Unit
{
    public function testValidation()
    {
        $comment = new Comment();
        verify($comment->validate())->false();
        verify($comment->errors)->arrayHasKey('article_id');
        verify($comment->errors)->arrayHasKey('content');
    }

    public function testParentCommentValidation()
    {
        // Create user and two articles
        $user = User::findOne(['username' => 'admin']) ?: new User([
            'username' => 'admin',
            'email' => 'admin@example.com',
            'auth_key' => 'test100key',
            'role' => 'author',
            'status' => User::STATUS_ACTIVE,
        ]);
        if ($user->isNewRecord) { $user->setPassword('admin'); $user->save(false); }

        $catA = new Category(['name' => 'A', 'slug' => 'a']); $catA->save(false);
        $catB = new Category(['name' => 'B', 'slug' => 'b']); $catB->save(false);

        $articleA = new Article(['title'=>'A1','slug'=>'a1','content'=>'c','category_id'=>$catA->id,'author_id'=>$user->id,'status'=>Article::STATUS_PUBLISHED]); $articleA->save(false);
        $articleB = new Article(['title'=>'B1','slug'=>'b1','content'=>'c','category_id'=>$catB->id,'author_id'=>$user->id,'status'=>Article::STATUS_PUBLISHED]); $articleB->save(false);

        // Parent on article A
        $parent = new Comment(['article_id' => $articleA->id, 'user_id' => $user->id, 'content' => 'parent']);
        verify($parent->save())->true();

        // Child with mismatched article_id (should fail validation)
        $child = new Comment(['article_id' => $articleB->id, 'user_id' => $user->id, 'parent_id' => $parent->id, 'content' => 'child']);
        verify($child->validate())->false();
        verify($child->errors)->arrayHasKey('parent_id');
    }

    public function testCommentStatus()
    {
        $user = User::findOne(['username' => 'admin']) ?: new User([
            'username' => 'admin',
            'email' => 'admin@example.com',
            'auth_key' => 'test100key',
            'role' => 'author',
            'status' => User::STATUS_ACTIVE,
        ]);
        if ($user->isNewRecord) { $user->setPassword('admin'); $user->save(false); }

        $cat = new Category(['name' => 'StatusCat', 'slug' => 'statuscat']);
        $cat->save(false);

        $article = new Article(['title' => 'Status Article', 'slug' => 'status-article', 'content' => 'c', 'category_id' => $cat->id, 'author_id' => $user->id, 'status' => Article::STATUS_PUBLISHED]);
        $article->save(false);

        $pending = new Comment(['article_id' => $article->id, 'user_id' => $user->id, 'content' => 'Pending comment', 'status' => Comment::STATUS_PENDING]);
        $pending->save(false);
        verify($pending->isPending())->true();
        verify($pending->isApproved())->false();

        $approved = new Comment(['article_id' => $article->id, 'user_id' => $user->id, 'content' => 'Approved comment', 'status' => Comment::STATUS_APPROVED]);
        $approved->save(false);
        verify($approved->isApproved())->true();
        verify($approved->isPending())->false();
    }

    public function testCommentIsRootAndIsReply()
    {
        $user = User::findOne(['username' => 'admin']) ?: new User([
            'username' => 'admin',
            'email' => 'admin@example.com',
            'auth_key' => 'test100key',
            'role' => 'author',
            'status' => User::STATUS_ACTIVE,
        ]);
        if ($user->isNewRecord) { $user->setPassword('admin'); $user->save(false); }

        $cat = new Category(['name' => 'RootCat', 'slug' => 'rootcat']);
        $cat->save(false);

        $article = new Article(['title' => 'Root Article', 'slug' => 'root-article', 'content' => 'c', 'category_id' => $cat->id, 'author_id' => $user->id, 'status' => Article::STATUS_PUBLISHED]);
        $article->save(false);

        $root = new Comment(['article_id' => $article->id, 'user_id' => $user->id, 'content' => 'Root comment']);
        $root->save(false);
        verify($root->isRoot())->true();
        verify($root->isReply())->false();

        $reply = new Comment(['article_id' => $article->id, 'user_id' => $user->id, 'parent_id' => $root->id, 'content' => 'Reply comment']);
        $reply->save(false);
        verify($reply->isReply())->true();
        verify($reply->isRoot())->false();
    }

    public function testCommentReplies()
    {
        $user = User::findOne(['username' => 'admin']) ?: new User([
            'username' => 'admin',
            'email' => 'admin@example.com',
            'auth_key' => 'test100key',
            'role' => 'author',
            'status' => User::STATUS_ACTIVE,
        ]);
        if ($user->isNewRecord) { $user->setPassword('admin'); $user->save(false); }

        $cat = new Category(['name' => 'RepliesCat', 'slug' => 'repliescat']);
        $cat->save(false);

        $article = new Article(['title' => 'Replies Article', 'slug' => 'replies-article', 'content' => 'c', 'category_id' => $cat->id, 'author_id' => $user->id, 'status' => Article::STATUS_PUBLISHED]);
        $article->save(false);

        $root = new Comment(['article_id' => $article->id, 'user_id' => $user->id, 'content' => 'Root', 'status' => Comment::STATUS_APPROVED]);
        $root->save(false);

        $reply1 = new Comment(['article_id' => $article->id, 'user_id' => $user->id, 'parent_id' => $root->id, 'content' => 'Reply 1', 'status' => Comment::STATUS_APPROVED]);
        $reply1->save(false);

        $reply2 = new Comment(['article_id' => $article->id, 'user_id' => $user->id, 'parent_id' => $root->id, 'content' => 'Reply 2', 'status' => Comment::STATUS_APPROVED]);
        $reply2->save(false);

        $root->refresh();
        verify($root->getReplies()->count())->equals(2);
        verify($root->getRepliesCount())->equals(2);
    }

    public function testCommentRelations()
    {
        $user = User::findOne(['username' => 'admin']) ?: new User([
            'username' => 'admin',
            'email' => 'admin@example.com',
            'auth_key' => 'test100key',
            'role' => 'author',
            'status' => User::STATUS_ACTIVE,
        ]);
        if ($user->isNewRecord) { $user->setPassword('admin'); $user->save(false); }

        $cat = new Category(['name' => 'RelCat', 'slug' => 'relcat']);
        $cat->save(false);

        $article = new Article(['title' => 'Rel Article', 'slug' => 'rel-article', 'content' => 'c', 'category_id' => $cat->id, 'author_id' => $user->id, 'status' => Article::STATUS_PUBLISHED]);
        $article->save(false);

        $comment = new Comment(['article_id' => $article->id, 'user_id' => $user->id, 'content' => 'Comment']);
        $comment->save(false);

        verify($comment->article)->notEmpty();
        verify($comment->article->title)->equals('Rel Article');
        verify($comment->user)->notEmpty();
        verify($comment->user->username)->equals('admin');
    }
}
