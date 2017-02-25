<?php

namespace Dravencms\FrontModule\Components\Structure\Menu\Special;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Locale\CurrentLocale;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Dravencms\Model\Structure\Repository\MenuRepository;

class Special extends BaseControl
{
    /** @var MenuRepository */
    private $menuRepository;

    /** @var CurrentLocale */
    private $currentLocale;

    /**
     * Special constructor.
     * @param MenuRepository $menuRepository
     * @param CurrentLocale $currentLocale
     */
    public function __construct(
        MenuRepository $menuRepository,
        CurrentLocale $currentLocale
    )
    {
        $this->menuRepository = $menuRepository;
        $this->currentLocale = $currentLocale;
    }

    /**
     * @param array $showItems
     */
    public function render(array $showItems)
    {
        $template = $this->template;
        $template->menuItems = $this->menuRepository->getById($showItems, $this->currentLocale);
        $template->setFile(__DIR__ . '/special.latte');
        $template->render();
    }

    /**
     * @param array $showItems
     */
    public function renderFooter(array $showItems)
    {
        $template = $this->template;

        $template->menuItems = $this->menuRepository->getById($showItems, $this->currentLocale);

        $template->setFile(__DIR__ . '/special-footer.latte');
        $template->render();
    }
}
