<?php declare(strict_types = 1);

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\AdminModule\Components\Structure\MenuMoveForm;

use Dravencms\Components\BaseForm\BaseFormFactory;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Components\BaseForm\Form;
use Dravencms\Model\Structure\Repository\MenuTranslationRepository;
use Dravencms\Structure\MenuParameterSumGenerator;
use Dravencms\Model\Structure\Repository\MenuRepository;
use Dravencms\Structure\MenuSlugGenerator;
use Kdyby\Doctrine\EntityManager;
use Dravencms\Database\EntityManager;
use Nette\Security\User;
use Dravencms\Model\Structure\Entities\Menu;
use Salamek\Cms\Cms;

class MenuMoveForm extends BaseControl
{
    /** @var BaseFormFactory */
    private $baseFormFactory;

    /** @var MenuRepository */
    private $structureMenuRepository;

    /** @var EntityManager */
    private $entityManager;
    
    /** @var User */
    private $user;

    /** @var Menu */
    private $menu;

    /** @var null|callable */
    public $onSuccess = null;

    /**
     * MenuForm constructor.
     * @param BaseFormFactory $baseForm
     * @param MenuRepository $structureMenuRepository
     * @param MenuTranslationRepository $menuTranslationRepository
     * @param EntityManager $entityManager
     * @param Cms $cms
     * @param Menu|null $parentMenu
     * @param Menu|null $menu
     * @param MenuParameterSumGenerator $menuParameterSumGenerator
     * @param LocaleRepository $localeRepository
     * @param MenuSlugGenerator $menuSlugGenerator
     */
    public function __construct(
        BaseFormFactory $baseForm,
        MenuRepository $structureMenuRepository,
        EntityManager $entityManager,
        User $user,
        Menu $menu
    )
    {
        $this->user = $user;
        $this->baseFormFactory = $baseForm;
        $this->structureMenuRepository = $structureMenuRepository;
        $this->entityManager = $entityManager;
        $this->menu = $menu;

        $defaultValues = [];
        $defaultValues['menuId'] = $this->menu->getId();


        $this['form']->setDefaults($defaultValues);
    }

    /**
     * @return Form
     */
    protected function createComponentForm(): Form
    {
        $form = $this->baseFormFactory->create();

        $menuItems = [];
        foreach($this->structureMenuRepository->getAllByIsSystem(false) AS $menu)
        {
            $path = [];
            foreach($this->structureMenuRepository->getPath($menu) AS $prev)
            {
                $path[] = $prev->getIdentifier();
            }

            $menuItems[$menu->getId()] = implode(' -> ', $path);
        }

        $form->addMultiSelect('menuId', null, $menuItems)
            ->setRequired(true);

        $form->addSelect('newParentId', null, [null => '--root--'] + $menuItems);

        $form->addSubmit('send');

        $form->onValidate[] = [$this, 'editFormValidate'];
        $form->onSuccess[] = [$this, 'editFormSucceeded'];

        return $form;
    }

    /**
     * @param Form $form
     */
    public function editFormValidate(Form $form): void
    {
        $values = $form->getValues();

        $moveWhat = $this->structureMenuRepository->getById($values->menuId);
        $moveTo = $this->structureMenuRepository->getOneById($values->newParentId);

        if ($moveTo)
        {
            if (in_array($moveTo, $moveWhat))
            {
                $form->addError('Menu item cannot be transfered into its self');
            }

            foreach($moveWhat AS $item)
            {
                if (in_array($item, $this->structureMenuRepository->getPath($moveTo)))
                {
                    $form->addError('Cannot set parent as its own child');
                }
            }
        }
    }

    /**
     * @param Form $form
     * @throws \Exception
     */
    public function editFormSucceeded(Form $form): void
    {
        $values = $form->getValues();

        $moveWhat = $this->structureMenuRepository->getById($values->menuId);
        $moveTo = $this->structureMenuRepository->getOneById($values->newParentId);

        foreach($moveWhat AS $item)
        {
            if ($moveTo)
            {
                $this->structureMenuRepository->persistAsLastChildOf($item, $moveTo);
            }
            else
            {
                $this->structureMenuRepository->persistAsNextSiblingOf($item, $this->structureMenuRepository->getLastMenuItem());
            }
        }

        $this->entityManager->flush();

        $this->onSuccess($moveTo);
    }

    public function render(): void
    {
        $template = $this->template;
        $template->setFile(__DIR__ . '/MenuMoveForm.latte');
        $template->render();
    }


}