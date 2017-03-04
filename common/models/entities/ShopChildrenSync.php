<?php

namespace bl\cms\shop\queen\common\models\entities;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "shop_children_synch".
 *
 * @property integer $id
 * @property integer $queen_log_id
 * @property integer $child_id
 * @property integer $status
 * @property string $updated_at
 * @property string $created_at
 *
 * @property ShopChildren $child
 * @property ShopQueenLog $log
 */
class ShopChildrenSync extends ActiveRecord
{
    const STATUS_ERROR = 0;
    const STATUS_SUCCESS = 1;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_children_synch';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['queen_log_id', 'child_id', 'status'], 'integer'],
            [['updated_at', 'created_at'], 'safe'],
            [['child_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopChildren::className(), 'targetAttribute' => ['child_id' => 'id']],
            [['queen_log_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopQueenLog::className(), 'targetAttribute' => ['queen_log_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('shop/queen', 'ID'),
            'queen_log_id' => Yii::t('shop/queen', 'Queen Log ID'),
            'child_id' => Yii::t('shop/queen', 'Child ID'),
            'status' => Yii::t('shop/queen', 'Status'),
            'updated_at' => Yii::t('shop/queen', 'Updated At'),
            'created_at' => Yii::t('shop/queen', 'Created At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChild()
    {
        return $this->hasOne(ShopChildren::className(), ['id' => 'child_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLog()
    {
        return $this->hasOne(ShopQueenLog::className(), ['id' => 'queen_log_id']);
    }
}
