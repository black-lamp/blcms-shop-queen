<?php
namespace bl\cms\shop\queen\common\commands;

use bl\cms\shop\common\entities\Category;
use bl\cms\shop\common\entities\Currency;
use bl\cms\shop\common\entities\ProductCountry;
use bl\cms\shop\common\entities\Vendor;
use bl\cms\shop\queen\common\models\entities\ShopChildren;
use bl\cms\shop\queen\common\models\entities\ShopChildrenSync;
use bl\cms\shop\queen\common\models\entities\ShopQueenLog;
use yii\console\Controller;
use yii\helpers\Console;
use yii\httpclient\Client;
use yii\httpclient\JsonParser;

/**
 * @author Gutsulyak Vadim <guts.vadim@gmail.com>
 */
class SyncController extends Controller
{
    public function actionIndex() {
        /* @var $sites ShopChildren[] */

        $errors = 0;
        $sites = ShopChildren::find()->all();
        foreach ($sites as $site) {
            $logs = ShopQueenLog::findUnapplied($site);

            echo $site->domain_name . " - " . count($logs) . ' logs to apply:' . "\n";

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
                    case ProductCountry::className(): {
                        $requestUrl = '/subsite/rest/country/update';
                        $requestData = ProductCountry::find()
                            ->where(['id' => $log->entity_id])
                            ->with('translations')
                            ->one();
                        break;
                    }
                    case Vendor::className(): {
                        $requestUrl = '/subsite/rest/vendor/update';
                        $requestData = Vendor::find()
                            ->where(['id' => $log->entity_id])
                            ->with('translations')
                            ->one();
                        break;
                    }
                    case Category::className(): {
                        $requestUrl = '/subsite/rest/category/update';
                        $requestData = Category::find()
                            ->where(['id' => $log->entity_id])
                            ->with('translations')
                            ->one();
                        break;
                    }
                }

                $client = new Client([
                    'requestConfig' => [
                        'format' => Client::FORMAT_JSON
                    ],
                    'responseConfig' => [
                        'format' => Client::FORMAT_JSON
                    ],
                ]);

                $message = $client->createRequest()
                    ->setMethod('post')
                    ->setFormat(Client::FORMAT_JSON)
                    ->setUrl($site->domain_name . $requestUrl)
                    ->setData($requestData);

                $response = $message->send();

                $sync = new ShopChildrenSync();
                $sync->child_id = $site->id;
                $sync->queen_log_id = $log->id;
                $sync->status = $response->isOk ? ShopChildrenSync::STATUS_SUCCESS : ShopChildrenSync::STATUS_ERROR;
                $sync->save();

                if(!$response->isOk) {
                    $errors++;
                    $this->stdout("{$response->content} \n", Console::FG_GREY);
                }

                $this->stdout("{$response->statusCode} - {$site->domain_name}{$requestUrl} \n", Console::FG_GREY);
//                $this->stdout("{$message->content} \n", Console::FG_GREY);
            }
        }
        $this->stdout("Done with {$errors} errors \n", Console::FG_GREY);
    }
}