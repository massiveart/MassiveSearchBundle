Commands
========

The MassiveBuildBundle provides some commands.

``massive:search:query``
------------------------

Perform a query from the command line::

    $ php app/console massive:search:query "Foobar" --index="barfoo"
    +------------------+--------------------------------------+-----------+-------------+-----------+------------------------+
    | Score            | ID                                   | Title     | Description | Url       | Class                  |
    +------------------+--------------------------------------+-----------+-------------+-----------+------------------------+
    | 0.53148467371593 | ac984681-ca92-4650-a9a6-17bc236f1830 | Structure |             | structure | OverviewStructureCache |
    +------------------+--------------------------------------+-----------+-------------+-----------+------------------------+

``massive:search:status``
-------------------------

Display status information for the current search implementation::

    $ php app/console massive:search:status
    +-------------+--------------------------------------------------------------+
    | Field       | Value                                                        |
    +-------------+--------------------------------------------------------------+
    | Adapter     | Massive\Bundle\SearchBundle\Search\Adapter\ZendLuceneAdapter |
    | idx:product | {"size":11825,"nb_files":36,"nb_documents":10}               |
    +-------------+--------------------------------------------------------------+

.. _command_search_index_rebuild:

``massive:search:index:rebuild``
--------------------------------

Rebuild the search index.

.. code-block:: bash


    $ php app/console massive:search:reindex

Options:

- ``provider``: Specify a specific reindex provider (can be specified
  multiple times).
- ``batch-size``: Specify the size of the batches.

.. warning::

    Rebuilding the search index is a memory intensive task and it will leak
    memory over time. You can mitigate this effect by running this command
    with ``--env=prod`` which should remove unnecessary overhead from logging
    systems etc.

.. note::

    If a reindexing command is interupted it will, on the next execution, ask
    if it should resume from its last checkpoint.

``massive:search:purge``
------------------------

Purge one or more or all indexes.

.. code-block:: bash

    $ php app/console massive:search:purge

Execute without arguments in order to see a list of current indexes.

Specify indexes with the ``--index`` option:

.. code-block:: bash

    $ php app/console massive:search:purge --index=index_1 --index=index_2

You can purge all indexes:

.. code-block:: bash

    $ php app/console massive:search:purge --all

Options:

 - ``index``: Specify index to purge
 - ``all``: Purge all indexes.
 - ``force``: Do not ask for confirmation.

``massive:search:optimize``
---------------------------

Optimize all search indices. Affects only indices that are managed with the ``zend_lucene`` adapter at the moment. It is recommended to configure this as cronjob when ``zend_lucene`` adapter.

.. code-block:: bash

    $ php app/console massive:search:optimize
