<?php

namespace app\models;

use Yii;
use yii\base\Model;

class ContactForm extends Model
{
    public $name;
    public $email;
    public $subject;
    public $body;
    public $verifyCode;

    public function rules()
    {
        return [
            [['name', 'email', 'subject', 'body'], 'required'],
            ['email', 'email'],
            ['verifyCode', 'captcha'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'verifyCode' => 'Verification Code',
        ];
    }

    /**
     * Sends an email to the specified email address using data collected by this model.
     * @param string $email target email
     * @return bool whether the message was sent
     */
    public function contact($email)
    {
        if (!$this->validate()) {
            return false;
        }

        return Yii::$app->mailer->compose()
            ->setTo($email)
            ->setFrom(['noreply@example.com' => Yii::$app->name])
            ->setReplyTo([$this->email => $this->name])
            ->setSubject($this->subject)
            ->setTextBody($this->body)
            ->send();
    }
}
