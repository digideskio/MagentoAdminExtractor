<?php

namespace Akeneo\Component\MagentoAdminExtractor;

require 'vendor/autoload.php';

use Akeneo\Component\MagentoAdminExtractor\Extractor\ProductAttributeExtractor;
use Akeneo\Component\MagentoAdminExtractor\Extractor\AttributeExtractor;
use Akeneo\Component\MagentoAdminExtractor\Extractor\CategoriesExtractor;
use Akeneo\Component\MagentoAdminExtractor\Manager\LogInException;
use Akeneo\Component\MagentoAdminExtractor\Manager\MagentoAdminConnectionManager;
use Akeneo\Component\MagentoAdminExtractor\Manager\NavigationManager;

const MAGENTO_ADMIN_URL   = 'http://magento.local/index.php/admin';
const MAGENTO_ADMIN_LOGIN = 'root';
const MAGENTO_ADMIN_PWD   = 'akeneo2014';

$connectionManager = new MagentoAdminConnectionManager(
    MAGENTO_ADMIN_URL,
    MAGENTO_ADMIN_LOGIN,
    MAGENTO_ADMIN_PWD
);

try {
    $mainPageCrawler = $connectionManager->connectToAdminPage();
    $client          = $connectionManager->getClient();
} catch (LogInException $e) {
    die($e->getMessage() . PHP_EOL);
}

$navigationManager         = new NavigationManager($client);
$productAttributeExtractor = new ProductAttributeExtractor($navigationManager);
$attributeExtractor        = new AttributeExtractor($navigationManager);
$categoriesExtractor       = new CategoriesExtractor($navigationManager);

$totalTime = microtime(true);

/*
 * Products extraction
 */
$timeFromBeginning = microtime(true);
$productCatalogCrawler = $navigationManager->goToProductCatalog($mainPageCrawler, 50);
$products = $productAttributeExtractor->filterRowsAndExtract($productCatalogCrawler);
$processProductsTime = microtime(true) - $timeFromBeginning;
printf(PHP_EOL . '%d products extracted in %fs' . PHP_EOL, count($products), $processProductsTime);
printf('Average time per product : %fs' . PHP_EOL, $processProductsTime / count($products));
printf('/******************************/' . PHP_EOL . PHP_EOL);

/*
 * Attributes extraction
 */
$timeFromBeginning = microtime(true);
$attributeCatalogCrawler = $navigationManager->goToAttributeCatalog($mainPageCrawler, 50);
$attributes = $attributeExtractor->filterRowsAndExtract($attributeCatalogCrawler);
$processAttributesTime = microtime(true) - $timeFromBeginning;
printf(PHP_EOL . '%d attributes extracted in %fs' . PHP_EOL, count($attributes), $processAttributesTime);
printf('Average time per attribute : %fs' . PHP_EOL, $processAttributesTime / count($attributes));
printf('/******************************/' . PHP_EOL . PHP_EOL);

/*
 * Categories extraction
 */
$timeFromBeginning = microtime(true);
$categories = $categoriesExtractor->extract($mainPageCrawler);
$processCategoriesTime = microtime(true) - $timeFromBeginning;
printf('Categories tree extracted in %fs' . PHP_EOL, $processCategoriesTime);

printf(PHP_EOL . 'Full extraction done in : %fs' . PHP_EOL, microtime(true) - $totalTime);
