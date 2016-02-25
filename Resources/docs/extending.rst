Extending
=========

You can extend the bundle by customizing the Factory class and with custom metadata drivers.

Factory
-------

The factory service can be customized, enabling you to instantiate your own
classes for use in any listeners which you register. For example, you want to
add a "thumbnail" field to the Document object and create a custom document
``MyCustomDocument``:

.. code-block:: php

    <?php

    namespace My\Namespace;

    use Massive\Bundle\SearchBundle\Search\Factory as BaseFactory;

    class MyFactory extends BaseFactory
    {
        public function makeDocument()
        {
            return MyCustomDocument();
        }
    }

You must then register your factory as a service and register the ID of that
service in your main application configuration:

.. code-block:: yaml

    massive_search:
        services:
            factory: my.factory.service

Metadata Providers
------------------

Massive Search allows you to implement the metadata `ProviderInterface`,
instances of which can load metadata from both domain object and search
document instances.

.. note:: 

    The metadata system is based upon the `JMS Metadata`_ library, although it
    diverges in that we allow you to load metadata from object instances instead
    of only the class name. It is still possible to implement standard JMS
    Metadata drivers as detailed below.

To implement a provider just implement the ``Metadata\\ProviderInterface`` and
add your class to the dependency injection configuration with the
``massive_search.metadata.provider`` tag:

.. code-block:: xml

    <service id="massive_search.metadata.provider.foo" class="Vendor\\Search\\Provider">
        <tag type="massive_search.metadata.provider" />
    </service>

You can also implement standard JMS serializer drivers. This would be optimal
if you only need the class name to determine the metadata. Extend the
``Metadata\Driver\DriverInterface`` and add the tag
``massive_search.metadata.driver`` tag to your implementations service
definition.

.. code-block:: xml

    <service id="massive_search.metadata.driver.xml" class="%massive_search.metadata.driver.xml.class%">
        <argument type="service" id="massive_search.metadata.file_locator" />
        <tag type="massive_search.metadata.driver" />
    </service>

.. note::

    Adding new metadata providers is non-trivial, you should check the
    existing code for implementation details.

Converters
----------

Massive Search allows you to implement custom types. This types can be defined
with custom converters, which converts the value of a fields into an indexable
format (currently only string or array).

A converter is a simple class which implements the interface
``Massive\Bundle\SearchBundle\Search\Converter\ConverterInterface``. To add
the converter to the system you simply add a tag to your custom service.

.. code-block:: xml

    <service id="massive_search.converter.foo_converter" class="Vendor\\Search\\FooConverter">
        <tag type="massive_search.converter" from="foo" />
    </service>

Reindex Providers
-----------------

When you implement a new driver you will most likely want to be able to
*re-index* objects which fall within the scope of this driver. In order to do
this you must create a class implementing ``ReindexProviderInterface`` and
add it to your service configutation with the ``massive_search.reindex.provider`` tag:

.. code-block:: xml

    <service id="massive_search.reindex.provider.foo_provider" class="Vendor\\Search\\Reindex\\FooProvider">
        <tag name="massive_search.reindex.provider" id="foo"/>
    </service>

Events
------

The MassiveSearchBundle issues events which can be listened to by using the
standard Symfony event dispatcher. You can register a listener in your
dependency injection configuration as follows:

.. code-block:: xml

     <!-- rebuild structure index on massive:search:index:rebuild -->
     <service id="acme.event_listener.search"
     class="Acme\Search\SearchListener">
         <tag name="kernel.event_listener" event="<event_name>" method="methodToCall" />
     </service>

``massive_search.hit``
~~~~~~~~~~~~~~~~~~~~~~

The ``SearchManager`` will fire an event of type ``HitEvent`` in the Symfony EventDispatcher named
``massive_search.hit``.

The ``HitEvent`` contains the hit object and the reflection class of the
object which was originally indexed.

For example:

.. code-block:: php

    <?php

    namespace Sulu\Bundle\SearchBundle\EventListener;

    use Massive\Bundle\SearchBundle\Search\Event\HitEvent;

    class HitListener
    {
        public function onHit(HitEvent $event)
        {
            $reflection = $event->getDocumentReflection();
            if (false === $reflection->isSubclassOf('MyClass')) {
                return;
            }

            $document = $event->getDocument();
            $document->setUrl('Foo' . $document->getUrl());
        }
    }

``massive_search.pre_index``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Fired before a document is indexed. See the code for more information.

``massive_search.search``
~~~~~~~~~~~~~~~~~~~~~~~~~

Fired when a search request is performed. See the code for more information.

.. JMS Metadata_: https://github.com/schmittjoh/metadata
