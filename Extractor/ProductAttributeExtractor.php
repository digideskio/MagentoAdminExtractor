<?php

namespace Extractor;

use Symfony\Component\DomCrawler\Crawler;

class ProductAttributeExtractor extends AbstractExtractor
{
    const MAGENTO_ROOT_CATEGORY_ID = 1;
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

        $sideMenuCrawler    = $crawler->filter('div.side-col');
        $categoryLink       = $sideMenuCrawler->filter('a#product_info_tabs_categories')->getNode(0)->getAttribute('href');
        $categoryLink      .= '?isAjax=true';
        $categoriesJsonLink = preg_replace('/categories/', 'categoriesJson', $categoryLink);
        $params['form_key'] = $crawler->filter('input[name="form_key"]')->getNode(0)->getAttribute('value');
        $params['category'] = self::MAGENTO_ROOT_CATEGORY_ID;

        $attributes['categories'] = $this->getProductCategoriesAsArray($categoriesJsonLink, $params);
        printf('%d attributes processed' . PHP_EOL, count($attributes));

        return $attributes;
    }

    /**
     * Returns categories of Magento product
     * Returns ['categories' => ['categoryName 1', 'categoryName 2', ...]]
     *
     * @param string $categoriesJsonLink
     * @param array  $params             ['form_key' => '', 'category' => id]
     *
     * @return array
     */
    protected function getProductCategoriesAsArray($categoriesJsonLink, $params)
    {
        $categories = [];
        $categoriesCrawler = $this->navigationManager->goToUri('POST', $categoriesJsonLink, $params);
        $tempCategories = json_decode($categoriesCrawler->getNode(0)->nodeValue, true);

        foreach ($tempCategories as $category) {
            if (isset($category['children'])) {
                $params['category'] = $category['id'];
                $lastResult = $this->getProductCategoriesAsArray($categoriesJsonLink, $params);

                if (is_array($lastResult) && !empty($lastResult)) {
                    $categories = array_merge($categories, $lastResult);
                }
                if (isset($category['checked']) && true === $category['checked']) {
                    $categories[] = $category['text'];
                }
            } else {
                if (isset($category['checked']) && true === $category['checked']) {
                    $categories[] = $category['text'];
                }
            }
        }

        return empty($categories) ? false : $categories;
    }
}
