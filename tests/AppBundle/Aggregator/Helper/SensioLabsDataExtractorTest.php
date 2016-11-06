<?php


namespace Tests\AppBundle\Aggregator\Helper;

use PHPUnit_Framework_TestCase;
use AppBundle\Aggregator\Helper\SensiolabsDataExtractor;
use Symfony\Component\DomCrawler\Crawler;

class SensiolabsDataExtractorTest extends PHPUnit_Framework_TestCase
{
    public function testExtract()
    {
        $extractor = new SensiolabsDataExtractor();
        $html = file_get_contents(__DIR__.'/../fixtures/sensiolabs-profiles/gandalf.html');

        $crawler = new Crawler($html, 'http://middle-earth');

        $data = $extractor->extract($crawler);

        $expected = [
            'city' => 'Valinor',
            'country' => 'Middle Earth',
            'github_url' => 'https://github.com/gandalf',
            'github_login' => 'gandalf',
        ];

        $this->assertEquals($expected, $data);
    }

    public function testExtractIfSomeFieldsAreMissing()
    {
        $extractor = new SensiolabsDataExtractor();
        $html = file_get_contents(__DIR__.'/../fixtures/sensiolabs-profiles/frodo.html');

        $crawler = new Crawler($html, 'http://middle-earth');

        $data = $extractor->extract($crawler);

        $expected = [
            'city' => 'Shire',
            'country' => '',
            'github_url' => '',
            'github_login' => '',
        ];

        $this->assertEquals($expected, $data);
    }
}
