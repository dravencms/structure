<?php

namespace Dravencms\FrontModule\Components\Structure\Search\Overview;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Model\Structure\Repository\MenuRepository;
use Dravencms\Model\Structure\Repository\SearchRepository;
use IPub\VisualPaginator\Components\Control;
use Salamek\Cms\ICmsActionOption;

class Overview extends BaseControl
{
    /** @var MenuRepository */
    private $menuRepository;

    /** @var ICmsActionOption */
    private $cmsActionOption;

    public function __construct(ICmsActionOption $cmsActionOption, MenuRepository $menuRepository)
    {
        parent::__construct();
        $this->cmsActionOption = $cmsActionOption;
        $this->menuRepository = $menuRepository;
    }


    public function render()
    {
        $template = $this->template;

        $q = $this->presenter->getParameter('q');

        $all = $this->menuRepository->search($q);
        $allCount = count($all);
        $visualPaginator = $this['visualPaginator'];

        $paginator = $visualPaginator->getPaginator();
        $paginator->itemsPerPage = 10;
        $paginator->itemCount = $allCount;

        $template->allCount = $allCount;
        $template->overview = $this->menuRepository->search($q, $paginator->itemsPerPage, $paginator->offset);
        $template->setFile(__DIR__.'/overview.latte');
        $template->render();
    }

    /**
     * @return Control
     */
    protected function createComponentVisualPaginator()
    {
        // Init visual paginator
        $control = new Control();
        $control->setTemplateFile('bootstrap.latte');

        $control->onShowPage[] = (function ($component, $page) {
            if ($this->presenter->isAjax()){
                $this->redrawControl('overview');
            }
        });

        return $control;
    }

}
