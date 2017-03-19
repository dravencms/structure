<?php

namespace Dravencms\FrontModule\Components\Structure\Menu\Front;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Locale\CurrentLocale;
use Dravencms\Model\Structure\Repository\MenuRepository;
use Dravencms\Model\Structure\Repository\MenuTranslationRepository;

class Front extends BaseControl
{
    /** @var MenuRepository */
    private $menuRepository;

    /** @var MenuTranslationRepository */
    private $menuTranslationRepository;

    /** @var CurrentLocale */
    private $currentLocale;

    /** @var array */
    private $menuConfig;

    public function __construct(
        array $menuConfig,
        MenuRepository $menuRepository,
        MenuTranslationRepository $menuTranslationRepository,
        CurrentLocale $currentLocale
    )
    {
        parent::__construct();
        $this->menuConfig = $menuConfig;
        $this->menuRepository = $menuRepository;
        $this->menuTranslationRepository = $menuTranslationRepository;
        $this->currentLocale = $currentLocale;
    }

    public function render()
    {
        $template = $this->template;

        $template->htmlTree = $this->menuRepository->getTree($this->menuConfig, $this->currentLocale);

        $template->setFile(__DIR__ . '/front.latte');
        $template->render();
    }
}
