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
    // Константи для ролей
    const ROLE_AUTHOR = 'author';
    const ROLE_READER = 'reader';

    // Константи для статусів
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
            [['username', 'email'], 'required'],
            [['password_hash'], 'required', 'on' => 'signup'], // Тільки при реєстрації
            [['role'], 'string'],
            [['status', 'created_at', 'updated_at'], 'integer'],
            [['username', 'email', 'avatar'], 'string', 'max' => 255],
            [['password_hash'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            [['username'], 'unique', 'filter' => function($query) {
                if (!$this->isNewRecord) {
                    $query->andWhere(['!=', 'id', $this->id]);
                }
            }],
            [['email'], 'unique', 'filter' => function($query) {
                if (!$this->isNewRecord) {
                    $query->andWhere(['!=', 'id', $this->id]);
                }
            }],
            [['email'], 'email'],
            [['role'], 'in', 'range' => [self::ROLE_AUTHOR, self::ROLE_READER]],
            [['status'], 'in', 'range' => [self::STATUS_INACTIVE, self::STATUS_ACTIVE]],
            [['avatar'], 'url', 'defaultScheme' => 'http', 'skipOnEmpty' => true],
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
        // If the users table has an access_token column, use it.
        $schema = static::getTableSchema();
        if (isset($schema->columns['access_token'])) {
            return static::findOne(['access_token' => $token, 'status' => self::STATUS_ACTIVE]);
        }

        // Support legacy/test token format like "<id>-token" (e.g., "100-token") to resolve to user id
        if (preg_match('/^(\d+)-token$/', (string)$token, $m)) {
            $id = (int)$m[1];
            $user = static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
            if ($user !== null) {
                return $user;
            }
        }

        // Fallback to checking auth_key
        return static::findOne(['auth_key' => $token, 'status' => self::STATUS_ACTIVE]);
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
     * Перевіряє, чи є користувач автором
     *
     * @return bool
     */
    public function isAuthor()
    {
        return $this->role === self::ROLE_AUTHOR;
    }

    /**
     * Перевіряє, чи є користувач читачем
     *
     * @return bool
     */
    public function isReader()
    {
        return $this->role === self::ROLE_READER;
    }

    /**
     * Отримати URL аватара користувача або дефолтне зображення
     * @param string $defaultUrl URL дефолтного аватара
     * @return string
     */
    public function getAvatarUrl($defaultUrl = null)
    {
        if (!empty($this->avatar)) {
            return $this->avatar;
        }
        
        // Якщо не вказано дефолтний URL, генеруємо аватар з ініціалом
        if ($defaultUrl === null) {
            // Використовуємо UI Avatars API для генерації аватара з першої літери імені
            $initial = strtoupper(substr($this->username, 0, 1));
            $backgroundColor = $this->getAvatarColor();
            $textColor = '#ffffff';
            
            return "https://ui-avatars.com/api/?name={$initial}&background={$backgroundColor}&color={$textColor}&size=150&bold=true&font-size=0.6";
        }
        
        return $defaultUrl; 
    }

    /**
     * Отримати колір фону для аватара на основі імені користувача
     * @return string Hex колір без #
     */
    private function getAvatarColor()
    {
        // Генеруємо колір на основі імені користувача для консистентності
        $colors = [
            '4a90e2', // Синій
            '50c878', // Зелений
            'f39c12', // Помаранчевий
            'e74c3c', // Червоний
            '9b59b6', // Фіолетовий
            '1abc9c', // Бірюзовий
            '34495e', // Темно-синій
            'e67e22', // Темно-помаранчевий
        ];
        
        // Вибираємо колір на основі хешу імені користувача
        $index = crc32($this->username) % count($colors);
        return $colors[$index];
    }
}