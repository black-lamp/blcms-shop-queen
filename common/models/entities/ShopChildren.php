<?php

namespace bl\cms\shop\queen\common\models\entities;

use common\modules\partner\common\entities\PartnerCompany;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "shop_children".
 *
 * @property integer $id
 * @property string $domain_name
 * @property string $site_name
 * @property integer $company_id
 * @property string $updated_at
 * @property string $created_at
 *
 * @property PartnerCompany $company
 * @property ShopChildrenSync[] $synchronizations
 */
class ShopChildren extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_children';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['domain_name', 'company_id'], 'integer'],
            [['updated_at', 'created_at'], 'safe'],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => PartnerCompany::className(), 'targetAttribute' => ['company_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('shop/queen', 'ID'),
            'domain_name' => Yii::t('shop/queen', 'Domain Name'),
            'company_id' => Yii::t('shop/queen', 'Company ID'),
            'updated_at' => Yii::t('shop/queen', 'Updated At'),
            'created_at' => Yii::t('shop/queen', 'Created At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(PartnerCompany::className(), ['id' => 'company_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSynchronizations()
    {
        return $this->hasMany(ShopChildrenSync::className(), ['child_id' => 'id']);
    }
}
