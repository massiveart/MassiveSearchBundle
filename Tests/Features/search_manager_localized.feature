Feature: Search Manager
    In order to manage the search indexing of objects
    As a developer
    I should be able to use the search manager API

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
        <massive-search-mapping xmlns="http://massive.io/schema/dic/massive-search-mapping">

            <mapping class="Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Product">
                <index name="product"/>
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
            </mapping>

        </massive-search-mapping>
        """
        And I purge the index "product"

    Scenario Outline: Searching in locale
        Given I index the following "Product" objects
        """
        [
            { "id": 1, "title": "Giraffe", "body": "Long neck", "date": "2015-01-01", "url": "http://foo", "locale": "fr", "image": "foo.png" },
            { "id": 2, "title": "Lion", "body": "Big mane", "date": "2015-01-01", "url": "http://lion.com", "locale": "fr", "image": "foo.png" },
            { "id": 4, "title": "Lion 2", "body": "Big mane", "date": "2015-01-01", "url": "http://lion.com", "locale": "fr", "image": "foo.png" },
            { "id": 6, "title": "German Hyena", "body": "Laughs", "date": "2015-01-01", "url": "http://hyena.com", "locale": "de", "image": "foo.png" }
        ]
        """
        And I search for "<animal>" in locale "<locale>"
        Then there should be "<nbResults>" results

        Examples:
            | animal | locale | nbResults |
            | Giraffe | fr | 1 |
            | Lion | fr | 2 |
            | Lion | de | 0 |
            | Hyena | de | 1 |

    Scenario: Search with no locale
        Given I index the following "Product" objects
        """
        [
            { "id": 3, "title": "German Hyena", "body": "Laughs", "date": "2015-01-01", "url": "http://hyena.com", "locale": "de", "image": "foo.png" },
            { "id": 4, "title": "French Hyena", "body": "Laughs", "date": "2015-01-01", "url": "http://hyena.com", "locale": "fr", "image": "foo.png" }
        ]
        """
        And I search for "Hyena"
        Then there should be "2" results
