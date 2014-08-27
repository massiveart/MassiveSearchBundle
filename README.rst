MassiveSearchBundle
===================

The MassiveSearchBundle provides:

- An abstraction for search engine libraries.
- A way to map classes which you want to index.

By default it is configured to use the Zend Lucene library, which must be
installed (see the `suggests` and ``require-dev`` sections in ``composer.json``.

Installation
------------

You can install the MassiveSearchBundle by adding it to `composer.json`:

.. code-block:: javascript

    "require": {
        ...
        "massive/search-bundle": "0.1"
    }

And then include it in your ``AppKernel``:

.. code-block:: php

    class AppKernel
    {
        public function registerBundles()
        {
            return array(
                // ...
                new \Massive\Bundle\SearchBundle\MassiveSearchBundle(),
            );
        }
    }

You will also need to include a search library. The search libraries are
listed in the ``suggests`` section of ``composer.json``, and exact package
versions can also be found in the ``require-dev`` section (as all the libraries are tested).

For example, to enable the ZendLucene search library:

.. code-block:: javascript

    "require": {
        ...
        "zendframework/zend-stdlib": "2.3.1 as 2.0.0rc5",
        "zendframework/zendsearch": "2.*",
    }

Mapping
-------

The MassiveSearchBundle requires that you define which objects should be indexed
through *mapping*. Currently only **XML mapping** supported:

.. code-block::

    <!-- /path/to/YourBundle/Resources/config/massive-search/Product.xml -->
    <massive-search-mapping xmlns="http://massive.io/schema/dic/massive-search-mapping">

        <mapping class="Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Product">
            <indexName>product</indexName>
            <idField name="id"/>
            <fields>
                <field name="title" type="string" />
                <field name="body" type="string" />
            </fields>
        </mapping>

    </massive-search-mapping>

This mapping will cause the fields ``title`` and ``body`` to be indexed into
an index named ``product`` using the ID obtained from the objects ``id``
field. (We use the Symfony `PropertyAccess
<http://symfony.com/doc/current/components/property_access/index.html>`_
component, so it works on properties and methods alike).

Note:

- This file MUST be located in ``YourBundle/Resources/config/massive-search``
- It must be named after the name of your class (without the namespace) e.g.
  ``Product.xml``
- Your ``Product`` class MUST be located in one of the following folders:
  - ``YourBundle/Document``
  - ``YourBundle/Entity``
  - ``YourBundle/Model``

.. note::

    This is an early version of the bundle, it will support explict non-magic
    mapping in the future.

Indexing
--------

Once you have created your mapping files you can index your objects, for
example after saving it.

The bundle provides the ``massive_search.search_manager`` object which is the
only service which you will need to access.

.. code-block:: php

    $product = new Product();

    // ... populate the product, persist it, whatever.

    $searchManager = $this->get('massive_search.search_manager');
    $searchManager->index($product);

The SearchManager will know from the mapping how to index the product, and it
will be indexed using the configured search library adapter.

.. note:: The bundle automatically removes existing documents with the same
          ID. The ID mapping is mandatory.

Searching
---------

As with the indexing, searching for results is also done with the
SearchManager.

Currently only supported by query string is supported. The query string
is passed directly to the search library:

.. code-block:: php

    $hits = $searchManager->search('My Product');

    foreach ($hits as $hit) {
        echo $hit->getScore();

        // @var Massive\Bundle\SearchBundle\Search\Document
        $document = $hit->getDocument();

        // retrieve the indexed documents "body" field
        $body = $document->getField('body');

        // retrieve the indexed ID of the document
        $body = $document->getId();
    }

Commands
--------

The MassiveBuildBundle provides some commands.

massive:search:query
~~~~~~~~~~~~~~~~~~~~

Perform a query from the command line::

    $ php app/console massive:search:query "Foobar" --index="barfoo"
    +------------------+--------------------------------------+-----------+-------------+-----------+------------------------+
    | Score            | ID                                   | Title     | Description | Url       | Class                  |
    +------------------+--------------------------------------+-----------+-------------+-----------+------------------------+
    | 0.53148467371593 | ac984681-ca92-4650-a9a6-17bc236f1830 | Structure |             | structure | OverviewStructureCache |
    +------------------+--------------------------------------+-----------+-------------+-----------+------------------------+

massive:search:status
~~~~~~~~~~~~~~~~~~~~~

Display status information for the current search implementation::

    $ php app/console massive:search:status
    +-------------+--------------------------------------------------------------+
    | Field       | Value                                                        |
    +-------------+--------------------------------------------------------------+
    | Adapter     | Massive\Bundle\SearchBundle\Search\Adapter\ZendLuceneAdapter |
    | idx:product | {"size":11825,"nb_files":36,"nb_documents":10}               |
    +-------------+--------------------------------------------------------------+

Extending
---------

You can extend the bundle with custom metadata drivers.

Metadata Drivers
~~~~~~~~~~~~~~~~

Simply extend the ``Metadata\Driver\DriverInterface`` and add the tag
``massive_search.metadata.driver`` tag to your implementations service
definition.

.. code-block:: xml

        <service id="massive_search.metadata.driver.xml" class="%massive_search.metadata.driver.xml.class%">
            <argument type="service" id="massive_search.metadata.file_locator" />
            <tag type="massive_search.metadata.driver" />
        </service>

Hit Listeners
~~~~~~~~~~~~~

The ``SearchManager`` will fire an event of type ``HitEvent`` in the Symfony EventDispatcher named
``massive_search.hit``.

The ``HitEvent`` contains the hit object and the reflection class of the
object which was originally indexed.

For example:

.. code-block:: php

    <?php

    namespace Sulu\Bundle\SearchBundle\EventListener;

    use Massive\Bundle\SearchBundle\Search\Event\HitEvent;

    class HitListener
    {
        public function onHit(HitEvent $event)
        {
            $reflection = $event->getDocumentReflection();
            if (false === $reflection->isSubclassOf('MyClass')) {
                return;
            }

            $document = $event->getDocument();
            $docuemnt->setUrl('Foo' . $document->getUrl());
        }
    }
