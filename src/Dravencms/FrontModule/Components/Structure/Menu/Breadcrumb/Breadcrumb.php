<?php

namespace Dravencms\FrontModule\Components\Structure\Menu\Breadcrumb;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Model\Structure\Repository\MenuRepository;

class Breadcrumb extends BaseControl
{

    /** @var MenuRepository */
    public $menuRepository;

    public function __construct(MenuRepository $menuRepository)
    {
        $this->menuRepository = $menuRepository;
    }

    public function render()
    {
        $template = $this->template;
        
        $thisPage = $this->menuRepository->getOneByPresenterAction(':'.$this->presenter->getName(), $this->presenter->getAction());
        $homePage = $this->menuRepository->getHomePage();

        $breadcrumbs = $this->menuRepository->buildParentTree($thisPage);

        $template->breadcrumbs = $breadcrumbs;
        $template->homePage = $homePage;
        $template->thisPage = $thisPage;

        $template->setFile(__DIR__ . '/breadcrumb.latte');
        $template->render();
    }
}
