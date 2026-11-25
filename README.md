# Silverstripe Typesense prefix

Gives each installation of a site its own collections on a shared Typesense server, for
[elliotsawyer/silverstripe-typesense](https://codeberg.org/0x/silverstripe-typesense) 2.x
(Silverstripe 6; release 2.x or the branch `upgrade/6`).

Note: release 2.0.2 sends `num_dim: 0` for every field, which Typesense 29 rejects. Its sync
task deletes a collection before creating it again, so a sync then leaves the collection
missing. Projects on Typesense 29 stay on `dev-upgrade/6` until that is fixed upstream.

Typesense has no namespaces: collection names are global per server, and API keys only
restrict which names a key may use. Live, staging and test of one project therefore need
different collection names. Set a prefix per installation:

```
TYPESENSE_COLLECTION_PREFIX="stg_"
```

- The collection names of the YAML config (`Typesense.collections.<class>.name`) get the
  prefix, e.g. `Contacts` becomes `stg_Contacts`.
- Collection records without the prefix are hidden (`Collection::get()`). The Typesense module
  keeps its collections as database records; its sync task indexes every enabled record, and
  the update on write uses the first record of a class. After copying the database from live,
  the live records would otherwise make staging write into the live collections. With the
  filter, the sync task simply creates the prefixed records from the YAML config.
- Without the variable (or empty) nothing changes, so live usually keeps its names.

## Installation

```bash
composer require atwx/silverstripe-typesense-prefix
```

Then rebuild the index of the installation: `vendor/bin/sake tasks:TypesenseSyncTask`.

## Own queries

Code that queries Typesense directly (e.g. a `multi_search` proxy) uses the prefixed name:

```php
use Atwx\TypesensePrefix\TypesensePrefix;

$search = ['collection' => TypesensePrefix::name('Contacts'), 'q' => $q, 'query_by' => 'Title'];
```

## API keys

Give each installation a key limited to its own collections, so a misconfiguration can never
reach another installation's index:

```json
{"description": "staging", "actions": ["collections:*", "documents:*"], "collections": ["^stg_.*"]}
```

## Tests

```bash
composer install && vendor/bin/phpunit
```
