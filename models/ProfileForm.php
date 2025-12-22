<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * ProfileForm is the model behind the profile edit form.
 */
class ProfileForm extends Model
{
    public $username;
    public $email;
    public $avatar;
    public $current_password;
    public $new_password;
    public $confirm_password;

    private $_user;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'email'], 'required'],
            [['username', 'email'], 'trim'],
            ['username', 'string', 'min' => 3, 'max' => 255],
            ['username', 'match', 'pattern' => '/^[a-zA-Z0-9_]+$/', 'message' => 'Username can only contain letters, numbers and underscores.'],
            ['username', 'unique', 'targetClass' => User::class, 'filter' => function($query) {
                $query->andWhere(['!=', 'id', $this->getUser()->id]);
            }, 'message' => 'This username has already been taken.'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => User::class, 'filter' => function($query) {
                $query->andWhere(['!=', 'id', $this->getUser()->id]);
            }, 'message' => 'This email address has already been taken.'],
            ['avatar', 'string', 'max' => 255],
            ['avatar', 'url', 'defaultScheme' => 'http', 'skipOnEmpty' => true],
            [['current_password', 'new_password', 'confirm_password'], 'string'],
            ['new_password', 'string', 'min' => 6, 'skipOnEmpty' => true],
            ['confirm_password', 'compare', 'compareAttribute' => 'new_password', 'message' => 'Passwords do not match.', 'skipOnEmpty' => true],
            ['current_password', 'validateCurrentPassword', 'skipOnEmpty' => true],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'username' => 'Username',
            'email' => 'Email',
            'avatar' => 'Avatar URL',
            'current_password' => 'Current Password',
            'new_password' => 'New Password',
            'confirm_password' => 'Confirm New Password',
        ];
    }

    /**
     * Validates current password
     */
    public function validateCurrentPassword($attribute, $params)
    {
        if (!empty($this->new_password)) {
            if (empty($this->current_password)) {
                $this->addError($attribute, 'Current password is required to change password.');
                return;
            }

            if (!$this->getUser()->validatePassword($this->current_password)) {
                $this->addError($attribute, 'Current password is incorrect.');
            }
        }
    }

    /**
     * Gets user model
     * @return User
     */
    public function getUser()
    {
        if ($this->_user === null) {
            $this->_user = Yii::$app->user->identity;
        }
        return $this->_user;
    }

    /**
     * Loads user data into form
     */
    public function loadUserData()
    {
        $user = $this->getUser();
        $this->username = $user->username;
        $this->email = $user->email;
        $this->avatar = $user->avatar;
    }

    /**
     * Saves profile data
     * @return bool
     */
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        $user = $this->getUser();
        $user->username = $this->username;
        $user->email = $this->email;
        $user->avatar = $this->avatar ?: null;

        // Оновлюємо пароль, якщо вказано новий
        if (!empty($this->new_password)) {
            $user->setPassword($this->new_password);
        }

        if ($user->save(false)) {
            return true;
        }

        // Якщо збереження не вдалось, додаємо помилки з моделі User
        foreach ($user->errors as $attribute => $errors) {
            foreach ($errors as $error) {
                $this->addError($attribute, $error);
            }
        }

        return false;
    }
}

