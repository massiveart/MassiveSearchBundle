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

use Massive\Bundle\SearchBundle\Controller\RestController;
use Massive\Bundle\SearchBundle\Tests\Functional\BaseTestCase;
use Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Product;

class RestControllerTest extends BaseTestCase
{
    /**
     * @var RestController
     */
    private $controller;

    /**
     * @var SearchManager
     */
    private $searchManager;

    public function setUp()
    {
        parent::setUp();
        $this->searchManager = $this->getSearchManager();
        $this->controller = new RestController($this->searchManager);

        $product = new Product();
        $product->setId(6);
        $product->setTitle('Product X');
        $product->setBody('To be or not to be, that is the question');
        $product->setUrl('/foobar');
        $product->setLocale('fr');

        $this->getSearchManager()->index($product);
    }

    public function provideSearch()
    {
        return array(
            array(
                'Product', 
                array('product'),
                'fr',
                array (
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
        $response = $this->controller->searchAction($query, $indexes, $locale);
        $result = json_decode($response->getContent(), true);

        $this->assertEquals($expectedResult, $result);
    }
}
