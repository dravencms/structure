<?php declare(strict_types=1);

namespace Dravencms\Structure\Macros;

use Latte\Extension;

/** CMS links for Latte 3, including the n:cmsLink attribute. */
final class Latte extends Extension
{
    public function getTags(): array
    {
        return [
            'cmsLink' => CmsLinkNode::create(...),
            'n:cmsLink' => CmsLinkNode::create(...),
        ];
    }
}
