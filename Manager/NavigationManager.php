<?php

namespace Akeneo\Component\MagentoAdminExtractor\Manager;

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
     * @throws NavigationException Thrown if an error occurs during navigation
     *
     * @return Crawler
     */
    public function goToLink(Crawler $crawler, $linkName)
    {
        if (is_string($linkName)) {
            printf('Accessing to "%s" page' . PHP_EOL, $linkName);

            try {
                $link    = $crawler->selectLink($linkName)->link();
                $crawler = $this->client->click($link);
            } catch (\InvalidArgumentException $e) {
                throw new NavigationException(PHP_EOL . '[ERROR] ' . $e->getMessage() . '. A problem can be that the' .
                    ' given link "' . $linkName .'" can not be found.' . PHP_EOL);
            }
        } else {
            throw new NavigationException(PHP_EOL . '[ERROR] $linkname "' . $linkName .'" has to be a string.'
                . PHP_EOL);
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
     * @param Crawler $crawler     A crawler on the page you are
     * @param int     $rowsPerPage Number of rows you want to have.
     *
     * @throws NavigationException Thrown if an error occurs during navigation
     *
     * @return Crawler
     */
    public function goToProductCatalog(Crawler $crawler, $rowsPerPage = 20)
    {
        printf('Accessing to Manage Products page' . PHP_EOL);

        try {
            $link    = $crawler->selectLink('Manage Products')->link();
            $crawler = $this->client->click($link);
        } catch (\InvalidArgumentException $e) {
            throw new NavigationException(PHP_EOL . '[ERROR] ' . $e->getMessage() . '. A problem can be that the' .
                ' link "Manage Products" can not be found.' . PHP_EOL);
        }

        if (is_numeric($rowsPerPage)) {
            try {
                $formKey = $crawler->filter('input[name="form_key"]')->attr('value');
                $viewPerPageUri = $link->getUri() . '/grid/limit/' . $rowsPerPage . '/?ajax=true&isAjax=true';
                $crawler = $this->goToUri('POST', $viewPerPageUri, ['form_key' => $formKey]);
            } catch (\InvalidArgumentException $e) {
                printf(PHP_EOL . '[WARNING] "' . $e->getMessage() . '" in file ' . $e->getFile() .
                    ', trying to access to products catalog with ' . $rowsPerPage . ' rows per page.' .
                    ' Form key was not found.');
            } catch (\Exception $e) {
                printf(PHP_EOL . '[WARNING] "' . $e->getMessage() . '" trying to change number of rows per page in products ' .
                    'grid.' . PHP_EOL . 'In file ' . $e->getFile() . ', line ' . $e->getLine());
            }
        }

        return $crawler;
    }

    /**
     * Allows to navigate to attribute catalog page
     *
     * @param Crawler $crawler     A crawler on a full page
     * @param int     $rowsPerPage Number of rows you want to have.
     *
     * @throws NavigationException Thrown if an error occurs during navigation
     *
     * @return Crawler
     */
    public function goToAttributeCatalog(Crawler $crawler, $rowsPerPage = 20)
    {
            printf('Accessing to Manage Attributes page' . PHP_EOL);

            try {
                $link    = $crawler->selectLink('Manage Attributes')->link();
                $crawler = $this->client->click($link);
            } catch (\InvalidArgumentException $e) {
                throw new NavigationException(PHP_EOL . '[ERROR] ' . $e->getMessage() . '. A problem can be that the' .
                    ' link "Manage Attributes" can not be found.' . PHP_EOL);
            }

            try {
                $script  = $crawler->filter('script[type="text/javascript"]')->first()->html();

                if (preg_match('#var FORM_KEY = \'(.*)\';#', $script, $formKey) && is_numeric($rowsPerPage)) {
                    $viewPerPageUri = $link->getUri() .
                        'index/limit/' . $rowsPerPage . '/form_key/' . $formKey[1] . '/';
                    $crawler = $this->goToUri('GET', $viewPerPageUri);
                }
            } catch (\InvalidArgumentException $e) {
                printf(PHP_EOL . '[WARNING] "' . $e->getMessage() . '" in file ' . $e->getFile() .
                    ', trying to access to attributes catalog with ' . $rowsPerPage . ' rows per page.' .
                    ' Form key not found.');
            } catch (\Exception $e) {
                printf(PHP_EOL . '[WARNING] "' . $e->getMessage() . '" trying to change number of rows per page in attributes ' .
                    'grid.' . PHP_EOL . 'In file ' . $e->getFile() . ', line ' . $e->getLine());
            }

            return $crawler;
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
