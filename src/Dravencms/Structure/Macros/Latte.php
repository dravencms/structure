<?php declare(strict_types = 1);


namespace Dravencms\Structure\Macros;

use Latte\Compiler;
use Latte\MacroNode;
use Latte\PhpWriter;
use Latte\Macros\MacroSet;


/**
 * Class Latte
 * @package Salamek\Files\Macros
 */
class Latte extends MacroSet
{
    /**
     * @param Compiler $compiler
     * @return static
     */
    public static function install(Compiler $compiler)
    {
        $me = new static($compiler);

        /**
         * {img [namespace/]$name[, $size[, $flags]]}
         */
        $me->addMacro('cmsLink', [$me, 'macroCmsLink'], null, [$me, 'macroAttrCmsLink']);

        return $me;
    }


    public function macroCmsLink(MacroNode $node, PhpWriter $writer)
    {
        $node->modifiers = preg_replace('#\|safeurl\s*(?=\||\z)#i', '', $node->modifiers);
        return $writer->using($node, $this->getCompiler())
            ->write('echo %modify(call_user_func($this->filters->cmsLink, %node.word, %node.array?))');
    }

    public function macroAttrCmsLink(MacroNode $node, PhpWriter $writer)
    {
        $node->modifiers = preg_replace('#\|safeurl\s*(?=\||\z)#i', '', $node->modifiers);
        return $writer->using($node, $this->getCompiler())
            ->write('?> href="<?php echo  %modify(call_user_func($this->filters->cmsLink, %node.word, %node.array?))?>" <?php');
    }

}
