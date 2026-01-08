<?php

namespace tests\unit\models;

use app\models\User;

class UserTest extends \Codeception\Test\Unit
{
    protected function _before()
    {
        // ensure admin user exists for tests
        $user = User::findOne(['id' => 100]);
        if (!$user) {
            $user = new User();
            $user->id = 100;
            $user->username = 'admin';
            $user->email = 'admin@example.com';
            $user->setPassword('admin');
            $user->auth_key = 'test100key';
            $user->role = 'author';
            $user->status = User::STATUS_ACTIVE;
            $user->created_at = time();
            $user->updated_at = time();
            $user->save(false);
        }
    }

    public function testFindUserById()
    {
        verify($user = User::findIdentity(100))->notEmpty();
        verify($user->username)->equals('admin');

        verify(User::findIdentity(999))->empty();
    }

    public function testFindUserByAccessToken()
    {
        verify($user = User::findIdentityByAccessToken('100-token'))->notEmpty();
        verify($user->username)->equals('admin');

        verify(User::findIdentityByAccessToken('non-existing'))->empty();        
    }

    public function testFindUserByUsername()
    {
        verify($user = User::findByUsername('admin'))->notEmpty();
        verify(User::findByUsername('not-admin'))->empty();
    }

    /**
     * @depends testFindUserByUsername
     */
    public function testValidateUser()
    {
        $user = User::findByUsername('admin');
        verify($user->validateAuthKey('test100key'))->notEmpty();
        verify($user->validateAuthKey('test102key'))->empty();

        verify($user->validatePassword('admin'))->notEmpty();
        verify($user->validatePassword('123456'))->empty();        
    }

    public function testUserRoles()
    {
        $user = User::findByUsername('admin');
        verify($user->isAuthor())->true();
        verify($user->isReader())->false();

        // Create a reader user
        $reader = new User();
        $reader->username = 'testreader';
        $reader->email = 'reader@example.com';
        $reader->setPassword('password');
        $reader->role = User::ROLE_READER;
        $reader->status = User::STATUS_ACTIVE;
        $reader->generateAuthKey();
        $reader->save(false);

        verify($reader->isReader())->true();
        verify($reader->isAuthor())->false();
    }

    public function testUserStatus()
    {
        $user = User::findByUsername('admin');
        verify($user->status)->equals(User::STATUS_ACTIVE);

        // Inactive user should not be found
        $inactive = new User();
        $inactive->username = 'inactive';
        $inactive->email = 'inactive@example.com';
        $inactive->setPassword('password');
        $inactive->role = User::ROLE_READER;
        $inactive->status = User::STATUS_INACTIVE;
        $inactive->generateAuthKey();
        $inactive->save(false);

        verify(User::findIdentity($inactive->id))->empty();
        verify(User::findByUsername('inactive'))->empty();
    }

    public function testUserAvatar()
    {
        $user = User::findByUsername('admin');
        
        // Test with custom avatar
        $user->avatar = 'https://example.com/avatar.jpg';
        $user->save(false);
        verify($user->getAvatarUrl())->equals('https://example.com/avatar.jpg');

        // Test with default avatar (no custom avatar)
        $user->avatar = null;
        $user->save(false);
        $avatarUrl = $user->getAvatarUrl();
        verify($avatarUrl)->notEmpty();
        verify($avatarUrl)->stringContainsString('ui-avatars.com');
    }

    public function testUserValidation()
    {
        $user = new User();
        verify($user->validate())->false();
        verify($user->errors)->arrayHasKey('username');
        verify($user->errors)->arrayHasKey('email');

        $user->username = 'testuser';
        $user->email = 'invalid-email';
        verify($user->validate())->false();
        verify($user->errors)->arrayHasKey('email');

        $user->email = 'valid@example.com';
        $user->role = User::ROLE_READER;
        $user->status = User::STATUS_ACTIVE;
        verify($user->validate())->true();
    }

    public function testUserUniqueConstraints()
    {
        $user1 = new User();
        $user1->username = 'uniqueuser';
        $user1->email = 'unique1@example.com';
        $user1->setPassword('password');
        $user1->role = User::ROLE_READER;
        $user1->status = User::STATUS_ACTIVE;
        $user1->generateAuthKey();
        verify($user1->save())->true();

        $user2 = new User();
        $user2->username = 'uniqueuser'; // Same username
        $user2->email = 'unique2@example.com';
        $user2->setPassword('password');
        $user2->role = User::ROLE_READER;
        $user2->status = User::STATUS_ACTIVE;
        $user2->generateAuthKey();
        verify($user2->validate())->false();
        verify($user2->errors)->arrayHasKey('username');

        $user3 = new User();
        $user3->username = 'uniqueuser2';
        $user3->email = 'unique1@example.com'; // Same email
        $user3->setPassword('password');
        $user3->role = User::ROLE_READER;
        $user3->status = User::STATUS_ACTIVE;
        $user3->generateAuthKey();
        verify($user3->validate())->false();
        verify($user3->errors)->arrayHasKey('email');
    }

    public function testGenerateAuthKey()
    {
        $user = new User();
        $user->username = 'authkeytest';
        $user->email = 'authkey@example.com';
        $user->setPassword('password');
        $user->role = User::ROLE_READER;
        $user->status = User::STATUS_ACTIVE;
        $user->generateAuthKey();
        
        verify($user->auth_key)->notEmpty();
        verify(strlen($user->auth_key))->equals(32);
    }

}
