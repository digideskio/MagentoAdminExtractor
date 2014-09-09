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
     * Returns [ ['param_1' => 'value', ..., 'children' => idem], [], ...]
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

        $categoriesJsonLink = $link->getUri() . 'categoriesJson/?isAjax=true';
        $script  = $categoriesPageCrawler
            ->filter('div.side-col script[type="text/javascript"]')
            ->getNode(1)
            ->textContent;
        $formKey = $categoriesPageCrawler->filter('input[name="form_key"]')->getNode(0)->getAttribute('value');

        if (preg_match('# data : (\[\{.*\}\])#', $script, $categoriesData)) {
            $rootCategories = json_decode($categoriesData[1], true);
        } else {
            $rootCategories = [];
        }

        foreach ($rootCategories as $key => $rootCategory) {
            $params = ['id' => $rootCategory['id'], 'form_key' => $formKey];

            $categories[$key]   = $rootCategory;
            $categories[$key]['children'] = $this->getCategoriesAsArray($categoriesJsonLink, $params);
        }
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
        $tempCategories    = json_decode($categoriesCrawler->getNode(0)->nodeValue, true);

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
