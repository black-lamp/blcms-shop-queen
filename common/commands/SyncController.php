<?php
namespace bl\cms\shop\queen\common\commands;

use bl\cms\shop\common\entities\Category;
use bl\cms\shop\common\entities\Currency;
use bl\cms\shop\common\entities\Product;
use bl\cms\shop\common\entities\ProductAvailability;
use bl\cms\shop\common\entities\ProductCountry;
use bl\cms\shop\common\entities\ShopAttribute;
use bl\cms\shop\common\entities\Vendor;
use bl\cms\shop\queen\common\models\entities\ShopChildren;
use bl\cms\shop\queen\common\models\entities\ShopChildrenSync;
use bl\cms\shop\queen\common\models\entities\ShopQueenLog;
use Yii;
use yii\console\Controller;
use yii\helpers\Console;
use yii\httpclient\Client;

/**
 * @author Gutsulyak Vadim <guts.vadim@gmail.com>
 */
class SyncController extends Controller
{
    private $logCounter = 0;

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

                $this->stdout("{$log->entity_name} - {$log->entity_id} \n");

                switch ($log->entity_name) {
                    case Currency::className(): {
                        $requestUrl = '/subsite/rest/currency/update';
                        $requestData = [
                            'value' => Currency::currentCurrency()
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
                    case ProductAvailability::className(): {
                        $requestUrl = '/subsite/rest/availability/update';
                        $requestData = ProductAvailability::find()
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
                    case ShopAttribute::className(): {
                        $requestUrl = '/subsite/rest/attribute/update';
                        $requestData = ShopAttribute::find()
                            ->where(['id' => $log->entity_id])
                            ->with(['translations', 'attributeValues'])
                            ->one();
                        break;
                    }
                    case Product::className(): {
                        $requestUrl = '/subsite/rest/product/update';
                        $requestData = Product::find()
                            ->where(['id' => $log->entity_id])
                            ->with([
                                'translations',
                                'params',
                                'productAvailability',
                                'productPrices',
                                'images',
                                'combinations',
                                'productAdditionalProducts'
                            ])
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
                $this->stdout("{$message->content} \n", Console::FG_GREY);
            }
        }
        $this->stdout("Done with {$errors} errors \n", Console::FG_GREY);
    }

    public function actionRegisterLog() {
        ShopQueenLog::log(Currency::findCurrent(), ShopQueenLog::ACTION_UPDATE);

        $this->logCounter= 0;
        foreach (ProductCountry::find()->all() as $country) {
            ShopQueenLog::log($country, ShopQueenLog::ACTION_UPDATE);
            $this->logCounter++;
        }
        $this->stdout("{$this->logCounter} Countries logged \n", Console::FG_GREY);


        $this->logCounter= 0;
        foreach (Vendor::find()->all() as $vendor) {
            ShopQueenLog::log($vendor, ShopQueenLog::ACTION_UPDATE);
            $this->logCounter++;
        }
        $this->stdout("{$this->logCounter} Vendors logged \n", Console::FG_GREY);

        $this->logCounter= 0;
        foreach (ShopAttribute::find()->all() as $attribute) {
            ShopQueenLog::log($attribute, ShopQueenLog::ACTION_UPDATE);
            $this->logCounter++;
        }
        $this->stdout("{$this->logCounter} Attributes logged \n", Console::FG_GREY);

        $this->logCounter= 0;
        foreach (ProductAvailability::find()->all() as $availability) {
            ShopQueenLog::log($availability, ShopQueenLog::ACTION_UPDATE);
            $this->logCounter++;
        }
        $this->stdout("{$this->logCounter} Product Availabilities logged \n", Console::FG_GREY);

        $this->logCounter= 0;
        foreach (Category::findAll(['parent_id' => null]) as $category) {
            $this->logCategory($category);
        }
        $this->stdout("{$this->logCounter} Categories logged \n", Console::FG_GREY);

        $this->logCounter= 0;
        foreach (Product::find()->all() as $product) {
            ShopQueenLog::log($product, ShopQueenLog::ACTION_CREATE);
            $this->logCounter++;
        }
        $this->stdout("{$this->logCounter} Products logged \n", Console::FG_GREY);
    }

    public function actionFiles() {
        $imagesDir = Yii::getAlias("@frontend/web/images/");
        $syncDirs = [
            'shop',
            'shop-category',
            'shop-product',
            'shop-product-country',
            'shop-vendors'
        ];


        $sites = ShopChildren::find()->all();
        foreach ($sites as $site) {
            if(!empty($site->site_name)) {
                $destImagesDir = Yii::getAlias("@domains/" . $site->site_name . "/public_html/frontend/web/images/");

                foreach ($syncDirs as $syncDir) {
                    $sourceDir = $imagesDir . $syncDir . "/*";
                    $destDir = $destImagesDir . $syncDir;

                    $this->stdout("from $sourceDir to $destDir \n");

                    if(!file_exists($destDir)) {
                        mkdir($destDir);
                    }
                    $result = shell_exec("cp -Rnv $sourceDir $destDir");

                    $this->stdout("$result \n");
                }
            }
        }

        echo $imagesDir;
    }

    /**
     * @param Category $category
     */
    private function logCategory($category) {
        ShopQueenLog::log($category, ShopQueenLog::ACTION_CREATE);
        $this->logCounter++;
        if(!empty($category->categories)) {
            foreach ($category->categories as $subCategory) {
                $this->logCategory($subCategory);
            }
        }
    }
}