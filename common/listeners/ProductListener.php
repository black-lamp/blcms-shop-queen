<?php
namespace bl\cms\shop\queen\common\listeners;

use bl\cms\shop\backend\controllers\ProductController;
use yii\base\BootstrapInterface;
use yii\base\Event;

/**
 * @author Gutsulyak Vadim <guts.vadim@gmail.com>
 */

class ProductListener implements BootstrapInterface
{
    public function bootstrap($app)
    {
        Event::on(ProductController::className(),
            ProductController::EVENT_AFTER_CREATE_PRODUCT, [$this, 'logCreated']);
        Event::on(ProductController::className(),
            ProductController::EVENT_AFTER_EDIT_PRODUCT, [$this, 'logEdited']);
        Event::on(ProductController::className(),
            ProductController::EVENT_AFTER_DELETE_PRODUCT, [$this, 'addLogDeleted']);
    }

    public function addLogCreated($event) {
        // TODO: log
    }

}