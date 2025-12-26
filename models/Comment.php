<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * Comment model
 *
 * @property int $id
 * @property int $article_id
 * @property int|null $user_id
 * @property int|null $parent_id
 * @property string $content
 * @property string $status
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Article $article
 * @property User|null $user
 * @property Comment|null $parent
 * @property Comment[] $replies
 */
class Comment extends ActiveRecord
{
    // Константи для статусів
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'comments';
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
            [['article_id', 'content'], 'required'],
            [['article_id', 'user_id', 'parent_id', 'created_at', 'updated_at'], 'integer'],
            [['content'], 'string'],
            [['status'], 'string'],
            [['status'], 'in', 'range' => [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_REJECTED]],
            [['status'], 'default', 'value' => self::STATUS_PENDING],
            [['article_id'], 'exist', 'skipOnError' => true, 'targetClass' => Article::class, 'targetAttribute' => ['article_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['parent_id'], 'exist', 'skipOnError' => true, 'skipOnEmpty' => true, 'targetClass' => Comment::class, 'targetAttribute' => ['parent_id' => 'id']],
            // Проверка, что родительский комментарий принадлежит той же статье
            ['parent_id', 'validateParentComment', 'skipOnEmpty' => true],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'article_id' => 'Article',
            'user_id' => 'User',
            'parent_id' => 'Parent Comment',
            'content' => 'Content',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Валідація: проверяє, чи батьківський коментар належить тій самій статті
     * @param string $attribute
     * @param array $params
     */
    public function validateParentComment($attribute, $params)
    {
        if ($this->parent_id) {
            $parent = static::findOne($this->parent_id);
            if (!$parent) {
                $this->addError($attribute, 'Parent comment not found.');
                return;
            }
            // Приводим к int для корректного сравнения
            $parentArticleId = (int)$parent->article_id;
            $currentArticleId = (int)$this->article_id;
            if ($parentArticleId !== $currentArticleId) {
                $this->addError($attribute, 'Parent comment must belong to the same article.');
            }
        }
    }

    /**
     * Зв'язок з статтею (1-N)
     * @return \yii\db\ActiveQuery
     */
    public function getArticle()
    {
        return $this->hasOne(Article::class, ['id' => 'article_id']);
    }

    /**
     * Зв'язок з користувачем (1-N, може бути NULL)
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Зв'язок з батьківським коментарем (самосв'язок)
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(Comment::class, ['id' => 'parent_id']);
    }

    /**
     * Зв'язок з дочірніми коментарями (відповіді)
     * @return \yii\db\ActiveQuery
     */
    public function getReplies()
    {
        return $this->hasMany(Comment::class, ['parent_id' => 'id'])
            ->where(['status' => self::STATUS_APPROVED])
            ->orderBy(['created_at' => SORT_ASC]);
    }

    /**
     * Отримати всі дочірні коментари (включаючи неодобренні)
     * @return \yii\db\ActiveQuery
     */
    public function getAllReplies()
    {
        return $this->hasMany(Comment::class, ['parent_id' => 'id'])
            ->orderBy(['created_at' => SORT_ASC]);
    }

    /**
     * Отримати кількість відповідей
     * @return int
     */
    public function getRepliesCount()
    {
        return $this->getReplies()->count();
    }

    /**
     * Перевіряє, чи є коментар корневим (не має батька)
     * @return bool
     */
    public function isRoot()
    {
        return $this->parent_id === null;
    }

    /**
     * Перевіряє, чи є коментар відповіддю (має батька)
     * @return bool
     */
    public function isReply()
    {
        return $this->parent_id !== null;
    }

    /**
     * Перевіряє, чи є коментар схвалений
     * @return bool
     */
    public function isApproved()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Перевіряє, чи очікує коментар модерації
     * @return bool
     */
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Отримати ім'я автора коментаря
     * @return string
     */
    public function getAuthorName()
    {
        return $this->user ? $this->user->username : 'Guest';
    }

    /**
     * Отримати схвалені коментари
     * @return \yii\db\ActiveQuery
     */
    public static function findApproved()
    {
        return static::find()->where(['status' => self::STATUS_APPROVED]);
    }

    /**
     * Отримати корневі коментари (без батька) для статті
     * @param int $articleId
     * @return \yii\db\ActiveQuery
     */
    public static function findRootComments($articleId)
    {
        return static::findApproved()
            ->where(['article_id' => $articleId, 'parent_id' => null])
            ->orderBy(['created_at' => SORT_ASC]);
    }

    /**
     * Отримати всі коментари статті (включаючи вкладені)
     * @param int $articleId
     * @return \yii\db\ActiveQuery
     */
    public static function findByArticle($articleId)
    {
        return static::findApproved()
            ->where(['article_id' => $articleId])
            ->orderBy(['created_at' => SORT_ASC]);
    }
}