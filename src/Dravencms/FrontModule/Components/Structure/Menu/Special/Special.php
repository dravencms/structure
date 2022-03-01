<?php declare(strict_types = 1);

namespace Dravencms\FrontModule\Components\Structure\Menu\Special;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Locale\CurrentLocaleResolver;
use Dravencms\Model\Structure\Repository\MenuRepository;

class Special extends BaseControl
{
    /** @var MenuRepository */
    private $menuRepository;

    /** @var ILocale */
    private $currentLocale;

    /**
     * Special constructor.
     * @param MenuRepository $menuRepository
     * @param CurrentLocaleResolver $currentLocaleResolver
     */
    public function __construct(
        MenuRepository $menuRepository,
        CurrentLocaleResolver $currentLocaleResolver
    )
    {
        $this->menuRepository = $menuRepository;
        $this->currentLocale = $currentLocaleResolver->getCurrentLocale();
    }

    /**
     * @param array $showItems
     */
    public function render(array $showItems): void
    {
        $template = $this->template;
        $template->menuItems = $this->menuRepository->getById($showItems, $this->currentLocale);
        $template->setFile(__DIR__ . '/special.latte');
        $template->render();
    }

    /**
     * @param array $showItems
     */
    public function renderFooter(array $showItems): void
    {
        $template = $this->template;

        $template->menuItems = $this->menuRepository->getById($showItems, $this->currentLocale);

        $template->setFile(__DIR__ . '/special-footer.latte');
        $template->render();
    }
}
