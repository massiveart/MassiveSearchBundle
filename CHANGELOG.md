CHANGELOG
=========

dev-master
----------

- ZendAdapter is localization aware
- Added search builder, `$searchManager->createSearch('my query')->index('foo')->locale('de')->go()`
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
