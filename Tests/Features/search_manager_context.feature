Feature: Contexts
    In order to be able to have different indexes for the back and front office
    The MassiveSearchBundle should provide a method to map different contexts

    Background:
        Given the entity "Product" exists:
        """
        <?php

        namespace Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity;

        class Product
        {
            public $id;
            public $title;
            public $body;
            public $date;
            public $url;
            public $locale;
            public $image;
        }
        """
        And that the following mapping for "Product" exists:
        """
        <massive-search-mapping xmlns="http://massiveart.com/schema/dic/massive-search-mapping">

            <mapping class="Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Product">
                <index name="animal"/>
                <id property="id"/>
                <url expr="object.url" />
                <title property="title" />
                <locale property="locale" />
                <description property="body" />
                <image property="image" />

                <fields>
                    <field name="title" type="string" />
                    <field name="body" type="string" />
                </fields>

                <context name="admin">
                    <index name="animal_website" />
                    <url expr="'/admin/edit/article/' ~ object.id" />
                </context>
            </mapping>

        </massive-search-mapping>
        """
        And I purge the index "animal"
        And I purge the index "animal_website"

    Scenario Outline: Searching in locale with context index
        Given the following "Product" objects have been persisted
        """
        [
            { "id": 1, "title": "Giraffe", "body": "Long neck", "date": "2015-01-01", "url": "http://foo", "locale": "fr", "image": "foo.png" },
            { "id": 2, "title": "Lion", "body": "Big mane", "date": "2015-01-01", "url": "http://lion.com", "locale": "fr", "image": "foo.png" }
        ]
        """
        When I search for "<animal>" in locale "<locale>" with index "<index>"
        Then there should be "<nbResults>" results

        Examples:
            | animal | locale | nbResults | index |
            | Giraffe | fr | 1 | animal_website |
            | Giraffe | fr | 1 | animal |

    Scenario: Search with no index
        Given the following "Product" objects have been persisted
        """
        [
            { "id": 3, "title": "German Hyena", "body": "Laughs", "date": "2015-01-01", "url": "http://hyena.com", "locale": "de", "image": "foo.png" },
            { "id": 4, "title": "French Hyena", "body": "Laughs", "date": "2015-01-01", "url": "http://hyena.com", "locale": "fr", "image": "foo.png" }
        ]
        """
        When I search for "Hyena"
        Then there should be "4" results
