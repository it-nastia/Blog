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
    // Константы для статусов
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
            [['parent_id'], 'exist', 'skipOnError' => true, 'targetClass' => Comment::class, 'targetAttribute' => ['parent_id' => 'id']],
            // Проверка, что родительский комментарий принадлежит той же статье
            ['parent_id', 'validateParentComment'],
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
     * Валидация: проверяет, что родительский комментарий принадлежит той же статье
     * @param string $attribute
     * @param array $params
     */
    public function validateParentComment($attribute, $params)
    {
        if ($this->parent_id) {
            $parent = static::findOne($this->parent_id);
            if ($parent && $parent->article_id !== $this->article_id) {
                $this->addError($attribute, 'Parent comment must belong to the same article.');
            }
        }
    }

    /**
     * Связь со статьей (1-N)
     * @return \yii\db\ActiveQuery
     */
    public function getArticle()
    {
        return $this->hasOne(Article::class, ['id' => 'article_id']);
    }

    /**
     * Связь с пользователем (1-N, может быть NULL)
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Связь с родительским комментарием (самосвязь)
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(Comment::class, ['id' => 'parent_id']);
    }

    /**
     * Связь с дочерними комментариями (ответы)
     * @return \yii\db\ActiveQuery
     */
    public function getReplies()
    {
        return $this->hasMany(Comment::class, ['parent_id' => 'id'])
            ->where(['status' => self::STATUS_APPROVED])
            ->orderBy(['created_at' => SORT_ASC]);
    }

    /**
     * Получить все дочерние комментарии (включая неодобренные)
     * @return \yii\db\ActiveQuery
     */
    public function getAllReplies()
    {
        return $this->hasMany(Comment::class, ['parent_id' => 'id'])
            ->orderBy(['created_at' => SORT_ASC]);
    }

    /**
     * Получить количество ответов
     * @return int
     */
    public function getRepliesCount()
    {
        return $this->getReplies()->count();
    }

    /**
     * Проверяет, является ли комментарий корневым (не имеет родителя)
     * @return bool
     */
    public function isRoot()
    {
        return $this->parent_id === null;
    }

    /**
     * Проверяет, является ли комментарий ответом (имеет родителя)
     * @return bool
     */
    public function isReply()
    {
        return $this->parent_id !== null;
    }

    /**
     * Проверяет, одобрен ли комментарий
     * @return bool
     */
    public function isApproved()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Проверяет, ожидает ли комментарий модерации
     * @return bool
     */
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Получить имя автора комментария
     * @return string
     */
    public function getAuthorName()
    {
        return $this->user ? $this->user->username : 'Guest';
    }

    /**
     * Получить одобренные комментарии
     * @return \yii\db\ActiveQuery
     */
    public static function findApproved()
    {
        return static::find()->where(['status' => self::STATUS_APPROVED]);
    }

    /**
     * Получить корневые комментарии (без родителя) для статьи
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
     * Получить все комментарии статьи (включая вложенные)
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