<?php

class LoginFormCest
{
    public function _before(\FunctionalTester $I)
    {
        $I->amOnRoute('site/login');
    }

    public function openLoginPage(\FunctionalTester $I)
    {
        $I->see('Login', 'h1');

    }

    // demonstrates `amLoggedInAs` method
    public function internalLoginById(\FunctionalTester $I)
    {
        // ensure admin user exists
        $hash = \Yii::$app->getSecurity()->generatePasswordHash('admin');
        $I->haveRecord('app\\models\\User', [
            'id' => 100,
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password_hash' => $hash,
            'role' => 'author',
            'status' => 1,
        ]);

        $I->amLoggedInAs(100);
        $I->amOnPage('/');
        $I->see('Logout');
        $I->see('admin');
    }

    // demonstrates `amLoggedInAs` method
    public function internalLoginByInstance(\FunctionalTester $I)
    {
        // ensure admin exists
        $user = \app\models\User::findByUsername('admin');
        if (! $user) {
            $hash = \Yii::$app->getSecurity()->generatePasswordHash('admin');
            $I->haveRecord('app\\models\\User', [
                'id' => 100,
                'username' => 'admin',
                'email' => 'admin@example.com',
                'password_hash' => $hash,
                'role' => 'author',
                'status' => 1,
            ]);
            $user = \app\models\User::findByUsername('admin');
        }

        $I->amLoggedInAs($user);
        $I->amOnPage('/');
        $I->see('Logout');
        $I->see('admin');
    }

    public function loginWithEmptyCredentials(\FunctionalTester $I)
    {
        $I->submitForm('#login-form', []);
        $I->expectTo('see validations errors');
        $I->see('Username cannot be blank.');
        $I->see('Password cannot be blank.');
    }

    public function loginWithWrongCredentials(\FunctionalTester $I)
    {
        $I->submitForm('#login-form', [
            'LoginForm[username]' => 'admin',
            'LoginForm[password]' => 'wrong',
        ]);
        $I->expectTo('see validations errors');
        $I->see('Incorrect username or password.');
    }

    public function loginSuccessfully(\FunctionalTester $I)
    {
        // ensure admin user exists with known password
        $hash = \Yii::$app->getSecurity()->generatePasswordHash('admin');
        $I->haveRecord('app\\models\\User', [
            'id' => 100,
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password_hash' => $hash,
            'role' => 'author',
            'status' => 1,
        ]);

        $I->submitForm('#login-form', [
            'LoginForm[username]' => 'admin',
            'LoginForm[password]' => 'admin',
        ]);
        $I->see('Logout');
        $I->see('admin');
        $I->dontSeeElement('form#login-form');              
    }
}