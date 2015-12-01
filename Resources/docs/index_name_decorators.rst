Index name decorators
=====================

The bundle offers the possibility to influence the generated index names using
the `decorator pattern`_. If you want to add your own decorator, you have to
implement the `IndexNameDecoratorInterface`, and register the new decorator in
the `Symfony dependency injection container`_. Consider that you should pass
the current most outer decorator as an argument, so that it can be called in
your decorator.

Afterwards you have to make sure that your new decorator is returned when
getting the service for `massive_search.index_name_decorator.default`, what can
be achieved by using the `alias feature`_ of Symfony.

Available index name decorators
-------------------------------

IndexNameDecorator
~~~~~~~~~~~~~~~~~~

The index name decorator returns the value for the index name from the
:doc:`mapping` files.

LocalizationDecorator
~~~~~~~~~~~~~~~~~~~~~

This decorator adds the locale to the index name, in case the document has a
locale parameter.

PrefixDecorator
~~~~~~~~~~~~~~~

The `PrefixDecorator` is responsible for adding a prefix to the index, so that
the Bundle knows if the given index name is managed by the search bundle. This
prefix can be configured by the `massive_search.metadata.prefix` configuration
setting, which defaults to ``massive``.

.. warning::

    Make sure that the other indexes you might have in your system have a name
    not starting with the prefix defined by this configuration. Otherwise it
    might be possible that the MassiveSearchBundle modifies or deletes these
    indexes.

.. _`decorator pattern`: https://en.wikipedia.org/wiki/Decorator_pattern
.. _`Symfony dependency injection container`: http://symfony.com/doc/current/components/dependency_injection/introduction.html#setting-up-the-container-with-configuration-files
.. _`alias feature`: http://symfony.com/doc/current/components/dependency_injection/advanced.html#aliasing

