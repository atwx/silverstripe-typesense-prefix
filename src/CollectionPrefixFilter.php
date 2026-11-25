<?php

namespace Atwx\TypesensePrefix;

use SilverStripe\Core\Extension;
use SilverStripe\ORM\DataQuery;
use SilverStripe\ORM\Queries\SQLSelect;

/**
 * Hides the collection records of other installations (Collection::get()).
 *
 * The Typesense module keeps its collections as records in the database, and both
 * the sync task (all enabled records) and the update on write (first record of a
 * class) work from those records. After copying a database from live to staging,
 * the live records would otherwise make staging write into the live collections.
 * With a prefix set, only records whose name starts with it exist for this
 * installation; the sync task creates the prefixed ones from the YAML config.
 */
class CollectionPrefixFilter extends Extension
{
    protected function augmentSQL(SQLSelect $query, ?DataQuery $dataQuery = null): void
    {
        $prefix = TypesensePrefix::prefix();
        if ($prefix === '') {
            return;
        }
        $query->addWhere([
            '"TypesenseCollection"."Name" LIKE ?' => TypesensePrefix::likePattern($prefix),
        ]);
    }
}
