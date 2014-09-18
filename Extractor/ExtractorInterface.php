<?php

namespace Akeneo\Component\MagentoAdminExtractor\Extractor;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Extractor interface
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ExtractorInterface
{
    const TAG_WARNING = '[WARNING]';

    /**
     * @param Crawler $nodeCrawler
     *
     * @return array
     */
    public function extract(Crawler $nodeCrawler);
}
