<?php

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
use Nette\Utils\Html;
use Kdyby\Doctrine\EntityManager;

class MenuGrid extends BaseControl
{
    /** @var BaseGridFactory */
    private $baseGridFactory;

    /** @var MenuRepository */
    private $menuRepository;

    private $menuTranslationRepository;

    /** @var EntityManager */
    private $entityManager;

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
        Menu $parentMenu = null,
        MenuRepository $menuRepository,
        MenuTranslationRepository $menuTranslationRepository,
        BaseGridFactory $baseGridFactory,
        EntityManager $entityManager
    )
    {
        parent::__construct();

        $this->baseGridFactory = $baseGridFactory;
        $this->menuTranslationRepository = $menuTranslationRepository;
        $this->parentMenu = $parentMenu;
        $this->menuRepository = $menuRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @param bool $isSystem
     */
    public function setIsSystem($isSystem = true)
    {
        $this->isSystem = $isSystem;
    }

    /**
     * @param $name
     * @return Grid
     * @throws \Ublaboo\DataGrid\Exception\DataGridColumnNotFoundException
     */
    protected function createComponentGrid($name)
    {
        /** @var Grid $grid */
        $grid = $this->baseGridFactory->create($this, $name);

        $grid->setDataSource($this->menuRepository->getMenuQueryBuilder($this->parentMenu, $this->isSystem));


        $grid->addColumnText('identifier', 'Identifier')
            ->setTemplate(__DIR__.'/identifier.latte')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnBoolean('isActive', 'Active');
        $grid->addColumnBoolean('isHidden', 'Hidden');

        $grid->addColumnPosition('position', 'Position', 'up!', 'down!');

        $grid->addAction('submenu', 'Submenu items', 'default', ['structureMenuId' => 'id'])
            ->setIcon('folder-open')
            ->setTitle('Submenu items')
            ->setClass('btn btn-xs btn-default');

        if ($this->presenter->isAllowed('structure', 'edit')) {
            $grid->addAction('edit', '')
                ->setIcon('pencil')
                ->setTitle('Edit')
                ->setClass('btn btn-xs btn-primary');
        }

        if ($this->presenter->isAllowed('structure', 'delete'))
        {
            $grid->addAction('delete', '', 'delete!')
                ->setIcon('trash')
                ->setTitle('Smazat')
                ->setClass('btn btn-xs btn-danger ajax')
                ->setConfirm('Do you really want to delete row %s?', 'identifier');

            $grid->addGroupAction('Smazat')->onSelect[] = [$this, 'gridGroupActionDelete'];
        }

        $grid->addExportCsvFiltered('Csv export (filtered)', 'acl_resource_filtered.csv')
            ->setTitle('Csv export (filtered)');

        $grid->addExportCsv('Csv export', 'acl_resource_all.csv')
            ->setTitle('Csv export');

        return $grid;
    }

    /**
     * @param array $ids
     */
    public function gridGroupActionDelete(array $ids)
    {
        $this->handleDelete($ids);
    }


    /**
     * @param $id
     * @throws \Exception
     * @isAllowed(structure, delete)
     */
    public function handleDelete($id)
    {
        $aclOperations = $this->menuRepository->getById($id);
        foreach ($aclOperations AS $aclOperation)
        {
            $this->entityManager->remove($aclOperation);
        }

        $this->entityManager->flush();

        $this->onDelete($this->parentMenu);
    }

    public function handleUp($id)
    {
        $menuItem = $this->menuRepository->getOneById($id);
        $this->menuRepository->moveUp($menuItem, 1);
    }

    public function handleDown($id)
    {
        $menuItem = $this->menuRepository->getOneById($id);
        $this->menuRepository->moveDown($menuItem, 1);
    }

    public function render()
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/MenuGrid.latte');
        $template->render();
    }
}