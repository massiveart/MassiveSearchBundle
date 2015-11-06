Introduction
============

The MassiveSearchBundle provides an extensible, localized search *abstraction* which is
concerned primarily with providing "site" search capabilities.

What it does
------------

It allows you to map documents using XML (or a custom driver), index them with
a search *adapter* and search for them. The search "results" (documents) are
returned in a format focused on the use case of providing a list of search
results on which the user clicks.

For example, a typical use case would be to provide a search results page as
follows:

.. code-block:: none

    +--------+ Search result 1
    |        | 
    | <img>  | Some description of this result
    |        |
    +--------+

    +--------+ Search result 2
    |        | 
    | <img>  | Some description for search result 2
    |        |
    +--------+

Just to be clear: it is not *designed* for anything else.

Quick example
-------------

This example will assume you want to index a ``Product`` entity using the
Doctrine ORM.

.. note::
 
    The bundle is in no way coupled to the Doctrine ORM, and it is possible to
    use it with any persistence system.

Enable the Doctrine ORM support in your main configuration:

.. code-block:: yaml

    massive_search:
        persistence:
            doctrine_orm:
                enabled: true

And enable one of the :doc:`search_adapters`
        
Create your model in ``<YourBundle>/Entity/Product.php``:

.. code-block:: php

    <?php

    // <YourBundle>/Entity/Product.php

    namespace Acme\YourBundle\Entity\Product;

    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity
     * @ORM\Table(name="product")
     */
    class Product
    {
        /**
         * @ORM\Column(type="integer")
         * @ORM\Id
         * @ORM\GeneratedValue(strategy="AUTO")
         */
        protected $id;

        /**
         * @ORM\Column(type="string", length=100)
         */
        protected $name;

        /**
         * @ORM\Column(type="decimal", scale=2)
         */
        protected $price;

        /**
         * @ORM\Column(type="text")
         */
        protected $description;
    }

Place the following mapping file in the
``Resources/config/massive-search/Product.xml``:

.. code-block:: xml

    <!-- /path/to/YourBundle/Resources/config/massive-search/Product.xml -->
    <massive-search-mapping xmlns="http://massive.io/schema/dic/massive-search-mapping">

        <mapping class="Model\Product">
            <index value="product" />
            <id property="id" />
            <title property="name" />
            <url expr="'/path/to/' ~ object.id" />
            <description property="body" />
            <image expr="'/assets/images/' ~ object.type" />
            <fields>
                <field name="title" type="string" />
                <field name="body" type="string" />
            </fields>
        </mapping>

    </massive-search-mapping>

Now, when you persist your ``Product`` with Doctrine ORM it should be
automatically indexed by the configured search adapter.
