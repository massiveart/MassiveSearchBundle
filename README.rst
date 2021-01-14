MassiveSearchBundle
===================

.. image:: https://img.shields.io/github/workflow/status/massiveart/MassiveSearchBundle/Test%20application?label=test-workflow
    :target: https://github.com/massiveart/MassiveSearchBundle/actions

The purpose of this bundle is to provide flexible **site search** functionality.

This means it provides a way to index objects (for example Doctrine entities)
and then to search for them using a query string.

This bundle provides:

  - Choice of search backends (ZendSearch, Elastic Search)
  - Localization
  - Doctrine ORM integration
  - Lots of extension points

By default it is configured to use the Zend Lucene library, which must be
installed (see the `suggests` and ``require-dev`` sections in ``composer.json``.

**NOTE**: This bundle is under developmenet and is not yet stable.

Installation
------------

You can install the MassiveSearchBundle by adding it to `composer.json`:

.. code-block:: javascript

    "require": {
        ...
        "massive/search-bundle": "~1.0@dev"
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

Documentation
-------------

See the official documentation_.

.. _`documentation`: http://massivesearchbundle.readthedocs.org
