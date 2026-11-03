<?php declare(strict_types=1);

namespace Dravencms\Structure;

use Dravencms\Admin\Menu\{AdminMenuItem, AdminMenuProviderInterface};

final class AdminMenuProvider implements AdminMenuProviderInterface
{
    public function getItems(): iterable
    {
        yield new AdminMenuItem('structure.overview', 'structure.menu.structure', destination: ':Admin:Structure:Structure:default', icon: 'fa-code-fork', order: 100, resource: 'structure', privilege: 'edit');
    }
}
