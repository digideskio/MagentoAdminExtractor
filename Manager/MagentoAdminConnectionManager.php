<?php

namespace Akeneo\Component\MagentoAdminExtractor\Manager;

use Goutte\Client;

/**
 * Manage connection to magento admin dashboard
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoAdminConnectionManager
{
    /** @var string */
    protected $adminUrl;

    /** @var string */
    protected $login;

    /** @var string */
    protected $password;

    /** @var Client */
    protected $client;

    /**
     * @param string $adminUrl
     * @param string $login
     * @param string $password
     */
    public function __construct($adminUrl, $login, $password)
    {
        $this->adminUrl = $adminUrl;
        $this->login    = $login;
        $this->password = $password;
    }

    /**
     * Allows to connect to Magento admin page
     *
     * @return Crawler Admin page crawler
     *
     * @throws LogInException if there is a problem with connection
     */
    public function connectToAdminPage()
    {
        if (empty($this->client)) {
            $client = new Client();
        } else {
            $client = $this->client;
        }

        printf('Requesting %s' . PHP_EOL, $this->adminUrl);
        $crawler = $client->request('GET', $this->adminUrl);

        printf('Login user "%s"' . PHP_EOL . PHP_EOL, $this->login);
        $form    = $crawler->selectButton('Login')->form();
        $crawler = $client->submit($form, ['login[username]' => $this->login, 'login[password]' => $this->password]);

        if (count($crawler->filter('li.error-msg')) > 0) {
            throw new LogInException('[ERROR] ' . $crawler->filter('li.error-msg')->first()->text());
        }

        $this->client = $client;

        return $crawler;
    }

    /**
     * @return Client
     *
     * @throws LogInException if there is a problem with connection
     */
    public function getClient()
    {
        if (empty($this->client)) {
            $this->connectToAdminPage();
        }

        return $this->client;
    }

    /**
     * @param string $adminUrl
     *
     * @return $this
     */
    public function setAdminUrl($adminUrl)
    {
        $this->adminUrl = $adminUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getAdminUrl()
    {
        return $this->adminUrl;
    }

    /**
     * @param string $login
     *
     * @return $this
     */
    public function setLogin($login)
    {
        $this->login = $login;

        return $this;
    }

    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @param string $password
     *
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }
}
