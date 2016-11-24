<?php

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */


namespace Dravencms\AdminModule\Components\Structure;

use Dravencms\Components\BaseControl;
use Dravencms\Components\BaseGridFactory;
use App\Model\Structure\Entities\Menu;
use App\Model\Structure\Repository\MenuRepository;
use Nette\Utils\Html;
use Kdyby\Doctrine\EntityManager;

class MenuGrid extends BaseControl
{
    /** @var BaseGridFactory */
    private $baseGridFactory;

    /** @var MenuRepository */
    private $menuRepository;

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
     * @param BaseGridFactory $baseGridFactory
     * @param EntityManager $entityManager
     */
    public function __construct(Menu $parentMenu = null, MenuRepository $menuRepository, BaseGridFactory $baseGridFactory, EntityManager $entityManager)
    {
        parent::__construct();

        $this->baseGridFactory = $baseGridFactory;
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
     * @return \Grido\Grid
     */
    protected function createComponentGrid($name)
    {
        $grid = $this->baseGridFactory->create($this, $name);

        $grid->setModel($this->menuRepository->getMenuItemsQueryBuilder($this->parentMenu, $this->isSystem));


        $grid->addColumnText('name', 'Name')
            ->setCustomRender(function ($row) use($grid) {
                /** @var $row Menu */
                if ($row->isHomePage()) {
                    $el = Html::el('span', $grid->getTranslator()->translate('Home page'));
                    $el->class = 'label label-info';
                    return $row->getName() . ' ' . $el;
                } else {
                    return $row->getName();
                }
            })
            ->setSortable()
            ->setFilterText()
            ->setSuggestion();

        $grid->addColumnBoolean('isActive', 'Active');
        $grid->addColumnBoolean('isHidden', 'Hidden');

        $grid->addColumnText('position', 'Position')
            ->setCustomRender(function($row){
                $elDown = Html::el('a');
                $elDown->class = "btn btn-xs";
                $elDown->href($this->link('down!', ['id' => $row->getId()]));
                $elDown->setHtml('<i class="fa fa-chevron-down" aria-hidden="true"></i>');

                $elUp = Html::el('a');
                $elUp->class = "btn btn-xs";
                $elUp->href($this->link('up!', ['id' => $row->getId()]));
                $elUp->setHtml('<i class="fa fa-chevron-up" aria-hidden="true"></i>');
                return $elUp.$elDown;
            });

        $header = $grid->getColumn('position')->headerPrototype;
        $header->style['width'] ='2%';
        $header->class[] = 'center';
        $grid->getColumn('position')->cellPrototype->class[] = 'center';

        $grid->addActionHref('submenu', 'Submenu items')
            ->setIcon('folder-open')
            ->setCustomHref(function ($item) {
                return $this->presenter->link('Structure:default', array('structureMenuId' => $item->id));
            });

        $grid->addActionHref('edit', 'Edit')
            ->setIcon('pencil');

        $grid->addActionHref('delete', 'Delete', 'delete!')
            ->setCustomHref(function($row){
                return $this->link('delete!', $row->getId());
            })
            ->setIcon('trash-o')
            ->setConfirm(function ($item) {
                return array("Are you sure you want to delete %s ?", $item->name);
            });

        $operations = array('delete' => 'Delete');
        $grid->setOperation($operations, [$this, 'gridOperationsHandler'])
            ->setConfirm('delete', 'Are you sure you want to delete %i items?');

        return $grid;
    }


    /**
     * @param $action
     * @param $ids
     */
    public function gridOperationsHandler($action, $ids)
    {
        switch ($action)
        {
            case 'delete':
                $this->handleDelete($ids);
                break;
        }
    }

    /**
     * @param $id
     * @throws \Exception
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