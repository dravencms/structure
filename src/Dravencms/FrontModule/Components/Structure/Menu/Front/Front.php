<?php declare(strict_types = 1);

namespace Dravencms\FrontModule\Components\Structure\Menu\Front;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Locale\CurrentLocaleResolver;
use Dravencms\Model\Structure\Repository\MenuRepository;
use Dravencms\Model\Structure\Repository\MenuTranslationRepository;

class Front extends BaseControl
{
    /** @var MenuRepository */
    private $menuRepository;

    /** @var MenuTranslationRepository */
    private $menuTranslationRepository;

    /** @var ILocale */
    private $currentLocale;

    /** @var array */
    private $menuConfig;

    /**
     * Front constructor.
     * @param array $menuConfig
     * @param MenuRepository $menuRepository
     * @param MenuTranslationRepository $menuTranslationRepository
     * @param CurrentLocaleResolver $currentLocaleResolver
     */
    public function __construct(
        array $menuConfig,
        MenuRepository $menuRepository,
        MenuTranslationRepository $menuTranslationRepository,
        CurrentLocaleResolver $currentLocaleResolver
    )
    {
        $this->menuConfig = $menuConfig;
        $this->menuRepository = $menuRepository;
        $this->menuTranslationRepository = $menuTranslationRepository;
        $this->currentLocale = $currentLocaleResolver->getCurrentLocale();
    }

    public function render(): void
    {
        $template = $this->template;

        $template->htmlTree = $this->menuRepository->getTree($this->menuConfig, $this->currentLocale);

        $template->setFile(__DIR__ . '/front.latte');
        $template->render();
    }
}
