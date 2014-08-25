<?php

namespace Extractor;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Extractor for magento attributes
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeExtractor extends AbstractExtractor
{
    /**
     * Allows you to extract product attributes
     *
     * @param Crawler $attributeCrawler Crawler positioned on the attribute in catalog page
     *                                  ex : $attributeCatalogCrawler->filter('table#attributeGrid_table tbody tr')
     * @param mixed   $attributeName
     *
     * @return array  $attributes       Array with parameters of attribute
     */
    public function extract(
        Crawler $attributeCrawler,
        $attributeName = ''
    ) {
        printf(PHP_EOL . 'Accessing to attribute %s edit page' . PHP_EOL, $attributeName);
        $crawler = $this->navigationManager->goToUri('GET', $attributeCrawler->getNode(0)->getAttribute('title'));
        $parameters = [];

        printf('Processing parameters' . PHP_EOL);
        $crawler->filter('table.form-list tr')->each(
            function ($attributeNode) use (&$parameters) {
                $parameters = array_merge(
                    $parameters,
                    $this->getAttributeAsArray($attributeNode)
                );
            }
        );
        printf('%d parameters processed' . PHP_EOL, count($parameters));

        $headers = [];
        printf('Processing options' . PHP_EOL);
        $crawler->filter('div#matage-options-panel table tr#attribute-options-table th')->each(
            function ($heading, $i) use (&$headers) {
                if ($i<5) {
                    $headers[] = $heading->text();
                }
            }
        );

        $options = [];
        $crawler->filter('div#matage-options-panel table tr')->each(
            function ($option, $i) use (&$options, $headers) {
                // On n'a pas toutes les lignes du tableau ... Seulement la première
                $option->filter('td input')->each(
                    function ($label, $j) use (&$options, $headers, $i) {
                        if ($j<5) {
                            $options[$i][$headers[$j]] = $label->getNode(0)->getAttribute('value');
                        }
                    }
                );
            }
        );
        printf('%d options processed' . PHP_EOL, count($options));

        $parameters['options'] = $options;

        return $parameters;
    }

} 