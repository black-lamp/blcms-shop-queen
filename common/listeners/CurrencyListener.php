<?php
namespace bl\cms\shop\queen\common\listeners;

use bl\cms\shop\backend\controllers\CurrencyController;
use bl\cms\shop\common\entities\Currency;
use bl\cms\shop\queen\common\models\entities\ShopQueenLog;
use yii\base\BootstrapInterface;
use yii\base\Event;

/**
 * @author Gutsulyak Vadim <guts.vadim@gmail.com>
 */

class CurrencyListener implements BootstrapInterface
{

    public function bootstrap($app)
    {
        Event::on(CurrencyController::className(),
            CurrencyController::EVENT_AFTER_CHANGE, function() {
                ShopQueenLog::log(Currency::findCurrent(), ShopQueenLog::ACTION_UPDATE);
            });
    }

}