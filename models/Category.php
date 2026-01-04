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
 * @property string|null $image
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
            [['name'], 'required'],
            [['slug'], 'string', 'max' => 255],
            [['description'], 'string'],
            [['created_at', 'updated_at'], 'integer'],
            [['name', 'image'], 'string', 'max' => 255],
            [['name'], 'unique'],
            [['slug'], 'unique', 'skipOnEmpty' => true],
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
            'image' => 'Image',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Генерує slug з назви категорії
     * Викликається перед збереженням, якщо slug не вказаний
     * Забезпечує унікальність slug та обробку порожніх значень
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Генеруємо slug, якщо він порожній або містить тільки пробіли
            if (empty(trim($this->slug ?? ''))) {
                $baseSlug = Inflector::slug($this->name);
                
                // Якщо slug порожній після генерації, використовуємо ID або timestamp
                if (empty($baseSlug)) {
                    $baseSlug = 'category-' . ($this->id ?? time());
                }
                
                // Перевіряємо унікальність та додаємо суфікс при необхідності
                $slug = $baseSlug;
                $counter = 1;
                while (static::find()->where(['slug' => $slug])->andWhere(['!=', 'id', $this->id ?? 0])->exists()) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }
                
                $this->slug = $slug;
            }
            return true;
        }
        return false;
    }

    /**
     * Зв'язок з таблицею articles
     * @return \yii\db\ActiveQuery
     */
    public function getArticles()
    {
        return $this->hasMany(Article::class, ['category_id' => 'id']);
    }

    /**
     * Отримати кількість статей в категорії
     * @return int
     */
    public function getArticlesCount()
    {
        return $this->getArticles()->count();
    }
}