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

    public function render(array $config = [])
    {
        $template = $this->template;

        $template->showCarrot = (array_key_exists('showCarrot', $config) ? $config['showCarrot'] : true);
        $template->showYouAreHere = (array_key_exists('showYouAreHere', $config) ? $config['showYouAreHere'] : true);
        $template->listClass = (array_key_exists('listClass', $config) ? $config['listClass'] : 'breadcrumbs-list');
        $template->activeClass = (array_key_exists('activeClass', $config) ? $config['activeClass'] : 'current');

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
