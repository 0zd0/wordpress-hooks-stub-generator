<?php

declare(strict_types=1);

namespace Onepix\WordPressHooksStubGenerator;

enum CommandOptionEnum: string
{
    case INPUT = 'input';
    case OUTPUT = 'output';
    case IGNORE_HOOKS = 'ignore-hooks';
    case IGNORE_FILES = 'ignore-files';
}
