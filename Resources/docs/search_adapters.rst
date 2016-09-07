Search Adapters
===============

Zend Lucene
-----------

The `Zend Lucene`_ search is the default implementation. It requires no external
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

    # app/config/config.yml
    massive_search:
        adapter: zend_lucene

The search data is stored on the filesystem. By default it will be placed in
``app/data``. This can be changed as follows:

.. code-block:: yaml

    # app/config/config.yml
    massive_search:
        # ...
        adapters:
            zend_lucene:
                basepath: /path/to/data

.. note::
    
    The Zend Lucene library was originally written for Zend Framework 1 (ZF1),
    it was later ported to Zend Framework 2 (ZF2) and made available through
    composer. 

    Neither the ZF1 or ZF2 versions are maintained, and the ZF1 version is
    more up-to-date than the ZF2 version which this library uses and neither
    are compatible with the Apache Lucene index format.

    Long story short: the library is not maintained, but we have encountered
    no issues with it and it is the only native PHP search library.

Elasticsearch
-------------

The Elasticsearch adapter allows you to use the
`Elasticsearch`_ search engine.

You will need to include the official client in ``composer.json``:

.. code-block:: javascript

    "require": {
        ...
        "elasticsearch/elasticsearch": "~1.3",
    }

and select the adapter in your application configuration:

.. code-block:: yaml

    # app/config/config.yml
    massive_search:
        adapter: elastic

By default assumes the server is running on ``localhost:9200``. You
change this, or configure more severs as follows:

.. code-block:: yaml

    # app/config/config.yml
    massive_search:
        # ...
        adapters:
            elastic:
                version: 2.2
                hosts: [ 192.168.0.63:9200, 192.168.0.63:9200 ]

Elasticsearch has removed some deprecations in 2.3 so we introduced a configuration
which indicates the version of the hosts. If you user a newer version than 2.2 you
should provide it in the config.

.. note::

    Elasticsearch has a default result limit of 10.

.. _`Elasticsearch`: http://www.elasticsearch.org
.. _`Zend Lucene`: http://framework.zend.com/manual/1.12/en/zend.search.lucene.html
