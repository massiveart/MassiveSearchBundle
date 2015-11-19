CHANGELOG
=========

* dev-develop
    * FEATURE #66 [Converter] Added support for date
    * FEATURE #60 [Converter] Added support for custom type converter
    * FEATURE #60 [General]   Added support for arrays
     
* 0.9.0
    * FEATURE [Configuration] Index name can now also be created using expression language
    * FEATURE [Configuration] Search categories have been removed

* 0.8.0
    * FEATURE [Rebuild] Added reindex directive
    * FEATURE [Tests]   Removed prophecy bridge
    * FEATURE [General] Added Style-CI configuration

* 0.7.0
    * BUGFIX [Metadata] Added scalar check

* 0.6.0
    * BUGFIX [Configuraiton] Undefined index when elastic search is used without
                             configuration
    * BUGFIX [Search]        If not localized managed indexes exist then a global search is
                             performed: https://github.com/massiveart/MassiveSearchBundle/issues/38
    * BUGFIX [Metadata]      Removed index strategies, replaced with explicit `stored` and
                             `indexed` and `aggregate` flags (**BC BREAK**).
    * BUGFIX [Metadata]      Added metadata cache
    * BUGFIX [Metadata]      Added metadata providers

* 0.5.1
    * FEATURE [ZendLucene] Add index strategy 'INDEX_STORED_INDEXED'

* 0.5.0
    * FEATURE [TestAdapter]  Supports indexes
    * FEATURE [Mapping]      Expression language
    * FEATURE [Persistence]  Doctrine ORM event subscriber
    * FEATURE [Elastic]      Elasticsearch adapter
    * FEATURE [Localization] Localization strategy support
    * ENHANCEMENT [Config]   Renamed `adapter_id` to `adapter` in configuration. See UPGRADE.md
    * ENHANCEMENT [Rebuild]  Added core support for rebuilding indexes via
                             massive:search:index:rebuild command

* 0.4.1
    * BUGFIX [TestAdapter] Fixed deindexing

* 0.4.0
    * FEATURE [TestAdapter] Test (memory storage) adapter now uses regex when searching

0.3.2
    * BUGFIX [ZendSearch] Catch "non-wildcard characters" exception and return 0 results

0.3.1
    * BUGFIX [ZendSeach] Added workaround to avoid Fatal errors after a test suite run caused by
                         the Lucene\Index `__destruct()` method. You can now configure the adapter to hide Exceptions
                         from the Index class.

* 0.3
    * FEATURE Added special "complex" field mapping type to enable the mapping of array / object

* 0.2
    * Support for deindex operation
    * ZendAdapter is localization aware
    * Added search builder, `$searchManager->createSearch('my query')->index('foo')->locale('de')->execute()`
    * Changed search API. `$searchManager->search` now accepts a `SearchQuery` object
    * Added TestAdapter to enable testing without any dependencies (f.e. when integrating into a third party bundle)
    * Added support for adding image URLs to search "results" (not currently implemented in the XML driver)
    * Added replaceable Factory service to enable better extension possiblities

* 0.1
    * Added `massive:search:status` command to show information about the current implementation
    * Add document ReflectionClass to HitEvent
    * Added `massive:search:query` command to execute queries from the CLI
    * XML driver handles meta fields Url, Title and Description
    * Fire event for each found search result hit to allow listeners to modify the document (e.g.
      set URL or other data on the document).
