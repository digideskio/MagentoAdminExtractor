<?php

namespace Extractor;

use Symfony\Component\DomCrawler\Crawler;

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
        $attributeName
    ) {
        printf(PHP_EOL . 'Accessing to attribute %s edit page' . PHP_EOL, $attributeName);
        $crawler = $this->navigationManager->goToUri('GET', $attributeCrawler->getNode(0)->getAttribute('title'));
        $parameters = [];

        printf('Processing parameters' . PHP_EOL);
        $crawler->filter('table.form-list tr')->each(
            function ($attributeNode) use (&$parameters) {
                $attributes = array_merge(
                    $parameters,
                    $this->getAttributeAsArray($attributeNode)
                );
            }
        );
        printf('%d parameters processed' . PHP_EOL, count($parameters));

        return $parameters;
    }

} 