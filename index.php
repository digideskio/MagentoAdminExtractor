<?php

require 'vendor/autoload.php';

use Extractor\ProductAttributeExtractor;
use Manager\MagentoAdminConnexionManager;
use Manager\NavigationManager;

const MAGENTO_ADMIN_URL   = 'http://magento.local/index.php/admin';
const MAGENTO_ADMIN_LOGIN = 'root';
const MAGENTO_ADMIN_PWD   = 'akeneo2014';

$connexionManager = new MagentoAdminConnexionManager(
    MAGENTO_ADMIN_URL,
    MAGENTO_ADMIN_LOGIN,
    MAGENTO_ADMIN_PWD
);

$mainPageCrawler           = $connexionManager->connectToAdminPage();
$client                    = $connexionManager->getClient();
$navigationManager         = new NavigationManager($connexionManager->getClient());
$productAttributeExtractor = new ProductAttributeExtractor($navigationManager);

$totalTime = microtime(true);

$products = [];
$productCatalogCrawler = $navigationManager->goToProductCatalog($mainPageCrawler);
$productCatalogCrawler->filter('table#productGrid_table tbody tr')->each(
    function ($productNode, $i) use (&$products, $productAttributeExtractor) {
        $products[] = $productAttributeExtractor->extract(
            $productNode,
            $i+1
        );
    }
);
$processProductsTime = microtime(true) - $totalTime;
printf(PHP_EOL . '%d products extracted in %fs' . PHP_EOL, count($products), $processProductsTime);
printf('Average time per product : %fs' . PHP_EOL, $processProductsTime / count($products));

//$categories = [];
//$manageCategoriesCrawler = $navigationManager->goToManageCategoriesPage($mainPageCrawler);
//$manageCategoriesCrawler->filter('div.tree-holder ')->each(
//    function ($categoryCrawler) use (&$categories) {
//        die('trolo');
//        die(var_dump($categoryCrawler));
//        $categories[] = $categoryCrawler->text();
//    }
//);


die(var_dump($products));
