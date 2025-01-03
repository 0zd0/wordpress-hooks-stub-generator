<?php

declare(strict_types=1);

namespace Onepix\WordPressHooksStubGenerator;

use DOMDocument;
use Error;
use Exception;
use Parsedown;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use RuntimeException;
use Symfony\Component\Finder\SplFileInfo;

class Generator
{
    private Finder $finder;
    private Standard $printer;
    private Parsedown $markdown;
    private SplFileInfo $currentFile;
    private string $currentHook;

    public function __construct(
        private array $ignoreFiles = [],
        private array $ignoreHooks = [],
        private readonly array $inputDirs = []
    ) {
        $this->printer = new Standard();
        $this->markdown = Parsedown::instance();
    }

    /**
     */
    public function getIgnoreFiles(): array
    {
        return $this->ignoreFiles;
    }

    /**
     */
    public function getIgnoreHooks(): array
    {
        return $this->ignoreHooks;
    }

    /**
     */
    public function setIgnoreFiles(array $ignoreFiles): void
    {
        $this->ignoreFiles = $ignoreFiles;
    }

    /**
     */
    public function setIgnoreHooks(array $ignoreHooks): void
    {
        $this->ignoreHooks = $ignoreHooks;
    }

    /**
     */
    public function setFinder(Finder $finder): void
    {
        $this->finder = $finder;
    }

    public function generate(): Result
    {
        $filters = $actions = [];
        $parser = (new ParserFactory())->createForHostVersion();

        $visitor = new DocNodeVisitor(
            fn (Node $node) => ($node instanceof Node\Expr\FuncCall)
        );

        $traverser = new NodeTraverser();
        $traverser->addVisitor($visitor);

        $unparsedFiles = [];
        foreach ($this->finder as $file) {
            if ($this->isFileIgnored($file)) {
                continue;
            }

            $this->currentFile = $file;
            $stmts = null;

            try {
                $stmts = $parser->parse($file->getContents());
            } catch (Error|RuntimeException $e) {
                $unparsedFiles[$file->getPathname()] = $e;
            }

            if ($stmts) {
                $traverser->traverse($stmts);
                /**
                 * @var FuncCall[] $foundNodes
                 */
                $foundNodes = $visitor->getFoundNodes();
                $hooks = $this->generateHooksFromNodes($foundNodes);
                $filters = array_merge($filters, $this->extractFilters($hooks));
                $actions = array_merge($actions, $this->extractActions($hooks));
            }
        }

        return new Result(
            $filters,
            $actions,
            $unparsedFiles
        );
    }

    /**
     */
    public function generateHooksFromNodes(
        array $nodes
    ): array {
        $hooks = [];

        foreach ($nodes as $node) {
            if (!$this->getFunction($node)) {
                continue;
            }

            if (!isset($node->args[0])) {
                continue;
            }

            $this->currentHook = $this->parseHookName($node);

            try {
                $generated = $this->generateHookFromNode($node);
                if ($generated) {
                    $hooks[] = $generated;
                }
            } catch (Exception $e) {
                echo $e->getMessage() . '\n';
            }
        }

        return $hooks;
    }

    /**
     * @throws Exception
     */
    public function generateHookFromNode(
        FuncCall $node
    ): ?array {
        $hookName = $this->parseHookName($node);
        if (in_array($hookName, $this->getIgnoreHooks(), true)) {
            return null;
        }

        $docComment = $node->getDocComment();
        if ($docComment && str_starts_with($docComment->getText(), '/** This filter is documented in')) {
            return null;
        }

        return array_filter([
            'name' => $hookName,
            'aliases' => $this->parseAliases($node),
            'file' => $this->parseFileName(),
            'type' => $this->parseType($node)->value,
            'doc' => $this->parseDoc($node),
            'args' => $this->parseCountArgs($node),
        ], fn ($value) => $value !== null);
    }

    private function parseHookName(
        FuncCall $node
    ): string {
        return $this->sanitizeHookName($node->args[0]->value);
    }

    /**
     */
    public function sanitizeHookName(
        Expr $hookName
    ): string {
        $hook_name = $this->printer->prettyPrintExpr($hookName);
        return trim($hook_name, "'\"");
    }

    private function parseAliases(
        FuncCall $node
    ): ?array {
        $docBlock = $this->getDocBlock($node);
        if (is_null($docBlock)) {
            return null;
        }

        $html = $this->markdown->text((string) $docBlock->getDescription());
        $html = str_replace("\n", ' ', $html);
        if (!str_contains($html, 'Possible hook names include')) {
            return null;
        }

        $aliases = [];

        $html = explode('Possible hook names include', $html, 2);
        $html = explode('</ul>', end($html));

        $dom = new DOMDocument();
        $dom->loadHTML(reset($html));

        foreach ($dom->getElementsByTagName('li') as $li) {
            $aliases[] = $li->nodeValue;
        }

        sort($aliases);

        return $aliases;
    }

    private function parseFileName(): string
    {
        foreach ($this->inputDirs as $root) {
            $rootWithSeparator = rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            if (str_starts_with($this->currentFile->getPathname(), $rootWithSeparator)) {
                return substr($this->currentFile->getPathname(), strlen($rootWithSeparator));
            }
        }
        return $this->currentFile->getPathname();
    }

    private function getFunction(
        FuncCall $node
    ): ?HookFunctionEnum {
        if ($node->name instanceof Name) {
            return HookFunctionEnum::tryFrom($node->name->toString());
        }

        return null;
    }

    private function parseType(
        FuncCall $node
    ): HookTypeEnum {
        return HookTypeEnum::fromFunction($this->getFunction($node));
    }

    private function getDocBlock(
        Node $node
    ): ?DocBlock {
        $docComment = $node->getDocComment();
        $docCommentText = $docComment?->getText();
        return is_null($docCommentText) ? null : DocBlockFactory::createInstance()->create($docCommentText);
    }

    /**
     * @throws Exception
     */
    private function parseDoc(
        FuncCall $node
    ): array {
        $docBlock = $this->getDocBlock($node);
        if (is_null($docBlock)) {
            return [
                'description' => '',
                'long_description' => '',
                'long_description_html' => '',
                'tags' => [],
            ];
        }
        $summary = trim($docBlock->getSummary());

        return [
            'description' => str_replace("\n", ' ', $summary),
            'long_description' => $this->parseLongDescription($docBlock),
            'long_description_html' => $this->parseLongHtmlDescription($docBlock),
            'tags' => $this->parseTags($docBlock),
        ];
    }

    private function parseLongHtmlDescription(
        DocBlock $docBlock
    ): string {
        $html = $this->markdown->text((string) $docBlock->getDescription());
        return str_replace("\n", ' ', $html);
    }

    private function parseLongDescription(
        DocBlock $docBlock
    ): string {
        $description = $this->cleanNewLines((string) $docBlock->getDescription());

        // Restore line breaks before list items that start with dashes/hyphens.
        // This ensures proper formatting for unordered lists after text concatenation.
        $description = str_replace('  - ', "\n  - ", $description);

        // Restore line breaks before numbered list items (1-9).
        // This ensures proper formatting for ordered lists after text concatenation.
        $description = preg_replace_callback(
            '# ([1-9])\. #',
            static fn (array $matches): string => "\n {$matches[1]}. ",
            $description
        );

        return $description;
    }

    /**
     * @throws Exception
     */
    private function parseTags(
        DocBlock $docBlock
    ): array {
        $tags = [];

        foreach ($docBlock->getTags() as $tag) {
            $content = '';
            $tagString = (string) $tag;
            $tagName = $tag->getName();
            $formatHtml = fn ($text) => preg_replace('/^<p>(.)<\/p>$/', '$1', $this->markdown->text($text));

            if (! method_exists($tag, 'getVersion') && method_exists($tag, 'getDescription')) {
                $content = (string) $tag->getDescription();
                $content = preg_replace('#\n\s+#', ' ', $content);
            }

            $name = $tagName;
            $content = $this->cleanNewLines($content);
            $description = null;
            $link = null;
            $types = null;
            $variable = null;
            $refers = null;

            if ($tag instanceof DocBlock\Tags\InvalidTag && $tagName === 'since') {
                $content = $tagString;
                $description = $tagString;
            } elseif ($tag instanceof DocBlock\Tags\Since) {
                $version = $tag->getVersion();
                if (! empty($version)) {
                    $content = $version;
                }

                $description_raw = preg_replace('/[\n\r]+/', ' ', (string) $tag->getDescription());
                if (! empty($description_raw)) {
                    $description = $formatHtml($description_raw);
                }
            } elseif ($tag instanceof DocBlock\Tags\Deprecated) {
                $content = $tagString;
            } elseif ($tag instanceof DocBlock\Tags\Param) {
                $types = explode('|', (string) $tag->getType());
                $variable = '$' . $tag->getVariableName();
                $content = $formatHtml($content);
            } elseif ($tag instanceof DocBlock\Tags\Link) {
                $link = $tag->getLink();
                $content = sprintf(
                    '<a href="%s">%s</a>',
                    $link,
                    $link
                );
            } elseif ($tag instanceof DocBlock\Tags\See) {
                $refers = ltrim((string) $tag->getReference(), '\\');
                $content = $formatHtml($content);
            } elseif ($tag instanceof DocBlock\Tags\Return_) {
                $types = explode('|', (string) $tag->getType());
            } elseif ($tag instanceof DocBlock\Tags\Generic) {
                //
            } elseif ($tag instanceof DocBlock\Tags\InvalidTag && $tagName === 'see') {
                //
            } else {
                throw new Exception(
                    sprintf(
                        'Unknown tag type "%s" (@%s) for hook "%s" in file "%s".\n',
                        get_class($tag),
                        $tagName,
                        $this->currentHook,
                        $this->currentFile,
                    )
                );
            }

            $tags[] = array_filter(
                [
                    'name' => $name,
                    'content' => $content,
                    'types' => $types,
                    'variable' => $variable,
                    'link' => $link,
                    'refers' => $refers,
                    'description' => $description,
                ],
                fn ($value) => $value !== null
            );
        }

        return $tags;
    }

    private function parseCountArgs(
        FuncCall $node
    ): int {
        $countArgs = count($node->args);

        return $countArgs > 1 ? $countArgs - 1 : 0;
    }

    public function extractFilters(
        array $hooks
    ): array {
        return array_filter($hooks, fn ($hook) => in_array(HookTypeEnum::from($hook['type']), HookTypeEnum::FILTERS, true));
    }

    public function extractActions(
        array $hooks
    ): array {
        return array_filter($hooks, fn ($hook) => in_array(HookTypeEnum::from($hook['type']), HookTypeEnum::ACTIONS, true));
    }

    public function cleanNewLines(string $text): string
    {
        // Temporary replacement for code/pre blocks
        $newline_placeholder = '{{' . uniqid() . '}}';

        // Save hyphenation in code/pre
        $text = preg_replace_callback(
            '/<(pre|code)>(.+?)<\/(?:pre|code)>/s',
            fn ($matches) => '<' . $matches[1] . '>' .
                str_replace(["\n", "\r"], $newline_placeholder, $matches[2]) .
                '</' . $matches[1] . '>',
            $text
        );

        // Merge strings
        $text = preg_replace("/[\n\r](?!\s*[\n\r])/m", ' ', $text);

        // Restore hyphenation
        return str_replace($newline_placeholder, "\n", $text);
    }

    private function isFileIgnored(SplFileInfo $file): bool
    {
        $filename = $file->getFilename();

        foreach ($this->getIgnoreFiles() as $pattern) {
            if (str_contains($filename, $pattern)) {
                return true;
            }
        }

        return false;
    }
}
