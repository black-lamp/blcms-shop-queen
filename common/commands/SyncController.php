<?php
namespace bl\cms\shop\queen\common\commands;
use bl\cms\shop\common\entities\Currency;
use bl\cms\shop\queen\common\models\entities\ShopChildren;
use bl\cms\shop\queen\common\models\entities\ShopChildrenSync;
use bl\cms\shop\queen\common\models\entities\ShopQueenLog;
use yii\console\Controller;
use yii\httpclient\Client;

/**
 * @author Gutsulyak Vadim <guts.vadim@gmail.com>
 */
class SyncController extends Controller
{
    public function actionIndex() {
        echo 'Sync...';

        $sites = ShopChildren::find()->all();
        foreach ($sites as $site) {
            $logs = ShopQueenLog::findUnapplied($site);

            echo count($logs) . ' logs to apply';

            foreach ($logs as $log) {
                $requestUrl = '';
                $requestData = [];

                switch ($log->entity_name) {
                    case Currency::className(): {
                        $requestUrl = '/subsite/rest/currency/update';
                        $requestData = [
                            'value' => Currency::findOne($log->entity_id)->value
                        ];
                        break;
                    }
                }

                $client = new Client();
                $response = $client->createRequest()
                    ->setMethod('get')
                    ->setUrl($site->domain_name . $requestUrl)
                    ->setData($requestData)
                    ->send();

                echo $response->statusCode;

                $sync = new ShopChildrenSync();
                $sync->child_id = $site->id;
                $sync->queen_log_id = $log->id;
                $sync->status = $response->isOk ? ShopChildrenSync::STATUS_SUCCESS : ShopChildrenSync::STATUS_ERROR;
                $sync->save();
            }
        }

        echo 'Done!';
    }
}