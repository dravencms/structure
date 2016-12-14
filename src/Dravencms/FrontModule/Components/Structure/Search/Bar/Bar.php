<?php

namespace Dravencms\FrontModule\Components\Structure\Search\Bar;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Components\BaseForm\BaseFormFactory;
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

    public function render(array $config = [])
    {
        $template = $this->template;

        $template->formClass = (array_key_exists('formClass', $config) ? $config['formClass'] : 'pull-right search-form');

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
