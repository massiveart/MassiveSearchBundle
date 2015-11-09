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

    Scenario: Basic indexing
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
        When I search for "My car"
        Then there should be 2 results
        And I search for "jac*"
        Then there should be 2 results
        And I search for "Jac*"
        Then there should be 2 results
        And I search for "jill"
        Then there should be 1 results
        And I search for "Jill"
        Then there should be 1 results
        And I search for "john"
        Then there should be 0 results
        And I search for "jac"
        Then there should be 0 results
        And I search for "Jackson"
        Then there should be 1 results

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

