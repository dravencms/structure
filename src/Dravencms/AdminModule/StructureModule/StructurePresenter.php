<?php

namespace Dravencms\AdminModule\StructureModule;

use Dravencms\AdminModule\SecuredPresenter;
use Dravencms\AdminModule\Components\Structure\MenuForm\MenuFormFactory;
use Dravencms\AdminModule\Components\Structure\MenuGrid\MenuGridFactory;
use Dravencms\Model\Structure\Entities\Menu;
use Dravencms\Model\Structure\Entities\MenuTranslation;
use Dravencms\Model\Structure\Repository\MenuRepository;
use Dravencms\Model\Structure\Repository\MenuTranslationRepository;
use Kdyby\Doctrine\EntityManager;
use Nette;
use Salamek\Cms\TCms;

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

    /** @var Menu */
    private $structureMenu = null;

    /** @var null|Menu */
    private $menuEdit = null;


    /**
     * @param $structureMenuId
     * @throws Nette\Application\BadRequestException
     */
    public function actionDefault($structureMenuId)
    {
        $this->template->h1 = $this->translator->translate('Web structure and content');
        if ($structureMenuId) {
            /** @var MenuTranslation $structureMenu */
            $structureMenu = $this->structureMenuRepository->getOneById($structureMenuId);
            if (!$structureMenu) {
                $this->error();
            }

            $this->structureMenu = $structureMenu;
            $this->template->menu = $structureMenu;
            $this->template->mapping = $this->cms->getLayoutMapping($structureMenu->getLayoutName());

            $this->template->h1 .= ' - ' . $this->structureMenu->getIdentifier();
        }

        $this->template->structureMenuId = $structureMenuId;
    }

    /**
     * @param null $id
     * @param null $structureMenuId
     * @throws Nette\Application\BadRequestException
     */
    public function actionEdit($id = null, $structureMenuId = null)
    {
        $this->template->h1 = $this->translator->translate('Web structure and content');

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
            $this->template->h1 .= ' - ' . $menu->getIdentifier();

        } else {
            $this->template->h1 .= ' - ' . $this->translator->translate('New menu item');
        }
    }

    public function createComponentStructureMenuForm()
    {
        $component = $this->structureMenuFormFactory->create($this->structureMenu, $this->menuEdit);
        $component->onSuccess[] = function ($menu) {
            $cmsMenu = new \Dravencms\Structure\Bridge\CmsMenu\Menu($menu);
            $this->cms->generateMenuPage($cmsMenu);

            /** @var Menu $structureMenu */
            if ($this->menuEdit) {
                $this->flashMessage('Changes has been saved.', 'alert-success');
                $this->redirect('Structure:edit', ['id' => $menu->getId()]);
            } else {
                $this->flashMessage('New menu item has been saved.', 'alert-success');
                $this->redirect('Structure:', ($this->structureMenu ? $this->structureMenu->getId() : null));
            }
        };

        return $component;
    }

    /**
     * @return \Dravencms\AdminModule\Components\Structure\MenuGrid\MenuGrid
     */
    protected function createComponentMenuGrid()
    {
        $control = $this->structureMenuGridFactory->create($this->structureMenu);
        $control->setIsSystem(false);
        $control->onDelete[] = function () {
            $this->flashMessage('Item has been deleted.', 'alert-success');
            $this->redirect('this', ['structureMenuId' => ($this->structureMenu ? $this->structureMenu->getId() : null)]);
        };
        return $control;
    }

    /**
     * @return \Dravencms\AdminModule\Components\Structure\MenuGrid\MenuGrid
     */
    protected function createComponentMenuSystemGrid()
    {
        $control = $this->structureMenuGridFactory->create($this->structureMenu);
        $control->setIsSystem(true);
        $control->onDelete[] = function () {
            $this->flashMessage('Item has been deleted.', 'alert-success');
            $this->redirect('this', ['structureMenuId' => ($this->structureMenu ? $this->structureMenu->getId() : null)]);
        };
        return $control;
    }

    /**
     * @param $structureMenuId
     */
    public function handleBlocksJson($structureMenuId)
    {
        $structureMenu = $this->structureMenuRepository->getOneById($structureMenuId);
        $this->payload->structure = (object)/*We work with object in JS not arrays*/
        $this->cms->parsePageLayout($structureMenu->getLatteTemplate());


        $componentArray = [];
        foreach ($this->cms->getTree() AS $moduleName => $components) {
            $moduleComponents = [];
            foreach ($components AS $componentName => $component) {
                $moduleComponents[$moduleName.'\\'.$componentName] = $componentName;
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
        $this->cms->getActionArray($componentClass);
        $this->sendPayload();
    }

    /**
     * @param $structureMenuId
     * @param array $structureTree
     */
    public function handleStructureSave($structureMenuId, array $structureTree)
    {
        $menu = $this->structureMenuRepository->getOneById($structureMenuId);

        $cmsMenu = new \Dravencms\Structure\Bridge\CmsMenu\Menu($menu);

        $this->cms->saveStructureTree($cmsMenu, $structureTree);
        $this->payload->structureTree = $structureTree;
        $this->sendPayload();
    }
}
