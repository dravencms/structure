<?php

namespace Dravencms\FrontModule\Components\Structure\Menu\Breadcrumb;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Locale\CurrentLocaleResolver;
use Dravencms\Model\Structure\Repository\MenuRepository;

class Breadcrumb extends BaseControl
{

    /** @var MenuRepository */
    private $menuRepository;

    /** @var ILocale */
    private $currentLocale;

    /**
     * Breadcrumb constructor.
     * @param MenuRepository $menuRepository
     * @param CurrentLocaleResolver $currentLocaleResolver
     */
    public function __construct(
        MenuRepository $menuRepository,
        CurrentLocaleResolver $currentLocaleResolver
    )
    {
        parent::__construct();
        $this->menuRepository = $menuRepository;
        $this->currentLocale = $currentLocaleResolver->getCurrentLocale();
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
