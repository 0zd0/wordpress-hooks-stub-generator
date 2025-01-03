<?php

namespace Onepix\WordPressHooksStubGenerator\Tests\Util;

enum StubFileEnum: string
{
    case DEFAULT_HOOKS = 'default';
    case WITHOUT_DOC = 'without_doc';
    case ALL_FUNCTIONS = 'all_functions';
    case WITH_IGNORE_HOOKS = 'with_ignore_hooks';
    case WITH_IGNORE_FILES = 'with_ignore_files';
    case WITH_VARIABLE = 'with_variable';
    case VALIDATE_BY_SCHEMA = 'test';
}
