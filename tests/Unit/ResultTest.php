<?php

declare(strict_types=1);

namespace Onepix\WordPressHooksStubGenerator\Tests\Unit;

use Onepix\WordPressHooksStubGenerator\ConfigTrait;
use Onepix\WordPressHooksStubGenerator\Result;
use Onepix\WordPressHooksStubGenerator\Tests\TestCase;

class ResultTest extends TestCase
{
    use ConfigTrait;

    public function testSaveToFiles()
    {
        $actions = [];
        $filters = [];
        $result = new Result($filters, $actions);

        $result->saveToFiles($this->outputDir);

        $this->assertTrue($this->root->hasChild(Result::ACTIONS_FILE));
        $this->assertTrue($this->root->hasChild(Result::FILTERS_FILE));

        $expectedActionsContent = [
            '$schema' => $this::getSchemaUrl(),
            'hooks' => $actions,
        ];
        $expectedFiltersContent = [
            '$schema' => $this::getSchemaUrl(),
            'hooks' => $filters,
        ];

        $actualActionsContent = json_decode(
            file_get_contents($this->outputDir . '/' . Result::ACTIONS_FILE),
            true
        );
        $actualFiltersContent = json_decode(
            file_get_contents($this->outputDir . '/' . Result::FILTERS_FILE),
            true
        );

        $this->assertEquals($expectedActionsContent, $actualActionsContent);
        $this->assertEquals($expectedFiltersContent, $actualFiltersContent);
    }
}
