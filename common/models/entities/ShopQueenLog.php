<?php

namespace bl\cms\shop\queen\common\models\entities;

use common\models\User;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "shop_queen_log".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $entity_name
 * @property integer $entity_id
 * @property integer $action_id
 * @property string $created_at
 *
 * @property ShopChildrenSync[] $synchronizations
 * @property User $user
 */
class ShopQueenLog extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_queen_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'entity_id', 'action_id'], 'integer'],
            [['created_at'], 'safe'],
            [['entity_name'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('shop/queen', 'ID'),
            'user_id' => Yii::t('shop/queen', 'User ID'),
            'entity_name' => Yii::t('shop/queen', 'Entity Name'),
            'entity_id' => Yii::t('shop/queen', 'Entity ID'),
            'action_id' => Yii::t('shop/queen', 'Action ID'),
            'created_at' => Yii::t('shop/queen', 'Created At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSynchronizations()
    {
        return $this->hasMany(ShopChildrenSync::className(), ['queen_log_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
