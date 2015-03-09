Feature: Search Managager category searching
    In order to manage the search for results in a specified category
    As a developer
    I should be able to use the search manager API to do that

    Background:
        Given the entity "Car" exists:
        """
        <?php

        namespace Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity;

        class Car { 
            public $id;
            public $title;
        }
        """
        And the entity "Cat" exists:
        """
        <?php

        namespace Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity;

        class Cat { 
            public $id;
            public $title;
        }
        """
        And the entity "Bicycle" exists:
        """
        <?php

        namespace Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity;

        class Bicycle { 
            public $id;
            public $title;
        }
        """
        And I purge the index "car"
        And that the following mapping for "Car" exists:
        """
        <massive-search-mapping xmlns="http://massiveart.com/schema/dic/massive-search-mapping">

            <mapping class="Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Car">
                <index name="index_car"/>
                <id property="id"/>
                <title property="title" />
                <category name="transport" />
                <fields>
                    <field name="title" expr="object.title" type="string" />
                </fields>
            </mapping>

        </massive-search-mapping>
        """
        And that the following mapping for "Bicycle" exists:
        """
        <massive-search-mapping xmlns="http://massiveart.com/schema/dic/massive-search-mapping">

            <mapping class="Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Bicycle">
                <index name="index_bicycle"/>
                <id property="id"/>
                <title property="title" />
                <category name="transport" />
                <fields>
                    <field name="title" expr="object.title" type="string" />
                </fields>
            </mapping>

        </massive-search-mapping>
        """
        And that the following mapping for "Cat" exists:
        """
        <massive-search-mapping xmlns="http://massiveart.com/schema/dic/massive-search-mapping">

            <mapping class="Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Cat">
                <index name="index_cat"/>
                <id property="id"/>
                <title property="title" />
                <category name="animals" />
                <fields>
                    <field name="title" expr="object.title" type="string" />
                </fields>
            </mapping>

        </massive-search-mapping>
        """
        And the following "Car" objects have been persisted
        """
        [
            { "id": 123, "title": "Car one"},
            { "id": 321, "title": "Car two"}
        ]
        """
        And the following "Bicycle" objects have been persisted
        """
        [
            { "id": 123, "title": "Bicycle one"},
            { "id": 321, "title": "Bicycle two"}
        ]
        """
        And the following "Cat" objects have been persisted
        """
        [
            { "id": 123, "title": "Cat one"}
        ]
        """

    Scenario: Searching no category
        When I search for "one"
        Then there should be 3 results

    Scenario: Searching with category
        When I search for "one" in category "transport"
        Then there should be 2 results

    Scenario: Searching in unknown category
        When I search for "one" in category "unknown category"
        Then an exception with message 'Categories "unknown category" not known. Known categories: "transport", "animals"' should be thrown

    Scenario: Search in both an index and a category
        When I search for something with both a category and an index
        Then an exception with message 'Category and indexes are mutually exclusive, you specified categories "bar" and indexes "foo"' should be thrown
