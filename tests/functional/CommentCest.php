<?php

class CommentCest
{
    public function _before(\FunctionalTester $I)
    {
    }

    public function guestSeesLoginPrompt(\FunctionalTester $I)
    {
        $I->haveRecord('app\\models\\User', [
            'id' => 100,
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password_hash' => 'test',
            'role' => 'author',
            'status' => 1,
        ]);

        $categoryId = $I->haveRecord('app\\models\\Category', [
            'name' => 'General',
            'slug' => 'general',
        ]);

        $I->haveRecord('app\\models\\Article', [
            'title' => 'Article for comments',
            'slug' => 'article-comments',
            'content' => 'Content for comments',
            'category_id' => $categoryId,
            'author_id' => 100,
            'status' => 'published',
            'views' => 0,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $I->amOnRoute('article/view', ['slug' => 'article-comments']);
        $I->see('Login', 'a');
        $I->seeLink('Login');
    }

    public function userCommentIsPending(\FunctionalTester $I)
    {
        // prepare author, normal user and article
        $I->haveRecord('app\\models\\User', [
            'id' => 100,
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password_hash' => 'test',
            'role' => 'author',
            'status' => 1,
        ]);

        $I->haveRecord('app\\models\\User', [
            'id' => 101,
            'username' => 'reader',
            'email' => 'reader@example.com',
            'password_hash' => 'test',
            'role' => 'user',
            'status' => 1,
        ]);

        $categoryId = $I->haveRecord('app\\models\\Category', [
            'name' => 'Lifestyle',
            'slug' => 'lifestyle',
        ]);

        $articleId = $I->haveRecord('app\\models\\Article', [
            'title' => 'Article to comment',
            'slug' => 'article-to-comment',
            'content' => 'Let\'s comment here',
            'category_id' => $categoryId,
            'author_id' => 100,
            'status' => 'published',
            'views' => 0,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $I->amLoggedInAs(101);
        $I->amOnRoute('article/view', ['slug' => 'article-to-comment']);

        $I->submitForm('.comment-form-card form', [
            'Comment[article_id]' => $articleId,
            'Comment[user_id]' => 101,
            'Comment[content]' => 'Nice article!',
        ]);

        // After submitForm the redirect response contains the flash; check immediately
        $I->see('Comment submitted and is pending moderation.');

        // verify created in DB with pending status
        $comment = $I->grabRecord('app\\models\\Comment', ['content' => 'Nice article!']);
        \PHPUnit\Framework\Assert::assertNotNull($comment);
        \PHPUnit\Framework\Assert::assertEquals('pending', $comment->status);
        // Pending comment is visible to the commenter (app behaviour)
        $I->see('Nice article!');

        // Visit article view to ensure it remains visible to the commenter
        $I->amOnRoute('article/view', ['slug' => 'article-to-comment']);
        $I->see('Nice article!');
    }

    public function authorCommentIsApproved(\FunctionalTester $I)
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
            'name' => 'Tech',
            'slug' => 'tech-comments',
        ]);

        $articleId = $I->haveRecord('app\\models\\Article', [
            'title' => 'Author comment article',
            'slug' => 'author-comment-article',
            'content' => 'Author article',
            'category_id' => $categoryId,
            'author_id' => 100,
            'status' => 'published',
            'views' => 0,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $I->amLoggedInAs(100);
        $I->amOnRoute('article/view', ['slug' => 'author-comment-article']);

        $I->submitForm('.comment-form-card form', [
            'Comment[article_id]' => $articleId,
            'Comment[user_id]' => 100,
            'Comment[content]' => 'Author comment visible',
        ]);

        // Check flash and page immediately after submitForm
        $I->see('Comment posted successfully.');
        $I->see('Author comment visible');

        // verify created in DB with approved status
        $comment = $I->grabRecord('app\\models\\Comment', ['content' => 'Author comment visible']);
        \PHPUnit\Framework\Assert::assertNotNull($comment);
        \PHPUnit\Framework\Assert::assertEquals('approved', $comment->status);

        // Visit article view to confirm comment remains visible
        $I->amOnRoute('article/view', ['slug' => 'author-comment-article']);
        $I->see('Author comment visible');
    }

    public function userCanReplyToComment(\FunctionalTester $I)
    {
        $hash = \Yii::$app->getSecurity()->generatePasswordHash('password');
        $I->haveRecord('app\\models\\User', [
            'id' => 100,
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password_hash' => $hash,
            'role' => 'author',
            'status' => 1,
        ]);

        $I->haveRecord('app\\models\\User', [
            'id' => 101,
            'username' => 'reader',
            'email' => 'reader@example.com',
            'password_hash' => $hash,
            'role' => 'reader',
            'status' => 1,
        ]);

        $categoryId = $I->haveRecord('app\\models\\Category', [
            'name' => 'Tech',
            'slug' => 'tech',
        ]);

        $articleId = $I->haveRecord('app\\models\\Article', [
            'title' => 'Article for reply',
            'slug' => 'article-reply',
            'content' => 'Content',
            'category_id' => $categoryId,
            'author_id' => 100,
            'status' => 'published',
            'views' => 0,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $parentCommentId = $I->haveRecord('app\\models\\Comment', [
            'article_id' => $articleId,
            'user_id' => 100,
            'content' => 'Parent comment',
            'status' => 'approved',
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $I->amLoggedInAs(101);
        $I->amOnRoute('article/view', ['slug' => 'article-reply']);

        $I->submitForm('.comment-form-card form', [
            'Comment[article_id]' => $articleId,
            'Comment[user_id]' => 101,
            'Comment[parent_id]' => $parentCommentId,
            'Comment[content]' => 'Reply to parent',
        ]);

        $I->see('Comment submitted and is pending moderation');
        $reply = $I->grabRecord('app\\models\\Comment', ['content' => 'Reply to parent']);
        \PHPUnit\Framework\Assert::assertNotNull($reply);
        \PHPUnit\Framework\Assert::assertEquals($parentCommentId, $reply->parent_id);
    }

}

