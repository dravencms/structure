<?php

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\AdminModule\Components\Structure\MenuForm;

use Dravencms\Components\BaseForm\BaseFormFactory;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Dravencms\Model\Structure\Entities\MenuTranslation;
use Dravencms\Model\Structure\Repository\MenuTranslationRepository;
use Dravencms\Structure\MenuParameterSumGenerator;
use Dravencms\Model\Structure\Repository\MenuRepository;
use Dravencms\Structure\MenuSlugGenerator;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Dravencms\Model\Structure\Entities\Menu;
use Salamek\Cms\Cms;

class MenuForm extends Control
{
    /** @var BaseFormFactory */
    private $baseFormFactory;

    /** @var MenuRepository */
    private $structureMenuRepository;

    /** @var MenuTranslationRepository */
    private $menuTranslationRepository;

    /** @var EntityManager */
    private $entityManager;

    /** @var Cms */
    private $cms;

    /** @var null|Menu */
    private $menu = null;

    /** @var null|Menu */
    private $parentMenu = null;

    /** @var MenuParameterSumGenerator */
    private $menuParameterSumGenerator;

    /** @var LocaleRepository */
    private $localeRepository;

    /** @var MenuSlugGenerator */
    private $menuSlugGenerator;

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
        MenuTranslationRepository $menuTranslationRepository,
        EntityManager $entityManager,
        Cms $cms,
        Menu $parentMenu = null,
        Menu $menu = null,
        MenuParameterSumGenerator $menuParameterSumGenerator,
        LocaleRepository $localeRepository,
        MenuSlugGenerator $menuSlugGenerator
    )
    {
        parent::__construct();
        $this->baseFormFactory = $baseForm;
        $this->structureMenuRepository = $structureMenuRepository;
        $this->menuTranslationRepository = $menuTranslationRepository;
        $this->entityManager = $entityManager;
        $this->cms = $cms;
        $this->menu = $menu;
        $this->parentMenu = $parentMenu;
        $this->menuParameterSumGenerator = $menuParameterSumGenerator;
        $this->localeRepository = $localeRepository;
        $this->menuSlugGenerator = $menuSlugGenerator;

        $defaultValues = [];
        if ($this->menu)
        {
            $defaultValues['metaRobots'] = $this->menu->getMetaRobots();
            $defaultValues['sitemapPriority'] = $this->menu->getSitemapPriority();
            $defaultValues['layoutName'] = $this->menu->getLayoutName();

            $defaultValues['isHidden'] = $this->menu->isHidden();
            $defaultValues['isActive'] = $this->menu->isActive();
            $defaultValues['isShowH1'] = $this->menu->isShowH1();
            $defaultValues['isRegularExpression'] = $this->menu->isRegularExpression();
            $defaultValues['isRegularExpressionMatchArguments'] = $this->menu->isRegularExpressionMatchArguments();
            $defaultValues['isSitemap'] = $this->menu->isSitemap();
            $defaultValues['isHomePage'] = $this->menu->isHomePage();
            $defaultValues['presenter'] = $this->menu->getPresenter();
            $defaultValues['action'] = $this->menu->getAction();
            $defaultValues['latteTemplate'] = $this->menu->getLatteTemplate();
            $defaultValues['identifier'] = $this->menu->getIdentifier();

            foreach ($this->menu->getTranslations() AS $translation)
            {
                $defaultValues[$translation->getLocale()->getLanguageCode()]['name'] = $translation->getName();
                $defaultValues[$translation->getLocale()->getLanguageCode()]['slug'] = $translation->getSlug();
                $defaultValues[$translation->getLocale()->getLanguageCode()]['h1'] = $translation->getH1();
                $defaultValues[$translation->getLocale()->getLanguageCode()]['title'] = $translation->getTitle();
                $defaultValues[$translation->getLocale()->getLanguageCode()]['metaDescription'] = $translation->getMetaDescription();
                $defaultValues[$translation->getLocale()->getLanguageCode()]['metaKeywords'] = $translation->getMetaKeywords();
            }
        }
        else{
            $defaultValues['metaRobots'] = 'index, follow';
            $defaultValues['isActive'] = true;
            $defaultValues['sitemapPriority'] = '0.5';
            $defaultValues['isShowH1'] = true;
            $defaultValues['isSitemap'] = true;
            $defaultValues['layoutName'] = $this->cms->getDefaultLayout();
        }

        $this['form']->setDefaults($defaultValues);
    }

    /**
     * @return Form
     */
    protected function createComponentForm()
    {
        $form = $this->baseFormFactory->create();

        foreach ($this->localeRepository->getActive() AS $activeLocale)
        {
            $container = $form->addContainer($activeLocale->getLanguageCode());

            $container->addText('name')
                ->setRequired('Please enter menu item name.')
                ->addRule(Form::MAX_LENGTH, 'Name is too long.', 200);

            $container->addText('h1')
                ->addRule(Form::MAX_LENGTH, 'Main title is too long.', 200)
                ->setRequired('Please enter H1.');

            $container->addText('title')
                ->addRule(Form::MAX_LENGTH, 'Title is too long.', 200)
                ->setRequired('Please enter title.');

            $container->addText('metaDescription')
                ->setRequired(false)
                ->addRule(Form::MAX_LENGTH, 'SEO - Description is too long.', 250);

            $container->addText('metaKeywords')
                ->setRequired(false)
                ->addRule(Form::MAX_LENGTH, 'SEO - Keyword is too long.', 200);
        }

        $form->addText('metaRobots')
            ->setRequired(false)
            ->addRule(Form::MAX_LENGTH, 'SEO - Robots is too long.', 200);

        $sitemapPriorities = [];
        for ($pri = 0.0; $pri <= 1.0; $pri += 0.1) {
            $str = (string)round($pri, 2);
            $sitemapPriorities[$str] = $str;
        }

        $form->addSelect('sitemapPriority', null, $sitemapPriorities);

        $form->addSelect('layoutName', null, $this->cms->detectLayouts());

        $form->addTextarea('latteTemplate');
        $form->addText('identifier')
            ->setRequired('Please fill in an unique identifier');

        $form->addCheckbox('isHidden');
        $form->addCheckbox('isActive');
        $form->addCheckbox('isShowH1');
        $form->addCheckbox('isRegularExpression');
        $form->addCheckbox('isRegularExpressionMatchArguments');
        $form->addCheckbox('isSitemap');
        $form->addCheckbox('isHomePage');

        $form->addSubmit('send');

        $form->onValidate[] = [$this, 'editFormValidate'];
        $form->onSuccess[] = [$this, 'editFormSucceeded'];

        return $form;
    }

    /**
     * @param Form $form
     */
    public function editFormValidate(Form $form)
    {
        $values = $form->getValues();

        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            if (!$this->menuTranslationRepository->isNameFree($values->{$activeLocale->getLanguageCode()}->name, $activeLocale, $this->parentMenu, $this->menu))
            {
                $form->addError('Menu item with this name already exists!');
            }

            if (!isset($values->basic)) {
                $cnt = explode(',', $values->{$activeLocale->getLanguageCode()}->metaKeywords);
                if (count($cnt) > 20) {
                    $form->addError('Seo keywords is too long, only 10 words are allowed');
                }
            }
        }

        if (!$this->structureMenuRepository->isIdentifierFree($values->identifier, $this->menu))
        {
            $form->addError('Menu item with this identifier already exists!');
        }
    }

    /**
     * @param Form $form
     * @throws \Exception
     */
    public function editFormSucceeded(Form $form)
    {
        $values = $form->getValues();

        if ($values->isHomePage) {
            $this->structureMenuRepository->resetIsHomePage();
        }

        if ($this->menu)
        {
            $menu = $this->menu;

            $menu->setIdentifier($values->identifier);
            $menu->setMetaRobots($values->metaRobots);
            $menu->setIsActive($values->isActive);
            $menu->setIsHidden($values->isHidden);
            if ($values->isHomePage)
            {
                $menu->setIsHomePage(true);
            }
            $menu->setSitemapPriority($values->sitemapPriority);
            $menu->setIsSitemap($values->isSitemap);
            $menu->setIsShowH1($values->isShowH1);
            $menu->setIsRegularExpression($values->isRegularExpression);
            $menu->setIsRegularExpressionMatchArguments($values->isRegularExpressionMatchArguments);
            $menu->setLayoutName($values->layoutName);

            $this->entityManager->persist($menu);
        }
        else
        {
            $menu = new Menu(
                function($parameters){
                    return $this->menuParameterSumGenerator->hash($parameters);
                },
                $values->identifier,
                $values->metaRobots,
                $values->isActive,
                $values->isHidden,
                $values->isHomePage,
                $values->sitemapPriority,
                $values->isSitemap,
                $values->isShowH1,
                null,
                null,
                false,
                [],
                $values->isRegularExpression,
                $values->isRegularExpressionMatchArguments,
                $values->layoutName
            );

            if ($this->parentMenu)
            {
                $this->structureMenuRepository->persistAsLastChildOf($menu, $this->parentMenu);
            }
            else
            {
                $lastMenuItem = $this->structureMenuRepository->getLastMenuItem();
                $this->structureMenuRepository->persistAsNextSiblingOf($menu, $lastMenuItem);
            }
        }

        $this->entityManager->flush();

        $slugGenerator = function($formTranslation){
            /** @var MenuTranslation $formTranslation */
            return $this->menuSlugGenerator->slugify($formTranslation);
        };

        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            if ($formTranslation = $this->menuTranslationRepository->getTranslation($menu, $activeLocale))
            {
                $formTranslation->setName($values->{$activeLocale->getLanguageCode()}->name);
                $formTranslation->setMetaDescription($values->{$activeLocale->getLanguageCode()}->metaDescription);
                $formTranslation->setMetaKeywords($values->{$activeLocale->getLanguageCode()}->metaKeywords);
                $formTranslation->setTitle($values->{$activeLocale->getLanguageCode()}->title);
                $formTranslation->setH1($values->{$activeLocale->getLanguageCode()}->h1);

                $formTranslation->generateSlug($slugGenerator);
            }
            else
            {
                $formTranslation = new MenuTranslation(
                    $menu,
                    $activeLocale,
                    $values->{$activeLocale->getLanguageCode()}->name,
                    $values->{$activeLocale->getLanguageCode()}->metaDescription,
                    $values->{$activeLocale->getLanguageCode()}->metaKeywords,
                    $values->{$activeLocale->getLanguageCode()}->title,
                    $values->{$activeLocale->getLanguageCode()}->h1,
                    $slugGenerator
                );
            }

            $this->entityManager->persist($formTranslation);
        }

        $menu->setLatteTemplate($values->latteTemplate);

        $this->entityManager->persist($menu);


        $this->entityManager->flush();

        $this->onSuccess($menu);
    }

    public function render()
    {
        $template = $this->template;
        $template->activeLocales = $this->localeRepository->getActive();
        $template->setFile(__DIR__ . '/MenuForm.latte');
        $template->render();
    }


}