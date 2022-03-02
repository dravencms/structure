<?php declare(strict_types = 1);

namespace Dravencms\FrontModule\Components\Structure\Search\Bar;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Components\BaseForm\BaseFormFactory;
use Dravencms\Components\BaseForm\Form;
use Dravencms\Structure\Structure;

class Bar extends BaseControl
{
    /** @var BaseFormFactory */
    private $baseFormFactory;

    /** @var Structure */
    private $structure;

    public function __construct(BaseFormFactory $baseFormFactory, Structure $structure)
    {
        $this->baseFormFactory = $baseFormFactory;
        $this->structure = $structure;
    }

    public function render(array $config = []): void
    {
        $template = $this->template;

        $template->formClass = (array_key_exists('formClass', $config) ? $config['formClass'] : 'pull-right search-form');

        $template->setFile(__DIR__ . '/bar.latte');
        $template->render();
    }

    public function createComponentForm(): Form
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
    public function onSuccessForm(Form $form): void
    {
        $values = $form->getValues();
        $menu = $this->structure->findComponentActionPresenter('Structure\\Search\\Overview');
        $this->presenter->redirect($menu->getPresenter().':'.$menu->getAction(), ['q' => $values->q]);
    }
}
