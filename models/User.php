<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\behaviors\TimestampBehavior;

/**
 * User model
 *
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string|null $avatar
 * @property string $password_hash
 * @property string $auth_key
 * @property string $role
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 */
class User extends ActiveRecord implements IdentityInterface
{
    // Константы для ролей
    const ROLE_AUTHOR = 'author';
    const ROLE_READER = 'reader';

    // Константы для статусов
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'users';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'email', 'password_hash'], 'required'],
            [['role'], 'string'],
            [['status', 'created_at', 'updated_at'], 'integer'],
            [['username', 'email', 'avatar'], 'string', 'max' => 255],
            [['password_hash'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            [['username'], 'unique'],
            [['email'], 'unique'],
            [['email'], 'email'],
            [['role'], 'in', 'range' => [self::ROLE_AUTHOR, self::ROLE_READER]],
            [['status'], 'in', 'range' => [self::STATUS_INACTIVE, self::STATUS_ACTIVE]],
            [['avatar'], 'url', 'defaultScheme' => 'http'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'email' => 'Email',
            'avatar' => 'Avatar',
            'password_hash' => 'Password Hash',
            'auth_key' => 'Auth Key',
            'role' => 'Role',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->auth_key === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Проверяет, является ли пользователь автором
     *
     * @return bool
     */
    public function isAuthor()
    {
        return $this->role === self::ROLE_AUTHOR;
    }

    /**
     * Проверяет, является ли пользователь читателем
     *
     * @return bool
     */
    public function isReader()
    {
        return $this->role === self::ROLE_READER;
    }

    /**
     * Получить URL аватара пользователя или дефолтное изображение
     * @param string $defaultUrl URL дефолтного аватара
     * @return string
     */
    public function getAvatarUrl($defaultUrl = null)
    {
        if (!empty($this->avatar)) {
            return $this->avatar;
        }
        
        // Если не указан дефолтный URL, используем стандартный аватар
        if ($defaultUrl === null) {
            return 'https://www.shutterstock.com/ru/search/blank-avatar-icon' . urlencode(strtoupper(substr($this->username, 0, 1)));
        }
        
        return $defaultUrl; 
    }
}