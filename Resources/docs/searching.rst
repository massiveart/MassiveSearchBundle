Searching and the Search Manager
================================

The search manager service is the only part of the system which you need to
talk to, it provides all the methods you need to manage searching and the
search index.

The search manager can be retrieved from the DI container using
``massive_search.search_manager``.

For example:

.. code-block:: php

    <?php
    $searchManager = $container->get('massive_search.search_manager');

Searching
---------

Currently only supported by query string is supported. The query string
is passed directly to the search library:

.. code-block:: php

    <?php
    $hits = $searchManager->createSearch('My Product')->execute();

    foreach ($hits as $hit)
    {
        echo $hit->getScore();

        /** @var Massive\Bundle\SearchBundle\Search\Document */
        $document = $hit->getDocument();

        // retrieve the indexed documents "body" field
        $body = $document->getField('body');

        // retrieve the indexed ID of the document
        $id = $document->getId();
    }

You can also search in a specific locale and a specific index:

.. code-block:: php

    <?php
    $hits = $searchManager
      ->createSearch('My Article')
      ->index('article')
      ->locale('fr')
      ->execute();

Or search in multiple indexes:

.. code-block:: php

    <?php
    $hits = $searchManager
      ->createSearch('My Article')
      ->indexes(array('article', 'product'))
      ->execute();

Search results are returned as "hits", each hit contains a "document". The data may look something like the following
when represented as JSON:

.. code-block:: javascript

    {
        "id": "2347",
        "document": {
            "id": "2347",
            "title": "My Article",
            "description": "",
            "class": "Acme\\Bundle\\ArticleBundle\\Entity\\Article",
            "url": "\/admin\/articles\/edit/2347",
            "image_url": "",
            "locale": null
        },
        "score": 0.39123123123123
    }

Search results are paged. By default, only the first page is returned and a page contains 10 results. This can be
controlled on the SearchQuery like this:

.. code-block:: php

    <?php
    $hits = $searchManager
      ->createSearch('My Article')
      ->setLimit(100) // A page now contains 100 results
      ->setOffset(1) // Return the second page of results
      ->execute();

Indexing and deindexing
-----------------------

After you have mapped your object (see :doc:`mapping`) you can index it:

.. code-block:: php

    <?php
    $object = // your mapped object
    $searchManager->index($object);

And deindex it:

.. code-block:: php

    <?php
    $object = // your mapped object
    $searchManager->deindex($object);

Flushing
--------

Flushing will tell the search adapter to process all of its pending tasks
(for example, indexing, deindexing) now. This is sometimes useful when you
need to ensure that data in the search index is in a certain state before
performing more processing (for example when testing).

.. code-block:: php

    <?php
    $object = // your mapped object
    $searchManager->flush();

Note that flushing is not required, and that it is better not to flush if you
can avoid it.

