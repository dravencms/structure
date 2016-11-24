<?php

namespace Dravencms\FrontModule\Components\Structure\Menu\Special;

use Dravencms\Components\BaseControl;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Dravencms\Model\Structure\Repository\MenuRepository;

class Special extends BaseControl
{
    /** @var MenuRepository */
    private $menuRepository;

    /** @var LocaleRepository */
    private $localeRepository;

    /**
     * Special constructor.
     * @param MenuRepository $menuRepository
     * @param LocaleRepository $localeRepository
     */
    public function __construct(MenuRepository $menuRepository, LocaleRepository $localeRepository)
    {
        $this->menuRepository = $menuRepository;
        $this->localeRepository = $localeRepository;
    }

    /**
     * @param array $showItems
     */
    public function render(array $showItems)
    {
        $template = $this->template;
        $template->menuItems = $this->menuRepository->getById($showItems, $this->localeRepository->getCurrentLocale());
        $template->setFile(__DIR__ . '/special.latte');
        $template->render();
    }

    /**
     * @param array $showItems
     */
    public function renderFooter(array $showItems)
    {
        $template = $this->template;

        $template->menuItems = $this->menuRepository->getById($showItems, $this->localeRepository->getCurrentLocale());

        $template->setFile(__DIR__ . '/special-footer.latte');
        $template->render();
    }
}
