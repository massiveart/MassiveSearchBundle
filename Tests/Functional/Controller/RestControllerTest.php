<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Massive\Bundle\SearchBundle\Tests\Functional\Search;

use Massive\Bundle\SearchBundle\Controller\SearchController;
use Massive\Bundle\SearchBundle\Tests\Functional\BaseTestCase;
use Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Product;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\BrowserKit\Client;

class SearchControllerTest extends BaseTestCase
{
    /**
     * @var SearchController
     */
    private $controller;

    /**
     * @var SearchManager
     */
    private $searchManager;

    /**
     * @var Client
     */
    private $client;

    public function setUp()
    {
        parent::setUp();
        $this->client = $this->createClient();
        $this->searchManager = $this->client->getContainer()->get('massive_search.search_manager');

        $product = new Product();
        $product->setId(6);
        $product->setTitle('Product X');
        $product->setBody('To be or not to be, that is the question');
        $product->setUrl('/foobar');
        $product->setLocale('fr');

        $this->searchManager->index($product);
    }

    public function provideSearch()
    {
        return array(
            array(
                'Product',
                array('product'),
                'fr',
                array(
                    array(
                        'id' => null,
                        'document' => array(
                            'id' => 6,
                            'title' => 'Product X',
                            'description' => 'To be or not to be, that is the question',
                            'class' => 'Massive\\Bundle\\SearchBundle\\Tests\\Resources\\TestBundle\\Entity\\Product',
                            'url' => '/foobar',
                            'image_url' => null,
                            'locale' => 'fr',
                        ),
                        'score' => -1,
                    ),
                ),
            ),
        );
    }

    /**
     * @dataProvider provideSearch
     */
    public function testSearch($query, $indexes = null, $locale = null, $expectedResult)
    {
        $this->client->request('GET', '/api/search', array(
            'q' => $query,
            'indexes' => $indexes,
            'locale' => $locale,
        ));

        $response = $this->client->getResponse();
        $result = json_decode($response->getContent(), true);

        $this->assertEquals($expectedResult, $result);
    }
}
