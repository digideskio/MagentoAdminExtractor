<?php

namespace Extractor;

use Symfony\Component\DomCrawler\Crawler;

class ProductAttributeExtractor extends AbstractExtractor
{
    /**
     * Allows you to extract product attributes
     *
     * @param Crawler $productNodeCrawler Crawler positioned on the product in catalog page
     *                                    ex : $productCatalogCrawler->filter('table#productGrid_table tbody tr')
     * @param mixed   $productName
     *
     * @return array  $attributes         Array with attributes of product
     */
    public function extract(
        Crawler $productNodeCrawler,
        $productName
    ) {
        printf(PHP_EOL . 'Accessing to product %s edit page' . PHP_EOL, $productName);
        $crawler = $this->navigationManager->goToLink($productNodeCrawler, 'Edit');
        $attributes = [];

        printf('Processing attributes' . PHP_EOL);
        $crawler->filter('table.form-list tr')->each(
            function ($attributeNode) use (&$attributes) {
                $attributes = array_merge(
                    $attributes,
                    $this->getAttributeAsArray($attributeNode)
                );
            }
        );
        printf('%d attributes processed' . PHP_EOL, count($attributes));

//        CATEGORIES
//        $sideMenuCrawler = $crawler->filter('div.side-col');
//        $categoryLink    = $sideMenuCrawler->selectLink('Categories')->link();
//        $categoryCrawler = $client->click($categoryLink);
//        $categoryNode    = $categoryCrawler->filter('div#product-categories');
//        die(var_dump($categoryNode));
//        $attributes      = array_merge(
//            $attributes,
//            $attributeManager->getProductCategoriesAsArray($categoryNode)
//        );

        return $attributes;
    }

    /**
     * Returns categories of Magento product
     * Returns ['categories' => ['categoryName 1', 'categoryName 2', ...]]
     *
     * @param Crawler $categoryNode The category node from product edit view
     *                              $crawler->filter('div#product_info_tabs_categories_content div#product-categories')
     *
     * @return array
     */
    protected function getProductCategoriesAsArray(Crawler $categoryNode)
    {
        $categories = [];

        $categoryNode->filter('input[type="checkbox"]')->each(
            function ($category) use (&$categories) {
                $categories[] = $category->text();
            }
        );

        return ['categories' => $categories];
    }
}
