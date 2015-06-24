Localization
------------

The MassiveSearchBundle allows you to localize indexing and search operations.

To take advantage of this feature you need to choose a localization strategy:

.. code-block:: yaml

    # app/config/config.yml
    massive_search:
        localization_strategy: index

The localization strategy decides how the documents are localized in the
search implementation.

By default the adapter is the so-called ``noop`` which does nothing and so
localization is effectively disabled.

Strategies
~~~~~~~~~~

There are currently two localization strategies:

* ``noop``: No operation, this strategy does nothing, or in other words, it
  disables localization.
* ``index``: Creates an index per locale. For example if you store a document
  in an index named "foobar" with a locale of "fr" then the backend will use
  an index named "foobar_fr".

Searching
~~~~~~~~~

See :doc:`searching`

Mapping
~~~~~~~

See :doc:`mapping`
