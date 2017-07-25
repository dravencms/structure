<?php

namespace Dravencms\FrontModule\Components\Structure\Menu\Breadcrumb;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Locale\CurrentLocale;
use Dravencms\Model\Structure\Repository\MenuRepository;

class Breadcrumb extends BaseControl
{

    /** @var MenuRepository */
    private $menuRepository;

    /** @var CurrentLocale */
    private $currentLocale;

    public function __construct(
        MenuRepository $menuRepository,
        CurrentLocale $currentLocale
    )
    {
        parent::__construct();
        $this->menuRepository = $menuRepository;
        $this->currentLocale = $currentLocale;
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
        $template->currentLocale = $this->currentLocale;

        $template->setFile(__DIR__ . '/breadcrumb.latte');
        $template->render();
    }
}
