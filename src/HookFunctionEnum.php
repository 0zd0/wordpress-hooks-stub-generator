<?php

declare(strict_types=1);

namespace Onepix\WordPressHooksStubGenerator;

enum HookFunctionEnum: string
{
    case DO_ACTION = 'do_action';
    case DO_ACTION_REF_ARRAY = 'do_action_ref_array';
    case APPLY_FILTERS = 'apply_filters';
    case APPLY_FILTERS_REF_ARRAY = 'apply_filters_ref_array';

    public const FILTER_FUNCTIONS = [
        self::APPLY_FILTERS,
        self::APPLY_FILTERS_REF_ARRAY,
    ];

    public const ACTION_FUNCTIONS = [
        self::DO_ACTION,
        self::DO_ACTION_REF_ARRAY,
    ];
}
