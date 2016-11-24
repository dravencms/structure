<?php

namespace Dravencms\FrontModule\Components\Structure\Search\Overview;

use Dravencms\Components\BaseControl;
use Dravencms\Model\Structure\Repository\SearchRepository;
use IPub\VisualPaginator\Components\Control;
use Salamek\Cms\ICmsActionOption;

class Overview extends BaseControl
{
    /** @var SearchRepository */
    private $searchRepository;

    /** @var ICmsActionOption */
    private $cmsActionOption;

    public function __construct(ICmsActionOption $cmsActionOption, SearchRepository $searchRepository)
    {
        parent::__construct();
        $this->cmsActionOption = $cmsActionOption;
        $this->searchRepository = $searchRepository;
    }


    public function render()
    {
        $template = $this->template;

        $q = $this->presenter->getParameter('q');

        $all = $this->searchRepository->search($q);
        $allCount = count($all);
        $visualPaginator = $this['visualPaginator'];

        $paginator = $visualPaginator->getPaginator();
        $paginator->itemsPerPage = 10;
        $paginator->itemCount = $allCount;

        $template->allCount = $allCount;
        $template->overview = $this->searchRepository->search($q, $paginator->itemsPerPage, $paginator->offset);
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
