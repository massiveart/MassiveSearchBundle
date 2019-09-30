# UPGRADE

## 2.0.0

## JMS/Metadata package update to 2.0

To update JMS/Metadata to 2.0 the following public functions were removed:

 - Massive\Bundle\SearchBundle\Search\Metadata\Driver::loadMetadataFromFile
 - Massive\Bundle\SearchBundle\Search\Metadata\Driver::getExtension

Because of the update we had to increase the required `php` version to `7.2`.
Also the requirement of symfony packages where increased to `^4.3`.

## 1.0.0

No breaking changes.

## 0.17.0

The _type field in Elasticsearch has been modified so it correctly reflects the fully qualified
class name (FQCN) of the documents being indexed in snake case. When you are using Elasticsearch
and upgrade, you will have to rebuild the indexes using the following command :

```bash
bin/console massive:search:reindex
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
