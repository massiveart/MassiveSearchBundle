Feature: Pagination
    In order to search for things
    As a developer
    I can search paginated

    Background:
        Given the entity "PaginatingCar" exists:
        """
        <?php

        namespace Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity;

        class PaginatingCar {
            public $id;
            public $title;
        }
        """
        And I purge the index "car"
        Given that the following mapping for "PaginatingCar" exists:
        """
        <massive-search-mapping xmlns="http://massiveart.com/schema/dic/massive-search-mapping">

            <mapping class="Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\PaginatingCar">
                <index value="car"/>
                <id property="id"/>
                <title property="title" />

                <fields>
                    <field name="title" expr="object.title" type="string" />
                </fields>
            </mapping>

        </massive-search-mapping>
        """
        And I index the following "PaginatingCar" objects
        """
        [
            { "id": 1, "title": "My car" },
            { "id": 2, "title": "My car" },
            { "id": 3, "title": "My car" },
            { "id": 4, "title": "My car" },
            { "id": 5, "title": "My car" },
            { "id": 6, "title": "My car" },
            { "id": 7, "title": "My car" },
            { "id": 8, "title": "My car" },
            { "id": 9, "title": "My car" },
            { "id": 10, "title": "My car" },
            { "id": 11, "title": "My car" },
            { "id": 12, "title": "My car" }
        ]
        """

    @zend_lucene @elastic
    Scenario Outline: Basic paginating
        When I search for "<search>" with limit <limit> and offset <offset>
        Then there should be <nbResults> results

        Examples:
            | search | nbResults | limit | offset |
            | My Car | 5         | 5     | 1      |
            | My Car | 5         | 5     | 5      |
            | My Car | 2         | 5     | 10     |
            | My Car | 12        | 20    | 0      |
            | My Car | 0         | 0     | 20     |

    @elastic
    Scenario: Without paginating
        When I search for "My Car"
        Then there should be 10 results

    @zend_lucene
    Scenario: Without paginating
        When I search for "My Car"
        Then there should be 12 results
