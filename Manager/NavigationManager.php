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
     * @return Crawler
     */
    public function goToLink(Crawler $crawler, $linkName)
    {
        if (is_string($linkName)) {
            printf('Accessing to "%s" page' . PHP_EOL, $linkName);
            $link    = $crawler->selectLink($linkName)->link();
            $crawler = $this->client->click($link);
        } else {
            //TODO: throw exception
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
     * @param Crawler $crawler     A crawler on the page you are
     * @param int     $rowsPerPage Number of rows you want to have.
     *
     * @return Crawler
     */
    public function goToProductCatalog(Crawler $crawler, $rowsPerPage = 20)
    {
        printf('Accessing to Manage Products page' . PHP_EOL);
        $link    = $crawler->selectLink('Manage Products')->link();
        $crawler = $this->client->click($link);

        if (is_numeric($rowsPerPage))
        {
            try {
                $formKey = $crawler->filter('input[name="form_key"]')->attr('value');
                $viewPerPageUri = $link->getUri() . '/grid/limit/' . $rowsPerPage . '/?ajax=true&isAjax=true';
                $crawler = $this->goToUri('POST', $viewPerPageUri, ['form_key' => $formKey]);
            } catch (\InvalidArgumentException $e) {
                printf('[WARNING] "' . $e->getMessage() . '" in file ' . $e->getFile() .
                    ', trying to access to products catalog with ' . $rowsPerPage . ' rows per page.' .
                    ' Form key was not found.');
            } catch (\Exception $e) {
                printf('[ERROR] "' . $e->getMessage() . '" in file ' . $e->getFile() . ', line ' . $e->getLine());
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
     * @return Crawler
     */
    public function goToAttributeCatalog(Crawler $crawler, $rowsPerPage = 20)
    {
            printf('Accessing to Manage Attributes page' . PHP_EOL);
            $link    = $crawler->selectLink('Manage Attributes')->link();
            $crawler = $this->client->click($link);

            try {
                $script  = $crawler->filter('script[type="text/javascript"]')->first()->html();

                if (preg_match('#var FORM_KEY = \'(.*)\';#', $script, $formKey) && is_numeric($rowsPerPage)) {
                    $viewPerPageUri = $link->getUri() . 'index/limit/' . $rowsPerPage . '/form_key/' . $formKey[1] . '/';
                    $crawler = $this->goToUri('GET', $viewPerPageUri);
                }
            } catch (\InvalidArgumentException $e) {
                printf('[WARNING] "' . $e->getMessage() . '" in file ' . $e->getFile() .
                    ', trying to access to attributes catalog with ' . $rowsPerPage . ' rows per page.' .
                    ' Form key not found.');
            } catch (\Exception $e) {
                printf('[ERROR] "' . $e->getMessage() . '" in file ' . $e->getFile() . ', line ' . $e->getLine());
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
