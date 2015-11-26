@elastic
Feature: Search Manager
  In order to manage the search indexing of objects
  As a developer
  I should be able to use the search manager API

  Background:
    Given the entity "SortingCar" exists:
        """
        <?php

        namespace Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity;

        class SortingCar {
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
    And that the following mapping for "SortingCar" exists:
        """
        <massive-search-mapping xmlns="http://massiveart.com/schema/dic/massive-search-mapping">

            <mapping class="Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\SortingCar">
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
                </fields>
            </mapping>

        </massive-search-mapping>
        """

  Scenario Outline: Sorting
    Given the following "SortingCar" objects have been indexed
        """
        [
            { "id": 123, "url": "/url/to", "title": "Carone", "body": "Hello", "image": "foo.jpg"},
            { "id": 321, "url": "/url/to", "title": "Cartwo", "body": "Bello", "image": "foo.jpg"}
        ]
        """
    When I search for "<search>" with sort "<field>" and order "<order>"
    Then the result at position "<position>" should be "<id>"

    Examples:
      | search | field | order | position | id  |
      | Car    | title | asc   | 0        | 123 |
      | Car    | title | asc   | 1        | 321 |
      | Car    | title | desc  | 1        | 123 |
      | Car    | title | desc  | 0        | 321 |
      | Car    | body  | asc   | 0        | 321 |
      | Car    | body  | asc   | 1        | 123 |
      | Car    | body  | desc  | 1        | 321 |
      | Car    | body  | desc  | 0        | 123 |
