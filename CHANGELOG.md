CHANGELOG
=========

dev-develop
-----------

### Bugfix

- [Configuraiton] Undefined index when elastic search is used without
  configuration
- [Search] If not localized managed indexes exist then a global search is
  performed: https://github.com/massiveart/MassiveSearchBundle/issues/38
- [Metadata] **BC BREAK**: Removed index strategies, replaced with explicit `stored` and
  `indexed` and `aggregate` flags.
- [Metadata] Added metadata cache
- [Metadata] Added metadata providers

0.5.1
-----

### Features

- [ZendLucene] Add index strategy 'INDEX_STORED_INDEXED'

0.5.0
-----

### Features

- [TestAdapter] Supports indexes
- [Mapping] Expression language
- [Persistence] Doctrine ORM event subscriber
- [Elastic] Elasticsearch adapter
- [Localization] Localization strategy support

### Enhancements

- [Config] Renamed `adapter_id` to `adapter` in configuration. See UPGRADE.md
- [Rebuild] Added core support for rebuilding indexes via
    massive:search:index:rebuild command

0.4.1
-----

- [TestAdapter] Fixed deindexing

0.4.0
-----

- [TestAdapter] Test (memory storage) adapter now uses regex when searching

0.3.2
-----

- [ZendSearch] Catch "non-wildcard characters" exception and return 0 results

0.3.1
-----

- [ZendSeach] Added workaround to avoid Fatal errors after a test suite run caused by
  the Lucene\Index __destruct() method. You can now configure the adapter to hide Exceptions
  from the Index class.

0.3
---

- Added special "complex" field mapping type to enable the mapping of array / object

0.2
---

- Support for deindex operation
- ZendAdapter is localization aware
- Added search builder, `$searchManager->createSearch('my query')->index('foo')->locale('de')->execute()`
- Changed search API. `$searchManager->search` now accepts a `SearchQuery` object
- Added TestAdapter to enable testing without any dependencies (f.e. when integrating into a third party bundle)
- Added support for adding image URLs to search "results" (not currently implemented in the XML driver)
- Added replaceable Factory service to enable better extension possiblities

0.1
---

- Added `massive:search:status` command to show information about the current implementation
- Add document ReflectionClass to HitEvent
- Added `massive:search:query` command to execute queries from the CLI
- XML driver handles meta fields Url, Title and Description
- Fire event for each found search result hit to allow listeners to modify the document (e.g.
  set URL or other data on the document).
