<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Inflector;

/**
 * Category model
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Article[] $articles
 */
class Category extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'categories';
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
            [['name', 'slug'], 'required'],
            [['description'], 'string'],
            [['created_at', 'updated_at'], 'integer'],
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
            'description' => 'Description',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Генерирует slug из названия категории
     * Вызывается перед сохранением, если slug не указан
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
     * Связь с таблицей articles
     * @return \yii\db\ActiveQuery
     */
    public function getArticles()
    {
        return $this->hasMany(Article::class, ['category_id' => 'id']);
    }

    /**
     * Получить количество статей в категории
     * @return int
     */
    public function getArticlesCount()
    {
        return $this->getArticles()->count();
    }
}