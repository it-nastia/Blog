<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Inflector;

/**
 * Tag model
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property int $created_at
 *
 * @property Article[] $articles
 */
class Tag extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tags';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false, // Вимікаємо updated_at, т.к. його немає в таблиці
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'slug'], 'required'],
            [['created_at'], 'integer'],
            [['name', 'slug'], 'string', 'max' => 255],
            [['name'], 'unique'],
            [['slug'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'slug' => 'Slug',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Генерує slug з назви тега
     * Викликається перед збереженням, якщо slug не вказаний
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if (empty($this->slug)) {
                $this->slug = Inflector::slug($this->name);
            }
            return true;
        }
        return false;
    }

    /**
     * Зв'язок many-to-many з таблицею articles через article_tag
     * @return \yii\db\ActiveQuery
     */
    public function getArticles()
    {
        return $this->hasMany(Article::class, ['id' => 'article_id'])
            ->viaTable('article_tag', ['tag_id' => 'id']);
    }

    /**
     * Отримати кількість статей з цим тегом
     * @return int
     */
    public function getArticlesCount()
    {
        return $this->getArticles()->count();
    }
}