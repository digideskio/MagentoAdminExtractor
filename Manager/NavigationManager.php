<?php

namespace MagentoAdminExtractor\Manager;

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Manager to navigate in magento dashboard
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
     * Allows to navigate from crawler to link
     *
     * @param Crawler $crawler  Crawler
     * @param string  $linkName Text in the link
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
     * Allows to navigate to the uri
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
     * Allows to navigate to product catalog page
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
     * Allows to navigate to attribute catalog page
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
