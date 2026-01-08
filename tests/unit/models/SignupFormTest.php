<?php

namespace tests\unit\models;

use app\models\SignupForm;
use app\models\User;

class SignupFormTest extends \Codeception\Test\Unit
{
    protected function _after()
    {
        // Clean up test users
        User::deleteAll(['username' => ['testuser', 'testuser2', 'testuser3']]);
    }

    public function testValidationEmptyFields()
    {
        $model = new SignupForm();
        verify($model->validate())->false();
        verify($model->errors)->arrayHasKey('username');
        verify($model->errors)->arrayHasKey('email');
        verify($model->errors)->arrayHasKey('password');
        verify($model->errors)->arrayHasKey('password_repeat');
    }

    public function testValidationUsernameTooShort()
    {
        $model = new SignupForm([
            'username' => 'ab',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_repeat' => 'password123',
        ]);
        verify($model->validate())->false();
        verify($model->errors)->arrayHasKey('username');
    }

    public function testValidationUsernameInvalidCharacters()
    {
        $model = new SignupForm([
            'username' => 'test-user!',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_repeat' => 'password123',
        ]);
        verify($model->validate())->false();
        verify($model->errors)->arrayHasKey('username');
    }

    public function testValidationEmailInvalid()
    {
        $model = new SignupForm([
            'username' => 'testuser',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_repeat' => 'password123',
        ]);
        verify($model->validate())->false();
        verify($model->errors)->arrayHasKey('email');
    }

    public function testValidationPasswordTooShort()
    {
        $model = new SignupForm([
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => '12345',
            'password_repeat' => '12345',
        ]);
        verify($model->validate())->false();
        verify($model->errors)->arrayHasKey('password');
    }

    public function testValidationPasswordMismatch()
    {
        $model = new SignupForm([
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_repeat' => 'password456',
        ]);
        verify($model->validate())->false();
        verify($model->errors)->arrayHasKey('password_repeat');
    }

    public function testValidationUsernameUnique()
    {
        // Create existing user
        $existing = new User();
        $existing->username = 'existinguser';
        $existing->email = 'existing@example.com';
        $existing->setPassword('password');
        $existing->role = User::ROLE_READER;
        $existing->status = User::STATUS_ACTIVE;
        $existing->generateAuthKey();
        $existing->save(false);

        $model = new SignupForm([
            'username' => 'existinguser',
            'email' => 'new@example.com',
            'password' => 'password123',
            'password_repeat' => 'password123',
        ]);
        verify($model->validate())->false();
        verify($model->errors)->arrayHasKey('username');
    }

    public function testValidationEmailUnique()
    {
        // Create existing user
        $existing = new User();
        $existing->username = 'existinguser2';
        $existing->email = 'existing2@example.com';
        $existing->setPassword('password');
        $existing->role = User::ROLE_READER;
        $existing->status = User::STATUS_ACTIVE;
        $existing->generateAuthKey();
        $existing->save(false);

        $model = new SignupForm([
            'username' => 'newuser',
            'email' => 'existing2@example.com',
            'password' => 'password123',
            'password_repeat' => 'password123',
        ]);
        verify($model->validate())->false();
        verify($model->errors)->arrayHasKey('email');
    }

    public function testSuccessfulSignup()
    {
        $model = new SignupForm([
            'username' => 'testuser',
            'email' => 'testuser@example.com',
            'password' => 'password123',
            'password_repeat' => 'password123',
        ]);

        verify($model->validate())->true();
        verify($model->signup())->true();

        $user = User::findByUsername('testuser');
        verify($user)->notEmpty();
        verify($user->email)->equals('testuser@example.com');
        verify($user->role)->equals(User::ROLE_READER);
        verify($user->status)->equals(User::STATUS_ACTIVE);
        verify($user->validatePassword('password123'))->true();
        verify($user->auth_key)->notEmpty();
    }

}

