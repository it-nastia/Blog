<?php

class SiteCest
{
    public function _before(\FunctionalTester $I)
    {
    }

    public function homepageDisplaysContent(\FunctionalTester $I)
    {
        // Create test data
        $hash = \Yii::$app->getSecurity()->generatePasswordHash('admin');
        $I->haveRecord('app\\models\\User', [
            'id' => 100,
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password_hash' => $hash,
            'role' => 'author',
            'status' => 1,
        ]);

        $categoryId = $I->haveRecord('app\\models\\Category', [
            'name' => 'Tech',
            'slug' => 'tech',
        ]);

        $I->haveRecord('app\\models\\Article', [
            'title' => 'Popular Article',
            'slug' => 'popular-article',
            'content' => 'Content',
            'category_id' => $categoryId,
            'author_id' => 100,
            'status' => 'published',
            'views' => 100,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $I->amOnPage('/');
        $I->see('Stories behind the screen');
        $I->see('Popular Article');
    }

    public function openAboutPage(\FunctionalTester $I)
    {
        $I->amOnRoute('site/about');
        $I->see('About');
    }

    public function openContactPage(\FunctionalTester $I)
    {
        $I->amOnRoute('site/contact');
        $I->see('Contact');
    }

    public function submitContactForm(\FunctionalTester $I)
    {
        $I->amOnRoute('site/contact');
        $I->submitForm('#contact-form', [
            'ContactForm[name]' => 'Test User',
            'ContactForm[email]' => 'test@example.com',
            'ContactForm[subject]' => 'Test Subject',
            'ContactForm[body]' => 'Test message body',
            'ContactForm[verifyCode]' => 'testme',
        ]);
        $I->see('Thank you for contacting us');
    }

}

