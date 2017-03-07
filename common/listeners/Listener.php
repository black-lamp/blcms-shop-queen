<?php
namespace bl\cms\shop\queen\common\listeners;

use bl\cms\shop\backend\components\events\CategoryEvent;
use bl\cms\shop\backend\components\events\CountryEvent;
use bl\cms\shop\backend\components\events\VendorEvent;
use bl\cms\shop\backend\controllers\CategoryController;
use bl\cms\shop\backend\controllers\CountryController;
use bl\cms\shop\backend\controllers\CurrencyController;
use bl\cms\shop\backend\controllers\VendorController;
use bl\cms\shop\common\entities\Category;
use bl\cms\shop\common\entities\Currency;
use bl\cms\shop\queen\common\models\entities\ShopQueenLog;
use yii\base\BootstrapInterface;
use yii\base\Event;

/**
 * @author Gutsulyak Vadim <guts.vadim@gmail.com>
 */

class Listener implements BootstrapInterface
{

    public function bootstrap($app)
    {
        // Currency listener
        Event::on(CurrencyController::className(),
            CurrencyController::EVENT_AFTER_CHANGE, function() {
                ShopQueenLog::log(Currency::findCurrent(), ShopQueenLog::ACTION_UPDATE);
            });

        // Country listener
        Event::on(CountryController::className(),
            CountryController::EVENT_AFTER_CREATE_OR_UPDATE_COUNTRY, function(CountryEvent $event) {
                ShopQueenLog::log($event->country, ShopQueenLog::ACTION_UPDATE);
            });

        // Vendor listener
        Event::on(VendorController::className(),
            VendorController::EVENT_AFTER_CREATE_OR_UPDATE_VENDOR, function(VendorEvent $event) {
                ShopQueenLog::log($event->vendor, ShopQueenLog::ACTION_UPDATE);
            });

        // Category listener
        Event::on(CategoryController::className(),
            CategoryController::EVENT_AFTER_CREATE_CATEGORY, function(CategoryEvent $event) {
                ShopQueenLog::log(Category::findOne($event->id), ShopQueenLog::ACTION_CREATE);
            });
        Event::on(CategoryController::className(),
            CategoryController::EVENT_AFTER_EDIT_CATEGORY, function(CategoryEvent $event) {
                ShopQueenLog::log(Category::findOne($event->id), ShopQueenLog::ACTION_UPDATE);
            });
    }

}