Introduction
============

The MassiveSearchBundle provides an extensible, localized search *abstraction* which is
concerned primarily with providing a site-search engine.

It allows you to map documents using XML (or a custom driver), index them with
a search *adapter* and search for them. The search "results" (documents) are
returned in a format focused on the use case of providing a list of search
results on which the user clicks.

.. code-block:: none

    +--------+ <title> <link>
    |        | 
    | <img>  | <body>
    |        |
    +--------+


