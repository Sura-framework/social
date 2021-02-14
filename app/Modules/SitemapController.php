<?php


namespace App\Modules;


use DateTime;
use Sura\Sitemap\SitemapGenerator;

class SitemapController
{

    /**
     */
    public function main(): int
    {
        $yourSiteUrl = 'https://sura.qd2.ru';

// Setting the current working directory to be output directory
// for generated sitemaps (and, if needed, robots.txt)
// The output directory setting is optional and provided for demonstration purpose.
// By default output is written to current directory.
        $outputDir = __DIR__.'/../../public/';

        $generator = new SitemapGenerator($yourSiteUrl, $outputDir);

// will create also compressed (gzipped) sitemap
        $generator->toggleGZipFileCreation();

// determine how many urls should be put into one file;
// this feature is useful in case if you have too large urls
// and your sitemap is out of allowed size (50Mb)
// according to the standard protocol 50000 is maximum value (see http://www.sitemaps.org/protocol.html)
        $generator->setMaxURLsPerSitemap(50000);

// sitemap file name
        $generator->setSitemapFileName("sitemap.xml");

// sitemap index file name
        $generator->setSitemapIndexFileName("sitemap-index.xml");

// alternate languages
        $alternates = [
            ['hreflang' => 'de', 'href' => "http://sura.qd2.ru/de"],
            ['hreflang' => 'fr', 'href' => "http://sura.qd2.ru/fr"],
        ];

// adding url `loc`, `lastmodified`, `changefreq`, `priority`, `alternates`
        $generator->addURL('/path/to/page/', new DateTime(), 'always', 0.5, $alternates);

// generate internally a sitemap
        $generator->createSitemap();

// write early generated sitemap to file(s)
        $generator->writeSitemap();

// update robots.txt file in output directory or create a new one
        $generator->updateRobots();

// submit your sitemaps to Google, Yahoo, Bing and Ask.com
        $generator->submitSitemap();

        echo 'true';
    }

}