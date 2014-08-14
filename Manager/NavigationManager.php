<?php

namespace Manager;

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

class NavigationManager
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Allows you to navigate from $crawler to $link
     *
     * @var Crawler $crawler
     * @var string  $link    Text in the link
     *
     * @return Crawler
     */
    public function goToLink(Crawler $crawler, $linkName)
    {
        if (is_string($linkName)) {
            printf('Accessing to "%s" page' . PHP_EOL, $linkName);
            $link    = $crawler->selectLink($linkName)->link();
            $crawler = $this->client->click($link);
        } else {
            $crawler = null;
        }

        return $crawler;
    }

    /**
     * Allows you to navigate to the uri
     *
     * @param string $method
     * @param string $uri
     * @param array  $params
     *
     * @return Crawler $crawler
     */
    public function goToUri($method, $uri, $params = [])
    {
        return $this->client->request($method, $uri, $params);
    }

    /**
     * Allows you to navigate to product catalog page
     *
     * @param Crawler $crawler
     *
     * @return Crawler
     */
    public function goToProductCatalog(Crawler $crawler)
    {
        return $this->goToLink($crawler, 'Manage Products');
    }

    /**
     * Allows you to navigate to manage categories page
     *
     * @param Crawler $crawler
     *
     * @return Crawler
     */
    public function goToManageCategoriesPage(Crawler $crawler)
    {
        return $this->goToLink($crawler, 'Manage Categories');
    }

    /**
     * Allows you to navigate to attribute catalog page
     *
     * @param Crawler $crawler
     *
     * @return Crawler
     */
    public function goToAttributeCatalog(Crawler $crawler)
    {
        return $this->goToLink($crawler, 'Manage Attributes');
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param Client $client
     *
     * @return Client
     */
    public function setClient($client)
    {
        $this->client = $client;
        return $this;
    }
} 