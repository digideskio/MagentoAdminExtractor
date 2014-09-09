<?php

namespace MagentoAdminExtractor\Extractor;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Abstract grid extractor for magento
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractGridExtractor extends AbstractExtractor
{
    /**
     * Returns the attribute as array
     * Returns ['nameOfAttribute' => ['value', 'value2', ...]]
     *
     * @param Crawler $attributeNode Node of the Magento attribute line in product edit mode
     *                               ($crawler->filter('table.form-list tr'))
     *
     * @return array
     */
    public function getAttributeAsArray(Crawler $attributeNode)
    {
        $name   = $this->getAttributeName($attributeNode);
        $values = $this->getAttributeValues($attributeNode);

        return [$name => $values];
    }

    /**
     * You must implement EXTRACTED_ENTITY const in your child class
     *
     * Filters rows of a catalog grid and extracts entities one by one
     * Returns [ ['nameOfAttribute' => ['value', 'value2', ...], ...], ...]
     *
     * @param Crawler $gridCrawler Crawler positioned in the catalog grid of the entity
     *
     * @return array $entities    Returns all entities which have been extracted
     */
    public function filterRowsAndExtract(Crawler $gridCrawler)
    {
        $entities = [];

        $gridCrawler->filter('table#' . static::EXTRACTED_ENTITY . 'Grid_table tbody tr')->each(
            function ($entityNode, $i) use (&$entities) {
                $entities[] = $this->extract(
                    $entityNode,
                    $i+1
                );
            }
        );

        return $entities;
    }

    /**
     * Returns the name of the given attribute
     *
     * @param Crawler $attributeNode Node of the Magento attribute line in product edit mode
     *                               ($crawler->filter('table.form-list tr'))
     *
     * @return string Name of the attribute
     */
    protected function getAttributeName(Crawler $attributeNode)
    {
        if ($attributeNode->filter('td.label')->getNode(0)) {
            if ($attributeNode->filter('td.label label')->getNode(0)) {
                $name = $attributeNode->filter('td.label label')->attr('for');
            } else {
                $name = $attributeNode->filter('td.label')->text();
            }
        } else {
            $name = self::TAG_WARNING . ' Unknown name';
        }

        return $name;
    }

    /**
     * Returns values of given attribute
     * Returns ['value1', 'value2', ...]
     *
     * @param Crawler $attributeNode Node of the attribute line in product edit mode
     *                               ($crawler->filter('table.form-list tr'))
     *
     * @return array Magento attribute values
     */
    protected function getAttributeValues(Crawler $attributeNode)
    {
        if ($attributeNode->filter('td.value input')->getNode(0)) {
            $type = $attributeNode->filter('td.value input')->attr('type');

            switch ($type) {
                case 'text':
                    $values = $attributeNode->filter('td.value input')->attr('value');
                    break;

                case 'checkbox':
                case 'radio':
                    // Need to be tested
                    $values = [];
                    $attributeNode->filter('td.value input')->each(
                        function ($input) use (&$values) {
                            if ($input->attr('checked')) {
                                $values[] = $input->attr('value');
                            }
                        }
                    );
                    break;

                default:
                    $values = self::TAG_WARNING . ' Unknown type of input';
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
            $values = self::TAG_WARNING . ' Unknown attribute type';
        }

        return is_array($values) ? $values : [$values];
    }
}
