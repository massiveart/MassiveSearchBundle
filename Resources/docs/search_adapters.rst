Search Adapters
===============

Zend Lucene
-----------

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
-------

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
