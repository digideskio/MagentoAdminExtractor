<?php

namespace Akeneo\Component\MagentoAdminExtractor\Extractor;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Extractor for magento categories tree
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoriesExtractor extends AbstractExtractor
{
    /**
     * Allows to extract all magento categories from the catalog categories page crawler
     * Returns ['store view label 1' => ['param_1' => 'value', ..., 'children' => idem], ...]
     * first level is root categories
     *
     * @param Crawler $mainPageCrawler
     *
     * @return array $categories Array with categories
     */
    public function extract(Crawler $mainPageCrawler)
    {
        $categories = [];
        $linkName   = 'Manage Categories';

        printf(PHP_EOL . 'Accessing to "%s" page' . PHP_EOL, $linkName);
        $link = $mainPageCrawler->selectLink($linkName)->link();
        $categoriesPageCrawler = $this->navigationManager->getClient()->click($link);

        $formKey = $categoriesPageCrawler->filter('input[name="form_key"]')->first()->attr('value');

        $categoriesPageCrawler->filter('select#store_switcher optgroup[label~="Store"] option')->each(
            function ($option) use ($link, $categoriesPageCrawler, $formKey, &$categories) {
                $storeId = $option->attr('value');
                // used in order to remove &nbsp; before the store view name
                $storeView = strtr($option->text(), array_flip(get_html_translation_table(HTML_ENTITIES, ENT_QUOTES)));
                $storeView = trim($storeView, chr(0xC2).chr(0xA0));

                $categoriesJsonLink = $link->getUri() . 'categoriesJson/?isAjax=true';
                $script             = $categoriesPageCrawler
                    ->filter('div.side-col script[type="text/javascript"]')
                    ->eq(1)
                    ->text();

                if (preg_match('# data : (\[\{.*\}\])#', $script, $categoriesData)) {
                    $rootCategories = json_decode($categoriesData[1], true);
                } else {
                    $rootCategories = [];
                }

                foreach ($rootCategories as $key => $rootCategory) {
                    $params = ['id' => $rootCategory['id'], 'form_key' => $formKey, 'store' => $storeId];
                    $categories[$storeView][$key] = $rootCategory;
                    $categories[$storeView][$key]['children'] = $this
                        ->getCategoriesAsArray($categoriesJsonLink, $params);
                }
            }
        );
        printf('Categories tree extracted' . PHP_EOL);

        return $categories;
    }

    /**
     * Returns categories tree of Magento from categoriesJson and root category id
     * Returns [ ['param_1' => 'value', ..., 'children' => idem], [], ...]
     * first level is root categories
     *
     * @param string $categoriesJsonLink Link to get categories in json in Magento
     * @param array  $params             ['form_key' => '', 'id' => id]
     *
     * @return array $categories
     */
    protected function getCategoriesAsArray($categoriesJsonLink, $params)
    {
        $categories        = [];
        $categoriesCrawler = $this->navigationManager->goToUri('POST', $categoriesJsonLink, $params);
        $tempCategories    = json_decode($categoriesCrawler->first()->text(), true);

        foreach ($tempCategories as $key => $category) {
            if (isset($category['children'])) {
                $params['id'] = $category['id'];
                $lastResult   = $this->getCategoriesAsArray($categoriesJsonLink, $params);

                $categories[$key] = $category;
                $categories[$key]['children'] = $lastResult;
            } else {
                $categories[] = $category;
            }
        }

        return $categories;
    }
}
