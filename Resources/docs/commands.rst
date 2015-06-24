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

``massive:search:index:rebuild``
--------------------------------

Rebuild the search index. This command issues an event which instructs any
listeners to rebuild all of the mapped classes.

.. code-block:: bash


    $ php app/console massive:search:index:rebuild
    Rebuilding: Acme\Bundle\ContactBundle\Entity\Contact [OK] 1 entities indexed
    Rebuilding: Acme\Bundle\ContactBundle\Entity\Account [OK] 0 entities indexed

Options:

- ``purge``: Purge each affected index before reindexing.
- ``filter``: Only apply rebuild to classes matching the given regex pattern,
  e.g. ``.*Contact$``.
