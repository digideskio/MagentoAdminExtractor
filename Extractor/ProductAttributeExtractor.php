<?php

namespace Extractor;

use Symfony\Component\DomCrawler\Crawler;
use Manager\NavigationManager;

class ProductAttributeExtractor
{
    /** @var NavigationManager $navigationManager */
    protected $navigationManager;

    /**
     * @param NavigationManager $navigationManager
     */
    public function __construct(NavigationManager $navigationManager)
    {
        $this->navigationManager = $navigationManager;
    }

    /**
     * Allows you to extract
     *
     * @param Crawler $productNodeCrawler Crawler positioned on the product edit page
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
                    $this->getMagentoAttributeAsArray($attributeNode)
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
     * Returns the Magento attribute as array
     * Returns ['nameOfAttribute' => ['value', 'value2', ...]]
     *
     * @param Crawler $attributeNode Node of the Magento attribute line in product edit mode
     *                               ($crawler->filter('table.form-list tr'))
     *
     * @return array
     */
    public function getMagentoAttributeAsArray(Crawler $attributeNode)
    {
        $name   = $this->getMagentoAttributeName($attributeNode);
        $values = $this->getMagentoAttributeValues($attributeNode);

        return [$name => $values];
    }

    /**
     * Returns the name of the given Magento attribute
     *
     * @param Crawler $attributeNode Node of the Magento attribute line in product edit mode
     *                               ($crawler->filter('table.form-list tr'))
     *
     * @return string                Name of the attribute
     */
    protected function getMagentoAttributeName(Crawler $attributeNode)
    {
        if ($attributeNode->filter('td.label')->getNode(0)) {
            if ($attributeNode->filter('td.label label')->getNode(0)) {
                $name = $attributeNode->filter('td.label label')->attr('for');
            } else {
                $name = $attributeNode->filter('td.label')->text();
            }
        } else {
            $name = 'Unknown name';
        }

        return $name;
    }

    /**
     * Returns values of given Magento attribute
     * Returns ['value1', 'value2', ...]
     *
     * @param Crawler $attributeNode Node of the Magento attribute line in product edit mode
     *                               ($crawler->filter('table.form-list tr'))
     *
     * @return array                 Magento attribute values
     */
    protected function getMagentoAttributeValues(Crawler $attributeNode)
    {
        if ($attributeNode->filter('td.value input')->getNode(0)) {
            $type = $attributeNode->filter('td.value input')->attr('type');

            switch ($type) {
                case 'text':
                    $values = $attributeNode->filter('td.value input')->attr('value');
                    break;

                case 'checkbox':
                case 'radio':
                    // To be tested
                    $values = [];
                    $attributeNode->filter('td.value input')->each(
                        function($input) use (&$values) {
                            if ($input->attr('checked')) {
                                $values[] = $input->attr('value');
                            }
                        }
                    );
                    break;

                default:
                    $values = 'Unknown type of input';
                    break;
            }

        } elseif ($attributeNode->filter('td.value textarea')->getNode(0)) {
            $values = $attributeNode->filter('td.value textarea')->text();

        } elseif ($attributeNode->filter('td.value select')->getNode(0)) {

            if ($attributeNode->filter('td.value select option:selected')->getNode(0)) {
                $values = $attributeNode->filter('td.value select option:selected')->text();
            } else {
                $values = 'No option selected';
            }

        } else {
            $values = 'Unknown attribute type';
        }

        return is_array($values) ? $values : [$values];
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
