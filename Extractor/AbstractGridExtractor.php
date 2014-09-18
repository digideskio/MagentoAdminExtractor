<?php

namespace Akeneo\Component\MagentoAdminExtractor\Extractor;

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
     * Filters rows of a catalog grid and extracts entities one by one
     *
     * @param Crawler $gridCrawler Crawler positioned in the catalog grid of the entity
     *
     * @throws \RuntimeException if the CssSelector Component is not available
     *
     * @return array $entities Returns all entities which have been extracted
     */
    public function filterRowsAndExtract(Crawler $gridCrawler)
    {
        $entities = [];

        $gridCrawler->filter('table#' . $this->getExtractedEntity() . 'Grid_table tbody tr')->each(
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
     * Gives the current extracted entity name
     *
     * @return string
     */
    abstract protected function getExtractedEntity();

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
        if (count($attributeNode->filter('td.label')) > 0) {
            if (count($attributeNode->filter('td.label label')) > 0) {
                $name = $attributeNode->filter('td.label label')->first()->attr('for');
            } else {
                $name = $attributeNode->filter('td.label')->first()->text();
            }
        } else {
            $name = self::TAG_WARNING . ' Unknown name';
        }

        return $name;
    }

    /**
     * Returns values of given attribute
     *
     * @param Crawler $attributeNode Node of the attribute line in product edit mode
     *                               ($crawler->filter('table.form-list tr'))
     *
     * @return mixed Magento attribute values
     */
    protected function getAttributeValues(Crawler $attributeNode)
    {
        if (count($attributeNode->filter('td.value input')) > 0) {
            $type = $attributeNode->filter('td.value input')->first()->attr('type');

            switch ($type) {
                case 'text':
                    $values = $attributeNode->filter('td.value input')->first()->attr('value');
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

        } elseif (count($attributeNode->filter('td.value textarea')) > 0) {
            $values = $attributeNode->filter('td.value textarea')->first()->text();

        } elseif (count($attributeNode->filter('td.value select')) > 0) {
            if (count($attributeNode->filter('td.value select option:selected')) > 0) {
                $values = $attributeNode->filter('td.value select option:selected')->first()->text();
            } else {
                $values = 'No option selected';
            }

        } else {
            $values = self::TAG_WARNING . ' Unknown attribute type';
        }

        return $values;
    }
}
