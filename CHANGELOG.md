CHANGELOG
=========

* 0.17.3 (2019-08-22)
    * ENHANCEMENT  #130 Add command default names

* 0.17.2 (2019-08-20)
    * ENHANCEMENT  #129 Added dispatching of pre-deindex event
    * ENHANCEMENT  #127 Custom repository method bugfix

* 0.17.1 (2019-02-05)
    * HOTFIX       #126 Fix compatibility to php 7.2

* 0.17.0 (2018-09-25)
    * ENHANCEMENT  #124 Updated dependencies for symfony4 and fixed deprecations 
    * ENHANCEMENT  #114 Updated elasticsearch dependency
    * ENHANCEMENT  #111 Increased php memory_limit and fixed elasticsearch-installation for travis

* 0.16.2 (2017-06-16)
    * HOTFIX      #3406 (Sulu) Modified ElasticSearchAdapter search function, removed size paramter once limit is empty

* 0.16.1 (2017-05-11)
    * HOTFIX      #107 Added encode and decode field-name to elasticsearch adapter

* 0.16.0 (2016-10-06)
    * BUGFIX      #104 Added `__id` field for compatability reasons
    * ENHANCEMENT #103 Added elasticsearch version config parameter

* 0.15.0 (2016-08-08)
    * FEATURE     #98 Added json converter

* 0.14.0 (2016-07-21)
    * ENHANCEMENT #96 Updated elasticsearch dependency
    * BUGFIX      #93 Fixed composer-events
    * BUGFIX      #87 Added missing support for image field in XML document

* 0.13.2 (2016-06-01)
    * HOTFIX  #95 Fixed prefix is-variant when index has wrong prefix 
    * HOTFIX  #92 Fixed memory usage for reindex

* 0.13.1 (2016-05-09)
    * BUGFIX  #90 Fixed elastic search params array

* 0.13.0 (2016-04-11)
    * FEATURE #80 Added composer handler for creating zend lucene directory
    * FEATURE #74 Added support for resuming interupted reindexing tasks
    * FEATURE #74 Deprecated `massive:search:index:rebuild` command in favor
                  of `massive:search:reindex`
    * FEATURE #74 [BC BREAK] Removed REINDEX event. Reindex providers must be
      used instead.
    * BUGFIX  #73 Removed typehint to avoid failing testcases in php7

* 0.12.0 (2015-12-11)
    * HOTFIX  #69 Improve memory usage of rebuilding search indexes
    * HOTFIX  #72 Fixed null date-value

* 0.11.0 (2015-12-01)
    * BUGFIX  #71 Fixed caching of repository method
    * BUGFIX  #70 Added missing passing of options to PrefixDecorator
    * FEATURE #67 Added support for sort/order for elastic search
    * FEATURE #68 Introduce decorators for index names and introduce prefixes
    * FEATURE #66 Added support for date
    * FEATURE #60 Added support for custom type converter
    * FEATURE #60 Added support for arrays

* 0.10.0 (2015-11-19)
    * FEATURE #66 [Converter] Added support for date
    * FEATURE #60 [Converter] Added support for custom type converter
    * FEATURE #60 [General]   Added support for arrays
     
* 0.9.0
    * FEATURE Index name can now also be created using expression language
    * FEATURE Search categories have been removed

* 0.8.0
    * FEATURE Added reindex directive
    * FEATURE Removed prophecy bridge
    * FEATURE Added Style-CI configuration

* 0.7.0
    * BUGFIX Added scalar check

* 0.6.0
    * BUGFIX  Undefined index when elastic search is used without
              configuration
    * BUGFIX  If not localized managed indexes exist then a global search is
              performed: https://github.com/massiveart/MassiveSearchBundle/issues/38
    * BUGFIX  Removed index strategies, replaced with explicit `stored` and
              `indexed` and `aggregate` flags (**BC BREAK**).
    * BUGFIX  Added metadata cache
    * BUGFIX  Added metadata providers

* 0.5.1
    * FEATURE Add index strategy 'INDEX_STORED_INDEXED'

* 0.5.0
    * FEATURE     Supports indexes
    * FEATURE     Expression language
    * FEATURE     Doctrine ORM event subscriber
    * FEATURE     Elasticsearch adapter
    * FEATURE     Localization strategy support
    * ENHANCEMENT Renamed `adapter_id` to `adapter` in configuration. See UPGRADE.md
    * ENHANCEMENT Added core support for rebuilding indexes via
                             massive:search:index:rebuild command

* 0.4.1
    * BUGFIX Fixed deindexing

* 0.4.0
    * FEATURE Test (memory storage) adapter now uses regex when searching

0.3.2
    * BUGFIX Catch "non-wildcard characters" exception and return 0 results

0.3.1
    * BUGFIX Added workaround to avoid Fatal errors after a test suite run caused by
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
