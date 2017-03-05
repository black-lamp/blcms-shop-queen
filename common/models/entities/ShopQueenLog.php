<?php

namespace bl\cms\shop\queen\common\models\entities;

use common\models\User;
use Yii;
use yii\base\InvalidParamException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

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
    const ACTION_CREATE = 1;
    const ACTION_UPDATE = 2;
    const ACTION_DELETE = 3;

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'created_at',
                'value' => new Expression('NOW()')
            ]
        ];
    }

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

    /**
     * Logs an queen shop event
     *
     * @param BaseActiveRecord $entity
     * @param integer $action action id
     * @return mixed the property value
     * @throws InvalidParamException if the property is not defined
     */
    public static function log($entity, $action) {
        if(!($entity instanceof BaseActiveRecord)) {
            throw new InvalidParamException();
        }

        $log = new ShopQueenLog();
        $log->entity_id = $entity->getPrimaryKey();
        $log->entity_name = $entity->className();
        $log->action_id = $action;
        $log->user_id = Yii::$app->user->id;
        $log->save();
    }

    /**
     * @param $site ShopChildren
     * @return ShopQueenLog[]
     */
    public static function findUnapplied($site) {
        $logs = ShopQueenLog::find()
            ->joinWith(['synchronizations sync'])
            ->where([
                'sync.child_id' => $site->id
            ])
            ->andWhere(['!=', 'sync.status', ShopChildrenSync::STATUS_ERROR])
            ->groupBy(['sync.queen_log_id'])
            ->all();

        return ShopQueenLog::find()
            ->where(['not in', 'id', ArrayHelper::getColumn($logs, 'id')])
            ->orderBy(['created_at' => SORT_ASC])
            ->all();
    }
}
