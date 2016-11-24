<?php

namespace Dravencms\FrontModule\Components\Structure\Search;

use Dravencms\Components\BaseControl;
use Dravencms\Components\BaseFormFactory;
use Nette\Application\UI\Form;
use Salamek\Cms\Cms;

class Bar extends BaseControl
{
    /** @var BaseFormFactory */
    private $baseFormFactory;

    /** @var Cms */
    private $cms;

    public function __construct(BaseFormFactory $baseFormFactory, Cms $cms)
    {
        parent::__construct();
        $this->baseFormFactory = $baseFormFactory;
        $this->cms = $cms;
    }

    public function render()
    {
        $template = $this->template;

        $template->setFile(__DIR__ . '/bar.latte');
        $template->render();
    }

    public function createComponentForm()
    {
        $form = $this->baseFormFactory->create();

        $form->addText('q')
            ->setRequired('Query is required');

        $form->addSubmit('send');

        $form->onSuccess[] = [$this, 'onSuccessForm'];

        return $form;
    }

    /**
     * @param Form $form
     */
    public function onSuccessForm(Form $form)
    {
        $values = $form->getValues();
        $menu = $this->cms->findComponentActionPresenter('Structure\\Search\\Overview');
        $this->presenter->redirect($menu->getPresenter().':'.$menu->getAction(), ['q' => $values->q]);
    }
}
