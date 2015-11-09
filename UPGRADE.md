UPGRADE
=======

0.8.0
-----

- The index name is now evaluated the same way as the field names. This means
  that the current XML mappings also change, so ``<index value="...">`` instead
  of ``<index name="...">`` have to be used.

- The search categories have been removed.

0.5.0
-----

- XML mappings have changed, the "Field" suffix has been dropped, for example
  ``<idField ... />`` is now ``<id ... />`` and ``<indexName>foo</indexName>``
  has changed to ``<index name="foo" />``.

- The `massive_search.adapter_id` key should be renamed to
  `massive_search.adapter` and it now only accepts the name of the adapter,
  e.g. `zend_search`, `elastic` or `test`.

- The `Search\Factory` methods have been renamed from `makeSomething` to
  `createSomething`
