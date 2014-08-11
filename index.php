<?php

require 'vendor/autoload.php';

use Goutte\Client;
use Manager\AttributeManager;

const MAGENTO_ADMIN_URL = 'http://magento.local/index.php/admin';
const MAGENTO_ADMIN_LOGIN = 'root';
const MAGENTO_ADMIN_PWD = 'akeneo2014';

$client  = new Client();
$attributeManager = new AttributeManager();

$crawler = $client->request('GET', MAGENTO_ADMIN_URL);
$form    = $crawler->selectButton('Login')->form();
$crawler = $client->submit($form, ['login[username]' => MAGENTO_ADMIN_LOGIN, 'login[password]' => MAGENTO_ADMIN_PWD]);
$link    = $crawler->selectLink('Manage Products')->link();
$crawler = $client->click($link);

$products = [];
$crawler->filter('table#productGrid_table tbody tr')->each(
    function ($productNode, $i) use ($client, &$products, $attributeManager) {
        printf('Product ' . $i . PHP_EOL);
        $editLink = $productNode->selectLink('Edit')->link();
        $crawler = $client->click($editLink);
        $attributes = [];

        $crawler->filter('table.form-list tr')->each(
            function ($attributeNode) use (&$attributes, $attributeManager) {
                $attributes = array_merge(
                    $attributes,
                    $attributeManager->getMagentoAttributeAsArray($attributeNode)
                );
            }
        );

//        $sideMenuCrawler = $crawler->filter('div.side-col');
//        $categoryLink    = $sideMenuCrawler->selectLink('Categories')->link();
//        $categoryCrawler = $client->click($categoryLink);
//        $categoryNode    = $categoryCrawler->filter('div#product-categories');
//        die(var_dump($categoryNode));
//        $attributes      = array_merge(
//            $attributes,
//            $attributeManager->getProductCategoriesAsArray($categoryNode)
//        );

//        var_dump($attributes);die();

        $products[] = $attributes;
    }
);

die(var_dump($products));
