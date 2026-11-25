<?php

namespace Atwx\TypesensePrefix;

use ElliotSawyer\SilverstripeTypesense\Typesense;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Environment;

/**
 * Prefix for the Typesense collections of this installation, from
 * TYPESENSE_COLLECTION_PREFIX. Several installations (live, staging, test) can
 * then share one Typesense server without touching each other's collections.
 * An empty prefix changes nothing.
 */
class TypesensePrefix
{
    public static function prefix(): string
    {
        return trim((string) Environment::getEnv('TYPESENSE_COLLECTION_PREFIX'));
    }

    /**
     * Collection name as used on the Typesense server, for own queries
     * (e.g. multi_search): TypesensePrefix::name('Contacts').
     */
    public static function name(string $collection): string
    {
        return self::prefixed($collection, self::prefix());
    }

    public static function prefixed(string $collection, string $prefix): string
    {
        if ($prefix === '' || str_starts_with($collection, $prefix)) {
            return $collection;
        }
        return $prefix . $collection;
    }

    /**
     * Adds the prefix to the collection names of the YAML config
     * (Typesense.collections.<class>.name). Called from _config.php on boot.
     */
    public static function applyToConfig(): void
    {
        $prefix = self::prefix();
        if ($prefix === '') {
            return;
        }
        $collections = Config::inst()->get(Typesense::class, 'collections') ?: [];
        $prefixed = self::prefixCollections($collections, $prefix);
        if ($prefixed !== $collections) {
            Config::modify()->set(Typesense::class, 'collections', $prefixed);
        }
    }

    /**
     * @param array<string, array<string, mixed>> $collections
     * @return array<string, array<string, mixed>>
     */
    public static function prefixCollections(array $collections, string $prefix): array
    {
        foreach ($collections as $class => $collection) {
            if (is_array($collection) && !empty($collection['name'])) {
                $collections[$class]['name'] = self::prefixed((string) $collection['name'], $prefix);
            }
        }
        return $collections;
    }

    /**
     * SQL LIKE pattern matching every name that starts with the prefix.
     */
    public static function likePattern(string $prefix): string
    {
        return addcslashes($prefix, '\\%_') . '%';
    }
}
