<?php

namespace Akeneo\Component\MagentoAdminExtractor\Extractor;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Extractor for magento product attributes
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAttributeExtractor extends AbstractGridExtractor
{
    const MAGENTO_ROOT_CATEGORY_ID = 1;

    /**
     * Allows to extract product attributes
     * Returns [['store view label' => ['nameOfAttribute' => ['value', 'value2', ...], ...], ...], ...]
     *
     * @param Crawler $productNodeCrawler Crawler positioned on the product in catalog page
     *                                    ex : $productCatalogCrawler->filter('table#productGrid_table tbody tr')
     * @param mixed   $productName        Name of the product which will be display in terminal
     *
     * @return array $attributes Array with attributes of product
     */
    public function extract(Crawler $productNodeCrawler, $productName = '')
    {
        $productAttributes = [];

        printf(PHP_EOL . 'Accessing to product %s edit page' . PHP_EOL, $productName);
        $link    = $productNodeCrawler->selectLink('Edit')->link();
        $crawler = $this->navigationManager->getClient()->click($link);

        printf('Processing attributes' . PHP_EOL);
        $crawler->filter('select#store_switcher optgroup[label~="Store"] option')->each(
            function($option) use (&$productAttributes, $link) {
                $storeId = $option->attr('value');

                // used in order to remove &nbsp; before the store view name
                $storeView = strtr($option->text(), array_flip(get_html_translation_table(HTML_ENTITIES, ENT_QUOTES)));
                $storeView = trim($storeView, chr(0xC2).chr(0xA0));

                $productLink    = $link->getUri() . 'store/' . $storeId;
                $productCrawler = $this->navigationManager->goToUri('GET', $productLink);

                $attributes = [];
                $productCrawler->filter('table.form-list tr')->each(
                    function ($attributeNode) use (&$attributes) {
                        $attributes = array_merge(
                            $attributes,
                            $this->getAttributeAsArray($attributeNode)
                        );
                    }
                );

                $productAttributes[$storeView] = $attributes;
            }
        );

        $sideMenuCrawler    = $crawler->filter('div.side-col');
        $categoryLink       = $sideMenuCrawler->filter('a#product_info_tabs_categories')->first()->attr('href');
        $categoryLink      .= '?isAjax=true';
        $categoriesJsonLink = preg_replace('/categories/', 'categoriesJson', $categoryLink);
        $formKey            = $crawler->filter('input[name="form_key"]')->first()->attr('value');
        $categoryId         = self::MAGENTO_ROOT_CATEGORY_ID;

        $productAttributes['categories'] = $this
            ->getProductCategoriesAsArray($categoriesJsonLink, ['form_key' => $formKey, 'category' => $categoryId]);

        if (count($crawler->filter('a#product_info_tabs_configurable')->first()) > 0) {
            $productAttributes['type'] = 'configurable';
            $productAttributes['associated'] = $this->getAssociatedProducts($crawler);
        } else {
            $productAttributes['type'] = 'simple';
        }

        $count = 0;
        foreach ($productAttributes as $attributes) {
            $count += count($attributes);
        }
        printf('%d attributes processed' . PHP_EOL, $count);

        return $productAttributes;
    }

    /**
     * Returns categories of Magento product
     * Returns ['categoryName 1', 'categoryName 2', ...]
     * Recursive method
     *
     * @param string $categoriesJsonLink Link to get categories in json in Magento
     * @param array  $params             ['form_key' => '', 'category' => id]
     *
     * @return null|array
     */
    protected function getProductCategoriesAsArray($categoriesJsonLink, $params)
    {
        $categories        = [];
        $categoriesCrawler = $this->navigationManager->goToUri('POST', $categoriesJsonLink, $params);
        $tempCategories    = json_decode($categoriesCrawler->first()->text(), true);

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

        return empty($categories) ? null : $categories;
    }

    /**
     * Give associated products if the product is a configurable
     * Return [['attribute product 1' => 'value', ...], ['attr product 2' => 'value', ...], ...]
     *
     * @param Crawler $crawler Crawler on the product page
     *
     * @return array
     */
    protected function getAssociatedProducts(Crawler $crawler)
    {
        $headers = ['checked'];
        $crawler->filter('table#super_product_links_table tr.headings th')->each(
            function ($header) use (&$headers) {
                $text = $header->text();
                if (!empty($text)) {
                    $headers[] = $text;
                }
            }
        );

        $associatedProducts = [];
        $crawler->filter('table#super_product_links_table tr')->each(
            function ($row, $i) use (&$associatedProducts, $headers) {
                $row->filter('td')->each(
                    function ($column, $j) use (&$associatedProducts, $headers, $i) {
                        $text = trim($column->text());
                        $html = $column->html();

                        if (empty($text) && $j === 0 && !empty($html)) {
                            $associatedProducts[$i][$headers[$j]] = $column
                                ->filter('input[type="checkbox"]')
                                ->first()
                                ->attr('checked');
                        } else {
                            $associatedProducts[$i][$headers[$j]] = $text;
                        }
                    }
                );

                unset($associatedProducts[$i]['Action']);
            }
        );

        return array_values($associatedProducts);
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtractedEntity()
    {
        return 'product';
    }
}
