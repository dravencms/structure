<?php

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */


namespace Dravencms\AdminModule\Components\Structure\MenuGrid;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Components\BaseGrid\BaseGridFactory;
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
     * @return \Grido\Grid
     */
    protected function createComponentGrid($name)
    {
        $grid = $this->baseGridFactory->create($this, $name);

        $grid->setModel($this->menuRepository->getMenuQueryBuilder($this->parentMenu, $this->isSystem));


        $grid->addColumnText('identifier', 'Identifier')
            ->setCustomRender(function ($row) use($grid) {
                /** @var $row Menu */
                if ($row->isHomePage()) {
                    $el = Html::el('span', $grid->getTranslator()->translate('Home page'));
                    $el->class = 'label label-info';
                    return $row->getIdentifier() . ' ' . $el;
                } else {
                    return $row->getIdentifier();
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
                return $this->presenter->link('Structure:default', ['structureMenuId' => $item->getId()]);
            });

        $grid->addActionHref('edit', 'Edit')
            ->setCustomHref(function($row){
                return $this->presenter->link('Structure:edit', ['id' => $row->getId()]);
            })
            ->setIcon('pencil');

        $grid->addActionHref('delete', 'Delete', 'delete!')
            ->setCustomHref(function($row){
                return $this->link('delete!', $row->getId());
            })
            ->setIcon('trash-o')
            ->setConfirm(function ($item) {
                return ["Are you sure you want to delete %s ?", $item->getIdentifier()];
            });

        $operations = ['delete' => 'Delete'];
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