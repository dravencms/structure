<?php

namespace Dravencms\Structure\Script;

use Dravencms\Model\Admin\Entities\Menu;
use Dravencms\Model\Admin\Repository\MenuRepository;
use Dravencms\Model\User\Entities\AclOperation;
use Dravencms\Model\User\Entities\AclResource;
use Dravencms\Model\User\Repository\AclOperationRepository;
use Dravencms\Model\User\Repository\AclResourceRepository;
use Dravencms\Packager\IPackage;
use Dravencms\Packager\IScript;
use Kdyby\Doctrine\EntityManager;

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */
class PostInstall implements IScript
{
    /** @var MenuRepository */
    private $menuRepository;

    /** @var EntityManager */
    private $entityManager;

    /** @var AclOperationRepository */
    private $aclOperationRepository;

    /** @var AclResourceRepository */
    private $aclResourceRepository;

    /**
     * PostInstall constructor.
     * @param MenuRepository $menuRepository
     * @param EntityManager $entityManager
     * @param AclResourceRepository $aclResourceRepository
     * @param AclOperationRepository $aclOperationRepository
     */
    public function __construct(MenuRepository $menuRepository, EntityManager $entityManager, AclResourceRepository $aclResourceRepository, AclOperationRepository $aclOperationRepository)
    {
        $this->menuRepository = $menuRepository;
        $this->entityManager = $entityManager;
        $this->aclResourceRepository = $aclResourceRepository;
        $this->aclOperationRepository = $aclOperationRepository;
    }

    /**
     * @param IPackage $package
     * @throws \Exception
     */
    public function run(IPackage $package)
    {
        if (!$aclResource = $this->aclResourceRepository->getOneByName('structure')) {
            $aclResource = new AclResource('structure', 'Structure');

            $this->entityManager->persist($aclResource);
        }

        if (!$aclOperationEdit = $this->aclOperationRepository->getOneByName('edit')) {
            $aclOperationEdit = new AclOperation($aclResource, 'edit', 'Allows editation of Structure');
            $this->entityManager->persist($aclOperationEdit);
        }

        if (!$aclOperationDelete = $this->aclOperationRepository->getOneByName('delete')) {
            $aclOperationDelete = new AclOperation($aclResource, 'delete', 'Allows deletion of Structure');
            $this->entityManager->persist($aclOperationDelete);
        }

        if (!$this->menuRepository->getOneByPresenter(':Admin:Structure:Structure')) {
            $adminMenuRoot = new Menu('Web structure and content', ':Admin:Structure:Structure', 'fa-code-fork', $aclOperationEdit);
            $this->entityManager->persist($adminMenuRoot);
        }

        $this->entityManager->flush();
    }
}