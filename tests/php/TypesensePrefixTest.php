<?php

namespace Atwx\TypesensePrefix\Tests;

use Atwx\TypesensePrefix\TypesensePrefix;
use PHPUnit\Framework\TestCase;

class TypesensePrefixTest extends TestCase
{
    public function testPrefixed(): void
    {
        $this->assertSame('stg_Contacts', TypesensePrefix::prefixed('Contacts', 'stg_'));
        $this->assertSame('stg_Contacts', TypesensePrefix::prefixed('stg_Contacts', 'stg_'), 'no double prefix');
        $this->assertSame('Contacts', TypesensePrefix::prefixed('Contacts', ''), 'empty prefix changes nothing');
    }

    public function testPrefixCollections(): void
    {
        $config = [
            'App\Models\Contact' => ['name' => 'Contacts', 'fields' => [['name' => 'Title']]],
            'App\Models\Page' => ['fields' => []],
        ];
        $result = TypesensePrefix::prefixCollections($config, 'stg_');
        $this->assertSame('stg_Contacts', $result['App\Models\Contact']['name']);
        $this->assertSame('Title', $result['App\Models\Contact']['fields'][0]['name'], 'field names stay as they are');
        $this->assertArrayNotHasKey('name', $result['App\Models\Page']);
    }

    public function testLikePatternEscapesWildcards(): void
    {
        $this->assertSame('kjrcrmtest\_%', TypesensePrefix::likePattern('kjrcrmtest_'));
        $this->assertSame('a\%b%', TypesensePrefix::likePattern('a%b'));
    }
}
