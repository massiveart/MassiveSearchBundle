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

Metadata Drivers
----------------

Extend the ``Metadata\Driver\DriverInterface`` and add the tag
``massive_search.metadata.driver`` tag to your implementations service
definition.

.. code-block:: xml

    <service id="massive_search.metadata.driver.xml" class="%massive_search.metadata.driver.xml.class%">
        <argument type="service" id="massive_search.metadata.file_locator" />
        <tag type="massive_search.metadata.driver" />
    </service>

This is non-trivial and you should use the existing XML implementation as a
guide.

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
