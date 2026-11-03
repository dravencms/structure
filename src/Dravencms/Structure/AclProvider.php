<?php declare(strict_types=1);

namespace Dravencms\Structure;

use Dravencms\User\Acl\{AclPermission, AclProviderInterface};

final class AclProvider implements AclProviderInterface
{
    public function getPermissions(): iterable
    {
        yield new AclPermission('structure', 'edit', 'structure.acl.permissions.edit.label', resourceLabel: 'structure.acl.resource');
        yield new AclPermission('structure', 'delete', 'structure.acl.permissions.delete.label', resourceLabel: 'structure.acl.resource');
    }
}
