<?php declare(strict_types = 1);

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */


namespace Dravencms\AdminModule\Components\Structure\MenuGrid;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Components\BaseGrid\BaseGridFactory;
use Dravencms\Components\BaseGrid\Grid;
use Dravencms\Model\Structure\Entities\Menu;
use Dravencms\Model\Structure\Repository\MenuRepository;
use Dravencms\Model\Structure\Repository\MenuTranslationRepository;
use Dravencms\Database\EntityManager;
use Contributte\Datagrid\Column\Action\Confirmation\StringConfirmation;
use Nette\Security\User;

class MenuGrid extends BaseControl
{
    /** @var BaseGridFactory */
    private $baseGridFactory;

    /** @var MenuRepository */
    private $menuRepository;

    private $menuTranslationRepository;

    /** @var EntityManager */
    private $entityManager;
    
    /** @var User */
    private $user;

    /** @var Menu */
    private $parentMenu = null;

    /** @var array */
    public $onDelete = [];

    /** @var bool */
    public $isSystem = false;

    /**
     * MenuGrid constructor.
     * @param Menu|null $parentMenu
     * @param MenuRepository $menuRepository
     * @param MenuTranslationRepository $menuTranslationRepository
     * @param BaseGridFactory $baseGridFactory
     * @param EntityManager $entityManager
     */
    public function __construct(
        ?Menu $parentMenu,
        MenuRepository $menuRepository,
        MenuTranslationRepository $menuTranslationRepository,
        BaseGridFactory $baseGridFactory,
        User $user,
        EntityManager $entityManager
    )
    {
        $this->user = $user;
        $this->baseGridFactory = $baseGridFactory;
        $this->menuTranslationRepository = $menuTranslationRepository;
        $this->parentMenu = $parentMenu;
        $this->menuRepository = $menuRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @param bool $isSystem
     */
    public function setIsSystem(bool $isSystem = true): void
    {
        $this->isSystem = $isSystem;
    }

    /**
     * @param $name
     * @return Grid
     * @throws \Contributte\Datagrid\Exception\DataGridColumnNotFoundException
     */
    protected function createComponentGrid(string $name): Grid
    {
        /** @var Grid $grid */
        $grid = $this->baseGridFactory->create($this, $name);
        $grid->setWritePermission('structure', 'edit');

        $grid->setDataSource($this->menuRepository->getMenuQueryBuilder($this->parentMenu, $this->isSystem));

        $grid->addColumnText('identifier', 'structure.admin.field.identifier')
            ->setTemplate(__DIR__.'/identifier.latte')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnBoolean('isActive', 'structure.admin.field.active');
        $grid->addColumnBoolean('isHidden', 'structure.admin.field.hidden');

        $grid->addColumnPosition('position', 'structure.admin.field.position', 'up!', 'down!');

        $grid->addAction('submenu', 'structure.admin.action.submenu', 'default', ['structureMenuId' => 'id'])
            ->setIcon('folder-open')
            ->setTitle('structure.admin.action.submenu')
            ->setClass('btn btn-sm btn-outline-secondary');

        if ($this->user->isAllowed('structure', 'edit')) {

            $grid->addAction('move', '')
                ->setIcon('share')
                ->setTitle('structure.admin.action.move')
                ->setClass('btn btn-sm btn-outline-secondary');

            $grid->addAction('edit', '')
                ->setIcon('pencil')
                ->setTitle('structure.admin.action.edit')
                ->setClass('btn btn-sm btn-primary');
        }

        if ($this->user->isAllowed('structure', 'delete'))
        {
            $grid->addAction('delete', '', 'delete!')
                ->setIcon('trash')
                ->setTitle('structure.admin.action.delete')
                ->setClass('btn btn-sm btn-danger')
                ->setConfirmation(new StringConfirmation('structure.admin.confirm.delete', 'identifier'));

            $grid->addGroupAction('structure.admin.action.delete')->onSelect[] = [$this, 'gridGroupActionDelete'];
        }

        $grid->addExportCsvFiltered('structure.admin.action.exportFiltered', 'acl_resource_filtered.csv')
            ->setTitle('structure.admin.action.exportFiltered');

        $grid->addExportCsv('structure.admin.action.exportAll', 'acl_resource_all.csv')
            ->setTitle('structure.admin.action.exportAll');

        return $grid;
    }

    /**
     * @param array $ids
     */
    public function gridGroupActionDelete(array $ids): void
    {
        $this->handleDelete($ids);
    }


    /**
     * @param $id
     * @throws \Exception
     */
    #[\Dravencms\User\Attributes\IsAllowed('structure', 'delete')]
    #[\Dravencms\Security\Mutation]
    public function handleDelete($id): void
    {
        $this->requirePermission('structure', 'delete');
        $aclOperations = $this->menuRepository->getById($id);
        foreach ($aclOperations AS $aclOperation)
        {
            $this->entityManager->remove($aclOperation);
        }

        $this->entityManager->flush();

        $this->onDelete($this->parentMenu);
    }

    #[\Dravencms\User\Attributes\IsAllowed('structure', 'edit')]
    #[\Dravencms\Security\Mutation]
    public function handleUp(int $id): void
    {
        $this->requirePermission('structure', 'edit');
        $menuItem = $this->menuRepository->getOneById($id);
        $this->menuRepository->moveUp($menuItem, 1);
    }

    #[\Dravencms\User\Attributes\IsAllowed('structure', 'edit')]
    #[\Dravencms\Security\Mutation]
    public function handleDown(int $id): void
    {
        $this->requirePermission('structure', 'edit');
        $menuItem = $this->menuRepository->getOneById($id);
        $this->menuRepository->moveDown($menuItem, 1);
    }

    public function render(): void
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/MenuGrid.latte');
        $template->render();
    }
}
