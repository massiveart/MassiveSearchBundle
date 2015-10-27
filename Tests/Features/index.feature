Feature: Indexing
    In order to search for things
    As a developer
    I must first index them

    Background:
        Given the entity "Car" exists:
        """
        <?php

        namespace Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity;

        class Car { 
            public $id;
            public $title;
            public $body;
            public $numberOfWheels;
            public $cost;
            public $date;
            public $image;
            public $locale;
        }
        """
        And I purge the index "car"

    Scenario: Basic indexing
        Given that the following mapping for "Car" exists:
        """
        <massive-search-mapping xmlns="http://massiveart.com/schema/dic/massive-search-mapping">

            <mapping class="Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Car">
                <index value="car"/>
                <id property="id"/>
                <url expr="'foobar'" />
                <title property="title" />
                <description property="body" />
                <category name="Car" />
                <image property="image" />
                <locale property="locale" />

                <fields>
                    <field name="title" expr="object.title" type="string" />
                    <field name="body" type="string" />
                    <field name="numberOfWheels" type="string" />
                </fields>
            </mapping>

        </massive-search-mapping>
        """
        When I index the following "Car" objects
        """
        [
            { "id": 123, "url": "/url/to", "title": "My car", "body": "Hello", "image": "foo.jpg"},
            { "id": 321, "url": "/url/to", "title": "My car", "body": "Hello", "image": "foo.jpg"}
        ]
        """
        And I search for "My car"
        Then there should be 2 results

    Scenario: Invalid mapping, unknown field type
        Given that the following mapping for "Car" exists:
        """
        <massive-search-mapping xmlns="http://massiveart.com/schema/dic/massive-search-mapping">

            <mapping class="Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Car">
                <index value="car"/>
                <id property="id"/>
                <url expr="'foobar'" />
                <title property="title" />
                <description property="body" />
                <category name="Car" />
                <image property="image" />
                <locale property="locale" />

                <fields>
                    <field name="title" field="title" type="foobar" />
                </fields>
            </mapping>

        </massive-search-mapping>
        """
        When I index the following "Car" objects
        """
        [
            { "id": 123, "url": "/url/to", "title": "My car", "body": "Hello", "image": "foo.jpg"},
            { "id": 321, "url": "/url/to", "title": "My car", "body": "Hello", "image": "foo.jpg"}
        ]
        """
        Then an exception with message 'Search field type "foobar" is not known.' should be thrown

