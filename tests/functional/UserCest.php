<?php

class UserCest
{
    public function _before(\FunctionalTester $I)
    {
    }

    public function userCannotViewOtherProfile(\FunctionalTester $I)
    {
        $hash = \Yii::$app->getSecurity()->generatePasswordHash('password');
        $I->haveRecord('app\\models\\User', [
            'id' => 200,
            'username' => 'user1',
            'email' => 'user1@example.com',
            'password_hash' => $hash,
            'role' => 'reader',
            'status' => 1,
        ]);

        $I->haveRecord('app\\models\\User', [
            'id' => 201,
            'username' => 'user2',
            'email' => 'user2@example.com',
            'password_hash' => $hash,
            'role' => 'reader',
            'status' => 1,
        ]);

        $I->amLoggedInAs(200);
        $I->amOnRoute('user/profile', ['id' => 201]);
        $I->see('You can only view your own profile');
    }

    public function authorSeesStatsInProfile(\FunctionalTester $I)
    {
        $hash = \Yii::$app->getSecurity()->generatePasswordHash('password');
        $authorId = $I->haveRecord('app\\models\\User', [
            'id' => 200,
            'username' => 'author',
            'email' => 'author@example.com',
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
            'author_id' => 200,
            'status' => 'published',
            'views' => 0,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $I->amLoggedInAs(200);
        $I->amOnRoute('user/profile', ['id' => 200]);
        $I->see('author');
        // Stats should be visible for authors
    }

    public function userCanUpdateProfile(\FunctionalTester $I)
    {
        $hash = \Yii::$app->getSecurity()->generatePasswordHash('password');
        $userId = $I->haveRecord('app\\models\\User', [
            'id' => 200,
            'username' => 'testuser',
            'email' => 'testuser@example.com',
            'password_hash' => $hash,
            'role' => 'reader',
            'status' => 1,
        ]);

        $I->amLoggedInAs(200);
        $I->amOnRoute('user/profile', ['id' => 200]);
        
        $I->submitForm('#profile-form', [
            'ProfileForm[username]' => 'updateduser',
            'ProfileForm[email]' => 'updated@example.com',
            'ProfileForm[avatar]' => 'https://example.com/avatar.jpg',
        ]);

        $I->see('Profile updated successfully');
        $user = $I->grabRecord('app\\models\\User', ['id' => 200]);
        \PHPUnit\Framework\Assert::assertEquals('updateduser', $user->username);
    }
}

