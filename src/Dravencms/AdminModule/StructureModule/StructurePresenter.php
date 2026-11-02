<?php declare(strict_types = 1);

namespace Dravencms\AdminModule\StructureModule;

use Dravencms\AdminModule\Components\Structure\MenuMoveForm\MenuMoveFormFactory;
use Dravencms\AdminModule\SecuredPresenter;
use Dravencms\AdminModule\Components\Structure\MenuForm\MenuFormFactory;
use Dravencms\AdminModule\Components\Structure\MenuForm\MenuForm;
use Dravencms\AdminModule\Components\Structure\MenuGrid\MenuGridFactory;
use Dravencms\AdminModule\Components\Structure\MenuGrid\MenuGrid;
use Dravencms\Model\Structure\Entities\Menu;
use Dravencms\Model\Structure\Entities\MenuTranslation;
use Dravencms\Model\Structure\Repository\MenuRepository;
use Dravencms\Model\Structure\Repository\MenuTranslationRepository;
use Dravencms\Database\EntityManager;
use Nette;
use Dravencms\Structure\TCms;

/**
 * Homepage presenter.
 */
class StructurePresenter extends SecuredPresenter
{
    use TCms;
    
    /** @var MenuRepository @inject */
    public $structureMenuRepository;

    /** @var MenuTranslationRepository @inject */
    public $structureMenuTranslationRepository;

    /** @var EntityManager @inject */
    public $entityManager;

    /** @var MenuGridFactory @inject */
    public $structureMenuGridFactory;

    /** @var MenuFormFactory @inject */
    public $structureMenuFormFactory;

    /** @var MenuMoveFormFactory @inject */
    public $structureMenuMoveFormFactory;

    /** @var Menu */
    private $structureMenu = null;

    /** @var null|Menu */
    private $menuEdit = null;

    /**
     * @param $structureMenuId
     * @throws Nette\Application\BadRequestException
     */
    public function actionDefault(?int $structureMenuId = null): void
    {
        $this->template->h1 = $this->translator->translate('structure.admin.title.structure');
        if ($structureMenuId) {
            /** @var MenuTranslation $structureMenu */
            $structureMenu = $this->structureMenuRepository->getOneById($structureMenuId);
            if (!$structureMenu) {
                $this->error();
            }

            $this->structureMenu = $structureMenu;
            $this->template->menu = $structureMenu;
            $this->template->mapping = $this->structure->getLayoutMapping($structureMenu->getLayoutName());

            $this->template->h1 = $this->translator->translate('structure.admin.title.structureFor', ['identifier' => $this->structureMenu->getIdentifier()]);
        }

        $this->template->structureMenuId = $structureMenuId;
    }

    /**
     * @param null $id
     * @param null $structureMenuId
     * @throws Nette\Application\BadRequestException
     */
    public function actionEdit(?int $id = null, ?int $structureMenuId = null): void
    {
        $this->template->h1 = $this->translator->translate('structure.admin.title.structure');

        if ($structureMenuId)
        {
            $this->structureMenu = $this->structureMenuRepository->getOneById($structureMenuId);
        }

        if ($id) {
            /** @var Menu $menu */
            $menu = $this->structureMenuRepository->getOneById($id);
            if (!$menu) {
                $this->error();
            } 

            $this->menuEdit = $menu;
            $this->template->h1 = $this->translator->translate('structure.admin.title.structureFor', ['identifier' => $menu->getIdentifier()]);

        } else {
            $this->template->h1 = $this->translator->translate('structure.admin.title.newHeading');
        }
    }

    public function actionMove(int $id): void
    {
        /** @var Menu $menu */
        $menu = $this->structureMenuRepository->getOneById($id);
        if (!$menu) {
            $this->error();
        }
        $this->template->h1 = $this->translator->translate('structure.admin.title.moveMenuItem', ['identifier' => $menu->getIdentifier()]);
        $this->menuEdit = $menu;
    }

    /**
     * @return \Dravencms\AdminModule\Components\Structure\MenuMoveForm\MenuMoveForm
     */
    public function createComponentStructureMenuMoveForm()
    {
        $component = $this->structureMenuMoveFormFactory->create($this->menuEdit);
        $component->onSuccess[] = function ($parentMenu) {
            $this->flashMessage($this->translator->translate('structure.admin.flash.saved'), 'alert-success');
            $this->redirect('Structure:', ['structureMenuId' => ($parentMenu ? $parentMenu->getId(): null)]);
        };

        return $component;
    }

    /**
     * @return MenuForm
     */
    public function createComponentStructureMenuForm(): MenuForm
    {
        $component = $this->structureMenuFormFactory->create($this->structureMenu, $this->menuEdit);
        $component->onSuccess[] = function ($menu) {
            $this->structure->generateMenuPage($menu);

            /** @var Menu $structureMenu */
            if ($this->menuEdit) {
                $this->flashMessage($this->translator->translate('structure.admin.flash.saved'), 'alert-success');
                $this->redirect('Structure:edit', ['id' => $menu->getId()]);
            } else {
                $this->flashMessage($this->translator->translate('structure.admin.flash.created'), 'alert-success');
                $this->redirect('Structure:', ($this->structureMenu ? $this->structureMenu->getId() : null));
            }
        };

        return $component;
    }

    /**
     * @return MenuGrid
     */
    protected function createComponentMenuGrid(): MenuGrid
    {
        $control = $this->structureMenuGridFactory->create($this->structureMenu);
        $control->setIsSystem(false);
        $control->onDelete[] = function () {
            $this->flashMessage($this->translator->translate('structure.admin.flash.deleted'), 'alert-success');
            $this->redirect('this', ['structureMenuId' => ($this->structureMenu ? $this->structureMenu->getId() : null)]);
        };
        return $control;
    }

    /**
     * @return MenuGrid
     */
    protected function createComponentMenuSystemGrid(): MenuGrid
    {
        $control = $this->structureMenuGridFactory->create($this->structureMenu);
        $control->setIsSystem(true);
        $control->onDelete[] = function () {
            $this->flashMessage($this->translator->translate('structure.admin.flash.deleted'), 'alert-success');
            $this->redirect('this', ['structureMenuId' => ($this->structureMenu ? $this->structureMenu->getId() : null)]);
        };
        return $control;
    }

    /**
     * @param $structureMenuId
     */
    public function handleBlocksJson(int $structureMenuId): void
    {
        $structureMenu = $this->structureMenuRepository->getOneById($structureMenuId);
        $this->payload->structure = (object)/*We work with object in JS not arrays*/
        $this->structure->parsePageLayout($structureMenu->getLatteTemplate());


        $componentArray = [];
        foreach ($this->structure->getTree() AS $moduleName => $components) {
            $moduleComponents = [];
            foreach ($components AS $componentName => $component) {
                $moduleComponents[$moduleName.'\\'.$componentName] = $moduleName.' -> '.$componentName;
            }
            $componentArray[$moduleName] = $moduleComponents;
        }

        $this->payload->components = (object)$componentArray;
        $this->sendPayload();
    }

    /**
     * @param $componentClass
     */
    public function handleComponentJson($componentClass)
    {
        $this->payload->actions = (object)/*We work with object in JS not arrays*/
        $this->structure->getActionArray($componentClass);
        $this->sendPayload();
    }

    /**
     * @param $structureMenuId
     * @param array $structureTree
     */
    public function handleStructureSave(int $structureMenuId, array $structureTree): void
    {
        $menu = $this->structureMenuRepository->getOneById($structureMenuId);

        $this->structure->saveStructureTree($menu, $structureTree);
        $this->payload->structureTree = $structureTree;
        $this->sendPayload();
    }
}
