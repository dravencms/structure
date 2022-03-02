<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Structure\Fixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Dravencms\Model\Admin\Entities\Menu;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

class AdminMenuFixtures extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $child = new Menu('Web structure and content', ':Admin:Structure:Structure', 'fa-code-fork', $this->getReference('user-acl-operation-structure-edit'));
        $manager->persist($child);

        $child = new Menu('Site items', null, 'fa-cubes', $this->getReference('user-acl-operation-structure-edit'));
        $manager->persist($child);

        $manager->flush();
    }
    
    /**
     * Get the order of this fixture
     * @return array
     */
    public function getDependencies(): array
    {
        return ['Dravencms\Model\Structure\Fixtures\AclOperationFixtures'];
    }
}