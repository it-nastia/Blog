<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * Article model
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $content
 * @property string|null $image
 * @property int $category_id
 * @property int $author_id
 * @property string $status
 * @property int $views
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Category $category
 * @property User $author
 * @property Tag[] $tags
 * @property Comment[] $comments
 */
class Article extends ActiveRecord
{
    // Константы для статусов
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'articles';
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
            [['title', 'slug', 'content', 'category_id', 'author_id'], 'required'],
            [['content'], 'string'],
            [['category_id', 'author_id', 'views', 'created_at', 'updated_at'], 'integer'],
            [['title', 'slug', 'image'], 'string', 'max' => 255],
            [['status'], 'string'],
            [['slug'], 'unique'],
            [['status'], 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_PUBLISHED]],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
            [['author_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['author_id' => 'id']],
            [['views'], 'default', 'value' => 0],
            [['status'], 'default', 'value' => self::STATUS_DRAFT],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'slug' => 'Slug',
            'content' => 'Content',
            'image' => 'Image',
            'category_id' => 'Category',
            'author_id' => 'Author',
            'status' => 'Status',
            'views' => 'Views',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Генерирует slug из заголовка статьи
     * Вызывается перед сохранением, если slug не указан
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if (empty($this->slug)) {
                $this->slug = Inflector::slug($this->title);
            }
            return true;
        }
        return false;
    }

    /**
     * Связь с категорией (1-N)
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    /**
     * Связь с автором (1-N)
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(User::class, ['id' => 'author_id']);
    }

    /**
     * Связь many-to-many с тегами через article_tag
     * @return \yii\db\ActiveQuery
     */
    public function getTags()
    {
        return $this->hasMany(Tag::class, ['id' => 'tag_id'])
            ->viaTable('article_tag', ['article_id' => 'id']);
    }

    /**
     * Связь с комментариями (1-N)
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        return $this->hasMany(Comment::class, ['article_id' => 'id'])
            ->where(['status' => Comment::STATUS_APPROVED])
            ->orderBy(['created_at' => SORT_ASC]);
    }

    /**
     * Получить все комментарии (включая неодобренные)
     * @return \yii\db\ActiveQuery
     */
    public function getAllComments()
    {
        return $this->hasMany(Comment::class, ['article_id' => 'id'])
            ->orderBy(['created_at' => SORT_ASC]);
    }

    /**
     * Получить количество комментариев
     * @return int
     */
    public function getCommentsCount()
    {
        return $this->getComments()->count();
    }

    /**
     * Увеличить счетчик просмотров
     */
    public function incrementViews()
    {
        $this->updateCounters(['views' => 1]);
    }

    /**
     * Получить краткое описание статьи (первые N символов)
     * @param int $length
     * @return string
     */
    public function getExcerpt($length = 200)
    {
        return StringHelper::truncate(strip_tags($this->content), $length);
    }

    /**
     * Проверяет, опубликована ли статья
     * @return bool
     */
    public function isPublished()
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    /**
     * Проверяет, является ли статья черновиком
     * @return bool
     */
    public function isDraft()
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Получить опубликованные статьи
     * @return \yii\db\ActiveQuery
     */
    public static function findPublished()
    {
        return static::find()->where(['status' => self::STATUS_PUBLISHED]);
    }

    /**
     * Получить статьи по категории
     * @param int $categoryId
     * @return \yii\db\ActiveQuery
     */
    public static function findByCategory($categoryId)
    {
        return static::findPublished()->where(['category_id' => $categoryId]);
    }

    /**
     * Получить статьи по тегу
     * @param int $tagId
     * @return \yii\db\ActiveQuery
     */
    public static function findByTag($tagId)
    {
        return static::findPublished()
            ->innerJoin('article_tag', 'articles.id = article_tag.article_id')
            ->where(['article_tag.tag_id' => $tagId]);
    }
}