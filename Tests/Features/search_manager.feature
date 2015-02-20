Feature: Search Manager
    In order to manage the search indexing of objects
    As a developer
    I should be able to use the search manager API

    Background:
         Given the entity "Car" exists:
        """
        <?php

        namespace Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity;

        class Car
        {
            public $id;
            public $title;
            public $body;
            public $numberOfWheels;
            public $cost;
            public $date;
            public $image;
        }
        """
        And I purge the index "car"
        And that the following mapping for "Car" exists:
        """
        <massive-search-mapping xmlns="http://massive.io/schema/dic/massive-search-mapping">

            <mapping class="Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Car">
                <index name="car"/>
                <id property="id"/>
                <url expr="'foobar'" />
                <title property="title" />
                <description property="body" />
                <image property="image" />

                <fields>
                    <field name="title" expr="object.title" type="string" />
                    <field name="body" type="string" />
                    <field name="numberOfWheels" type="string" />
                </fields>
            </mapping>

        </massive-search-mapping>
        """

    Scenario: Get the index names, nothing indexed
        Given I get the index names
        Then the result should be the following array:
        """
        [ ]
        """

    Scenario: Get the index names
        Given I index the following "Car" objects
        """
        [
            { "id": 123, "url": "/url/to", "title": "My car", "body": "Hello", "image": "foo.jpg"}
        ]
        """
        And I get the index names
        Then the result should be the following array:
        """
        [ "car" ]
        """

    Scenario: Basic indexing
        Given I index the following "Car" objects
        """
        [
            { "id": 123, "url": "/url/to", "title": "My car", "body": "Hello", "image": "foo.jpg"},
            { "id": 321, "url": "/url/to", "title": "My car", "body": "Hello", "image": "foo.jpg"}
        ]
        """
        And I search for "My car"
        Then there should be 2 results

    Scenario: Purging
        Given I index the following "Car" objects
        """
        [
            { "id": 123, "url": "/url/to", "title": "My car", "body": "Hello", "image": "foo.jpg"},
            { "id": 321, "url": "/url/to", "title": "My car", "body": "Hello", "image": "foo.jpg"}
        ]
        """
        And I purge the index "car"
        And I search for "My car"
        Then there should be 0 results

    Scenario: Deindex
        Given I index the following "Car" objects
        """
        [
            { "id": 123, "url": "/url/to", "title": "My car", "body": "Hello", "image": "foo.jpg"},
            { "id": 321, "url": "/url/to", "title": "My car", "body": "Hello", "image": "foo.jpg"}
        ]
        """
        And I deindex the object with id "321"
        And I search for "My car"
        Then there should be 1 results

    Scenario Outline: Searching
        Given I index the following "Car" objects
        """
        [
            { "id": 123, "url": "/url/to", "title": "Car one", "body": "Hello", "image": "foo.jpg"},
            { "id": 321, "url": "/url/to", "title": "Car two", "body": "Hello", "image": "foo.jpg"}
        ]
        """
        And I search for "<search>"
        Then there should be <nbResults> results

        Examples:
            | search | nbResults |
            | one    | 1         |
            | roomba 870 | 0 |
            | Car | 2 |

    Scenario: Return the status
        Given I get the status
        Then the result should be an array
