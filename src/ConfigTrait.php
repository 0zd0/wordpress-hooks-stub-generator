<?php

declare(strict_types=1);

namespace Onepix\WordPressHooksStubGenerator;

trait ConfigTrait
{
    protected static function getSchemaUrl(): string
    {
        return 'https://raw.githubusercontent.com/0zd0/wordpress-hooks-stub-generator/dev/schema.json';
    }
}
