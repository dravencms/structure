<?php

namespace Dravencms\FrontModule\Components\Structure\Menu\Frontmap;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Model\Structure\Repository\MenuRepository;

class Frontmap extends BaseControl
{
    /** @var MenuRepository @inject */
    public $menuRepository;

    public function render()
    {
        $template = $this->template;


        $template->htmlTree = $this->menuRepository->getTree([]);

        $template->setFile(__DIR__ . '/frontMap.latte');
        $template->render();
    }
}
