<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Simple junction model for articles and tags table
 */
class ArticleTag extends ActiveRecord
{
    public static function tableName()
    {
        return 'article_tag';
    }
}
