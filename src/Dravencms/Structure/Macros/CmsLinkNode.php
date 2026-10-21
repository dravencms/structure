<?php declare(strict_types=1);

namespace Dravencms\Structure\Macros;

use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\Php\ModifierNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;

final class CmsLinkNode extends StatementNode
{
    public ExpressionNode $destination;
    public ArrayNode $arguments;
    public ModifierNode $modifier;
    public bool $attribute;

    public static function create(Tag $tag): ?self
    {
        $tag->expectArguments();
        $node = new self;
        $node->destination = $tag->parser->parseUnquotedStringOrExpression();
        if ($node->destination instanceof \Latte\Compiler\Nodes\Php\Expression\ConstantFetchNode) {
            $node->destination = new \Latte\Compiler\Nodes\Php\Scalar\StringNode((string) $node->destination->name);
        }
        $tag->parser->stream->tryConsume(',');
        $node->arguments = $tag->parser->parseArguments();
        $node->modifier = $tag->parser->parseModifier();
        $node->modifier->escape = true;
        $node->attribute = $tag->isNAttribute();
        if ($node->attribute) {
            $node->position = $tag->position;
            array_unshift($tag->htmlElement->attributes->children, $node);
            return null;
        }
        return $node;
    }

    public function print(PrintContext $context): string
    {
        if ($this->attribute) {
            $context->beginEscape()->enterHtmlAttribute();
        }
        $code = $context->format(
            'echo %modify(($this->filters->cmsLink)(%node, %node)) %line;',
            $this->modifier,
            $this->destination,
            $this->arguments,
            $this->position,
        );
        if ($this->attribute) {
            $context->restoreEscape();
            $code = 'echo \' href="\'; ' . $code . ' echo \'"\';';
        }
        return $code;
    }

    public function &getIterator(): \Generator
    {
        yield $this->destination;
        yield $this->arguments;
        yield $this->modifier;
    }
}
