<?php

namespace Akeneo\Component\MagentoAdminExtractor\Extractor;

use Symfony\Component\DomCrawler\Crawler;
use Akeneo\Component\MagentoAdminExtractor\Manager\NavigationManager;

/**
 * Abstract extractor for magento
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractExtractor
{
    const TAG_WARNING = '[WARNING]';

    /** @var NavigationManager */
    protected $navigationManager;

    /**
     * @param NavigationManager $navigationManager
     */
    public function __construct(NavigationManager $navigationManager)
    {
        $this->navigationManager = $navigationManager;
    }

    /**
     * @param Crawler $nodeCrawler
     *
     * @return mixed
     */
    abstract public function extract(Crawler $nodeCrawler);

    /**
     * Give the required node by the position
     * TODO: If Akeneo Pim update its version of Symfony,remove this method and use Crawler->getNode()
     *
     * @param Crawler $crawler
     * @param integer $position
     *
     * @return mixed DOMElement or null
     */
    public function getNode(Crawler $crawler, $position)
    {
        foreach ($crawler as $i => $node) {
            if ($i == $position) {
                return $node;
            }
        }
    }
}
