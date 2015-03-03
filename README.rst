MassiveSearchBundle
===================

.. image:: https://travis-ci.org/massiveart/MassiveSearchBundle.svg?branch=master
    :target: https://travis-ci.org/massiveart/MassiveSearchBundle

The purpose of this bundle is to provide flexible **site search** functionality.

This means it provides a way to index objects (for example Doctrine entities)
and then to search for them using a query string.

This bundle provides:

  - Choice of search backends (ZendSearch, Elastic Search)
  - Localization strategies

By default it is configured to use the Zend Lucene library, which must be
installed (see the `suggests` and ``require-dev`` sections in ``composer.json``.

Installation
------------

You can install the MassiveSearchBundle by adding it to `composer.json`:

.. code-block:: javascript

    "require": {
        ...
        "massive/search-bundle": "0.*"
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

Search Adapters
---------------

Zend Lucene
~~~~~~~~~~~

The ZendLucene search is the default implementation. It requires no external
dependencies. But be aware that the version used here is *unmaintained* and
is not considered stable.

To enable add the following dependencies to your ``composer.json``:

.. code-block:: javascript

    "require": {
        ...
        "zendframework/zend-stdlib": "2.3.1 as 2.0.0rc5",
        "zendframework/zendsearch": "2.*",
    }

and select the adapter in your application configuration:

.. code-block:: yaml

    // app/config/config.yml
    massive_search:
        adapter: zend_search

Elastic
~~~~~~~

The elastic search adapter allows you to use the
[Elasticsearch](http://www.elasticsearch.org/) search engine.

You will need to include the official client in ``composer.json``:

.. code-block:: javascript

    "require": {
        ...
        "elasticsearch/elasticsearch": "~1.3",
    }

and select the adapter in your application configuration:

.. code-block:: yaml

    // app/config/config.yml
    massive_search:
        adapter: elastic

Mapping
-------

The MassiveSearchBundle requires that you define which objects should be indexed
through *mapping*. Currently only **XML mapping** is natively supported:

.. code-block:: xml

    <!-- /path/to/YourBundle/Resources/config/massive-search/Product.xml -->
    <massive-search-mapping xmlns="http://massiveart.com/schema/dic/massive-search-mapping">

        <mapping class="Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Product">
            <index name="product" />
            <id property="id" />
            <locale property="locale" />
            <fields>
                <field name="title" type="string" />
                <field name="body" type="string" />
            </fields>
        </mapping>

    </massive-search-mapping>

This mapping will cause the fields ``title`` and ``body`` to be indexed into
an index named ``product`` using the ``locale`` determined from the locale
field and the ID obtained from the objects ``id``
field.

Mapping elements
~~~~~~~~~~~~~~~~

The possible mappings are:

- ``index``: Name of the index in which to insert the record
- ``title``: Title to use in search results
- ``description``: A description for the search result
- ``url``: The URL to which the search resolt should link to
- ``image``: An image to associate with the search result
- ``fields``: List of ``<field />`` elements detailing which fields should be
  indexed (i.e. used when finding search results).

Each mapping can use either a ``property`` attribute or an ``expr`` attribute.
These attributes determine how the value is retrieved. ``property`` will use
the Symfony `PropertyAccess`_ component, and ``expr`` will use
`ExpressionLanguage`_.

PropertyAccess allows you to access properties of an object by path, e.g.
``title``, or ``parent.title``. The expression allows you to build expressions
which can be evaluated, e.g. ``'/this/is/' ~ object.id ~ '/a/path'``.

Fields
~~~~~~

Fields dictate which fields are indexed in the underlying search engine.

Mapping:

- ``name``: Field name
- ``property``: Object property to map the field
- ``expr``: Mutually exclusive with ``property``

Types:

- ``string``: Store as a string

Expression Language
~~~~~~~~~~~~~~~~~~~

The MassiveSearchBundle includes its own flavor of the Symfony expression
language.

Functions:

- ``join``: Maps to the ``implode`` function in PHP. e.g. ``join(',', ["one",
  "two"])`` equals ``"one,two"``
- ``map``: Maps to the ``array_map`` function in PHP. e.g. ``map([1, 2, 3],
  'el + 1')`` equals ``array(2, 3, 4)``.

Contexts
~~~~~~~~

Sometimes you will require different mappings based on the context of the web
application. For example, an Article may have one URL in the front office, and
another in the backoffice (i.e. for viewing and editing respectively).

MassiveSearchBundle solves this with ``contexts``. A ``context`` allows you
to map additional indexes:

.. code-block:: xml

    <!-- /path/to/YourBundle/Resources/config/massive-search/Product.xml -->
    <massive-search-mapping xmlns="http://massiveart.com/schema/dic/massive-search-mapping">

        <mapping class="Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Product">
            <index name="product" />
            <id property="id" />
            <url expr="'/path/to/' ~ article.title'" />
            <fields>
                <field name="title" type="string" />
                <field name="body" type="string" />
            </fields>

            <context name="admin">
                <url exp="'/admin/edit/article/' ~ object.id" />
                <index name="product_foo" />
            </context>
        </mapping>

    </massive-search-mapping>

The above would create two mappings for the ``Product``. The second would use
the index name ``product_foo`` and override the ``url`` field.

Full example
~~~~~~~~~~~~

The following example uses all the mapping options:

.. code-block:: xml

    <!-- /path/to/YourBundle/Resources/config/massive-search/Product.xml -->
    <massive-search-mapping xmlns="http://massiveart.com/schema/dic/massive-search-mapping">

        <mapping class="Massive\Bundle\SearchBundle\Tests\Resources\TestBundle\Entity\Product">
            <index name="product" />
            <id property="id" />
            <locale property="locale" />
            <title property="title" />
            <url expr="'/path/to/' ~ object.id" />
            <description property="body" />
            <fields>
                <field name="title" type="string" />
                <field name="body" type="string" />
            </fields>

            <context name="admin">
                <index name="product_website" />
                <url exp="'/admin/edit/article/' ~ object.id" />
            </context>
        </mapping>

    </massive-search-mapping>

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

    $hits = $searchManager->createSearch('My Product')->index('product')->execute();

    foreach ($hits as $hit) {
        echo $hit->getScore();

        // @var Massive\Bundle\SearchBundle\Search\Document
        $document = $hit->getDocument();

        // retrieve the indexed documents "body" field
        $body = $document->getField('body');

        // retrieve the indexed ID of the document
        $body = $document->getId();
    }

You can also search in a specific locale:

.. code-block:: php

    $hits = $searchManager
      ->createSearch('My Article')
      ->index('article')
      ->locale('fr')
      ->execute();

Localization
------------

The MassiveSearchBundle allows you to localize indexing and search operations.

To take advantage of this feature you need to choose a localization strategy:

.. code-block:: yaml

    // app/config/config.yml
    massive_search:
        localization_strategy: index

The localization strategy decides how the documents are localized in the
search implementation.

By default the adapter is the so-called ``Noop`` which does nothing and so
localization is effectively disabled.

Strategies
~~~~~~~~~~

* ``noop``: The default strategy does nothing.
* ``index``: Creates an index per locale. For example if you store a document
  in an index named "foobar" with a locale of "fr" then the backend will use
  an index named "foobar_fr".

Searching
~~~~~~~~~

See "searching".

Mapping
~~~~~~~

TODO: Locale mapping is not currently implemented in the XML Driver.

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

You can extend the bundle by customizing the Factory class and with custom metadata drivers.

Factory
~~~~~~~

The factory service can be customized, enabling you to instantiate your own
classes for use in any listeners which you register. For example, you want to
add a "thumbnail" field to the Document object.

.. code-block:: php

    namespace My\Namespace;

    use Massive\Bundle\SearchBundle\Search\Factory as BaseFactory;

    class MyFactory extends BaseFactory
    {
        public function makeDocument()
        {
            return MyCustomDocument();
        }
    }

You must then register your factory as a service and register the ID of that
service in your main application configuration:

.. code-block:: yaml

    massive_search:
        services:
            factory: my.factory.service

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

.. _`PropertyAccess`: http://symfony.com/doc/current/components/property_access/index.html
.. _`ExpressionLanguage`: http://symfony.com/doc/current/components/expression_language/index.html
