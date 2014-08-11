<?php

namespace Manager;

use Symfony\Component\DomCrawler\Crawler;

class AttributeManager
{
    /**
     * Returns the name of the given Magento attribute
     *
     * @param Crawler $attributeNode Node of the Magento attribute line in product edit mode
     *                               ($crawler->filter('table.form-list tr'))
     *
     * @return string                Name of the attribute
     */
    public function getMagentoAttributeName(Crawler $attributeNode)
    {
        if ($attributeNode->filter('td.label')->getNode(0)) {
            if ($attributeNode->filter('td.label label')->getNode(0)) {
                $name = $attributeNode->filter('td.label label')->attr('for');
            } else {
                $name = $attributeNode->filter('td.label')->text();
            }
        } else {
            $name = 'Unknow name';
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
    public function getMagentoAttributeValues(Crawler $attributeNode)
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
     * Returns categories of Magento product
     * Returns ['categories' => ['categoryName 1', 'categoryName 2', ...]]
     *
     * @param Crawler $categoryNode The category node from product edit view
     *                              $crawler->filter('div#product_info_tabs_categories_content div#product-categories')
     *
     * @return array
     */
    public function getProductCategoriesAsArray(Crawler $categoryNode)
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
