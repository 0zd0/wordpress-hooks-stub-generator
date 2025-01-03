<?php

declare(strict_types=1);

namespace Onepix\WordPressHooksStubGenerator;

use InvalidArgumentException;

enum HookTypeEnum: string
{
    case ACTION = 'action';
    case FILTER = 'filter';
    case ACTION_REFERENCE = 'action_reference';
    case FILTER_REFERENCE = 'filter_reference';

    public const FILTERS = [
        self::FILTER,
        self::FILTER_REFERENCE,
    ];

    public const ACTIONS = [
        self::ACTION,
        self::ACTION_REFERENCE,
    ];

    public static function fromFunction(HookFunctionEnum $function): self
    {
        switch ($function) {
            case HookFunctionEnum::DO_ACTION:
            case HookFunctionEnum::DO_ACTION_REF_ARRAY:
                return self::ACTION;
                // no break
            case HookFunctionEnum::APPLY_FILTERS_REF_ARRAY:
            case HookFunctionEnum::APPLY_FILTERS:
                return self::FILTER;
        }
        throw new InvalidArgumentException('');
    }
}
