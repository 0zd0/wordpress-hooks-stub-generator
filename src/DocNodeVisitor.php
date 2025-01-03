<?php

declare(strict_types=1);

namespace Onepix\WordPressHooksStubGenerator;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\NodeVisitor\FindingVisitor;

class DocNodeVisitor extends FindingVisitor
{
    private ?Doc $latest_comment = null;

    public function enterNode(Node $node)
    {
        $comment = $node->getDocComment();
        if ($comment) {
            $this->latest_comment = $comment;
        }

        $filterCallback = $this->filterCallback;
        if ($filterCallback($node)) {
            if ($this->latest_comment &&
                $this->latest_comment->getEndLine() + 1 === $node->getStartLine()) {
                $node->setDocComment($this->latest_comment);
            }

            $this->foundNodes[] = $node;
        }

        return null;
    }
}
