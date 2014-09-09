<?php

namespace MagentoExtractor;

require 'vendor/autoload.php';

use MagentoExtractor\Extractor\ProductAttributeExtractor;
use MagentoExtractor\Extractor\AttributeExtractor;
use MagentoExtractor\Extractor\CategoriesExtractor;
use MagentoExtractor\Manager\MagentoAdminConnexionManager;
use MagentoExtractor\Manager\NavigationManager;

const MAGENTO_ADMIN_URL   = 'http://magento.local/index.php/admin';
const MAGENTO_ADMIN_LOGIN = 'some_login';
const MAGENTO_ADMIN_PWD   = 'some_pwd';

$connexionManager = new MagentoAdminConnexionManager(
    MAGENTO_ADMIN_URL,
    MAGENTO_ADMIN_LOGIN,
    MAGENTO_ADMIN_PWD
);

$mainPageCrawler           = $connexionManager->connectToAdminPage();
$client                    = $connexionManager->getClient();
$navigationManager         = new NavigationManager($client);
$productAttributeExtractor = new ProductAttributeExtractor($navigationManager);
$attributeExtractor        = new AttributeExtractor($navigationManager);
$categoriesExtractor       = new CategoriesExtractor($navigationManager);

$totalTime = microtime(true);

/*
 * Products extraction
 */
$timeFromBeginning = microtime(true);
$productCatalogCrawler = $navigationManager->goToProductCatalog($mainPageCrawler);
$products = $productAttributeExtractor->filterRowsAndExtract($productCatalogCrawler);
$processProductsTime = microtime(true) - $timeFromBeginning;
printf(PHP_EOL . '%d products extracted in %fs' . PHP_EOL, count($products), $processProductsTime);
printf('Average time per product : %fs' . PHP_EOL, $processProductsTime / count($products));
printf('/******************************/' . PHP_EOL . PHP_EOL);

/*
 * Attributes extraction
 */
$timeFromBeginning = microtime(true);
$attributeCatalogCrawler = $navigationManager->goToAttributeCatalog($mainPageCrawler);
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
