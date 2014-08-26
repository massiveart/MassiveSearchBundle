CHANGELOG
---------

dev-master
----------

- Added `massive:search:query` command to execute queries from the CLI
- XML driver handles meta fields Url, Title and Description
- Fire event for each found search result hit to allow listeners to modify the document (e.g.
  set URL or other data on the document).
