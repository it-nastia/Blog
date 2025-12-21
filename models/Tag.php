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
                'updatedAtAttribute' => false, // Отключаем updated_at, т.к. его нет в таблице
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
     * Генерирует slug из названия тега
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
     * Связь many-to-many с таблицей articles через article_tag
     * @return \yii\db\ActiveQuery
     */
    public function getArticles()
    {
        return $this->hasMany(Article::class, ['id' => 'article_id'])
            ->viaTable('article_tag', ['tag_id' => 'id']);
    }

    /**
     * Получить количество статей с этим тегом
     * @return int
     */
    public function getArticlesCount()
    {
        return $this->getArticles()->count();
    }
}