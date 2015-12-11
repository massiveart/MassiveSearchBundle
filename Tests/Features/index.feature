Feature: Indexing
    In order to search for things
    As a developer
    I must first index them

    Background:
        Given the entity "IndexingCar" exists:
        """
        <?php

        namespace Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity;

        class IndexingCar {
            public $id;
            public $title;
            public $body;
            public $numberOfWheels;
            public $cost;
            public $date;
            public $image;
            public $locale;
            public $passengers;
        }
        """
        And I purge the index "car"

    @zend_lucene @elastic
    Scenario Outline: Basic indexing
        Given that the following mapping for "IndexingCar" exists:
        """
        <massive-search-mapping xmlns="http://massiveart.com/schema/dic/massive-search-mapping">

            <mapping class="Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\IndexingCar">
                <index value="car"/>
                <id property="id"/>
                <url expr="'foobar'" />
                <title property="title" />
                <description property="body" />
                <image property="image" />
                <locale property="locale" />

                <fields>
                    <field name="title" expr="object.title" type="string" />
                    <field name="body" type="string" />
                    <field name="numberOfWheels" type="string" />
                    <field name="passengers" type="array" />
                </fields>
            </mapping>

        </massive-search-mapping>
        """
        And I index the following "IndexingCar" objects
        """
        [
            { "id": 123, "url": "/url/to", "title": "My car", "body": "Hello", "image": "foo.jpg", "passengers": ["Jackson", "Jill"] },
            { "id": 321, "url": "/url/to", "title": "My car", "body": "Hello", "image": "foo.jpg", "passengers": ["Jack"] }
        ]
        """
        When I search for "<search>"
        Then there should be <nbResults> results

        Examples:
            | search | nbResults |
            | My Car | 2 |
            | jac* | 2 |
            | Jac* | 2 |
            | jill | 1 |
            | Jill | 1 |
            | john | 0 |
            | jac | 0 |
            | Jackson | 1 |

    @zend_lucene @elastic @test
    Scenario: Invalid mapping, unknown field type
        Given that the following mapping for "IndexingCar" exists:
        """
        <massive-search-mapping xmlns="http://massiveart.com/schema/dic/massive-search-mapping">

            <mapping class="Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\IndexingCar">
                <index value="car"/>
                <id property="id"/>
                <url expr="'foobar'" />
                <title property="title" />
                <description property="body" />
                <image property="image" />
                <locale property="locale" />

                <fields>
                    <field name="title" field="title" type="foobar" />
                </fields>
            </mapping>

        </massive-search-mapping>
        """
        When I index the following "IndexingCar" objects
        """
        [
            { "id": 123, "url": "/url/to", "title": "My car", "body": "Hello", "image": "foo.jpg"},
            { "id": 321, "url": "/url/to", "title": "My car", "body": "Hello", "image": "foo.jpg"}
        ]
        """
        Then an exception with message 'No converter found to convert value from type "foobar"' should be thrown

