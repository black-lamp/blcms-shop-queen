<?php
namespace bl\cms\shop\queen\common\commands;
use yii\console\Controller;

/**
 * @author Gutsulyak Vadim <guts.vadim@gmail.com>
 */
class SyncController extends Controller
{
    public function actionIndex() {
        echo 'Sync...';
        echo 'Done!';
    }
}