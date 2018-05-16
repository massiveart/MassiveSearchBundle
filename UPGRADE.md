# UPGRADE

## develop

### Zend lucene basepath was changed 

The default basepath was set from `kernel.root_dir%/data` to  `%kernel.var_dir%/indexes`:

```yml
massive_search:
    adapters:
        zend_lucene:
            basepath: "kernel.root_dir%/data"
```

## 0.11.0

### IndexName decorators

The names of the indexes in the system can now be altered using decorators. There
is also a `PrefixDecorator`, which can prefixes the index name with an installation
specific prefix, which can be set using the `massive_search.metadata.prefix`
parameter.

The configuration parameter `massive_search.localization_strategy` have been removed.

The indexes have to be rebuilt using the following command:

```bash
app/console massive:search:index:rebuild --purge
```

## 0.8.0

- The index name is now evaluated the same way as the field names. This means
  that the current XML mappings also change, so ``<index value="...">`` instead
  of ``<index name="...">`` have to be used.

- The search categories have been removed.

## 0.5.0

- XML mappings have changed, the "Field" suffix has been dropped, for example
  ``<idField ... />`` is now ``<id ... />`` and ``<indexName>foo</indexName>``
  has changed to ``<index name="foo" />``.

- The `massive_search.adapter_id` key should be renamed to
  `massive_search.adapter` and it now only accepts the name of the adapter,
  e.g. `zend_search`, `elastic` or `test`.

- The `Search\Factory` methods have been renamed from `makeSomething` to
  `createSomething`
