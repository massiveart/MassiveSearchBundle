Web API
=======

The MassiveSearchBundle includes a simple controller which will return a JSON
response for search queries.

Configuration
-------------

Simply include the routing file from your main application:

.. code-block:: yaml

    massive_search:
        resource: "@MassiveSearchBundle/Resources/config/routing.yml"
        prefix: /admin

Querying
--------

You can then issue queries and reveive JSON responses:

.. code-block:: none

    # GET /admin/search?q=Dan
    [
        {
            "id": "2347",
            "document": {
                "id": "2347",
                "title": "Dan",
                "description": "",
                "class": "Acme\\Bundle\\ContactBundle\\Entity\\Contact",
                "url": "\/admin\/#contacts\/edit:2347",
                "image_url": "",
                "locale": null
            },
            "score": 0.30685281944005
        }
    ]

In specific indexes:

.. code-block:: none

    # GET /admin/search?q=Dan&index[0]=contact
    # GET /admin/search?q=Dan&index[0]=contact&index[1]=product

or in a specific locale:

.. code-block:: none

    # GET /admin/search?q=Dan&locale=fr
