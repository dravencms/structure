<?php

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\AdminModule\Components\Structure\MenuForm;

use Dravencms\Components\BaseForm\BaseFormFactory;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Dravencms\Structure\MenuParameterSumGenerator;
use Dravencms\Model\Structure\Repository\MenuRepository;
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

    /** @var null|callable */
    public $onSuccess = null;

    /**
     * MenuForm constructor.
     * @param BaseFormFactory $baseForm
     * @param MenuRepository $structureMenuRepository
     * @param EntityManager $entityManager
     * @param Cms $cms
     * @param Menu|null $parentMenu
     * @param Menu|null $menu
     * @param MenuParameterSumGenerator $menuParameterSumGenerator
     * @param LocaleRepository $localeRepository
     */
    public function __construct(BaseFormFactory $baseForm, MenuRepository $structureMenuRepository, EntityManager $entityManager, Cms $cms, Menu $parentMenu = null, Menu $menu = null, MenuParameterSumGenerator $menuParameterSumGenerator, LocaleRepository $localeRepository)
    {
        parent::__construct();
        $this->baseFormFactory = $baseForm;
        $this->structureMenuRepository = $structureMenuRepository;
        $this->entityManager = $entityManager;
        $this->cms = $cms;
        $this->menu = $menu;
        $this->parentMenu = $parentMenu;
        $this->menuParameterSumGenerator = $menuParameterSumGenerator;
        $this->localeRepository = $localeRepository;

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

            $repository = $this->entityManager->getRepository('Gedmo\Translatable\Entity\Translation');
            $defaultValues += $repository->findTranslations($this->menu);

            $defaultLocale = $this->localeRepository->getDefault();
            if ($defaultLocale)
            {
                $defaultValues[$defaultLocale->getLanguageCode()]['name'] = $this->menu->getName();
                $defaultValues[$defaultLocale->getLanguageCode()]['slug'] = $this->menu->getSlug();
                $defaultValues[$defaultLocale->getLanguageCode()]['h1'] = $this->menu->getH1();
                $defaultValues[$defaultLocale->getLanguageCode()]['title'] = $this->menu->getTitle();
                $defaultValues[$defaultLocale->getLanguageCode()]['metaDescription'] = $this->menu->getMetaDescription();
                $defaultValues[$defaultLocale->getLanguageCode()]['metaKeywords'] = $this->menu->getMetaKeywords();
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
            if (!$this->structureMenuRepository->isNameFree($values->{$activeLocale->getLanguageCode()}->name, $activeLocale, $this->parentMenu, $this->menu))
            {
                $form->addError('Menu item with this name already exists!');
            }

            if (!isset($values->basic)) {
                $cnt = explode(',', $values->{$activeLocale->getLanguageCode()}->metaKeywords);
                if (count($cnt) > 10) {
                    $form->addError('Seo description is too long, only 10 words are allowed');
                }
            }
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
            /*$menu->setName($values->name);
            $menu->setMetaDescription($values->metaDescription);
            $menu->setMetaKeywords($values->metaKeywords);*/
            $menu->setMetaRobots($values->metaRobots);
            /*$menu->setTitle($values->title);
            $menu->setH1($values->h1);
            */
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
            $defaultLocale = $this->localeRepository->getDefault();

            $menu = new Menu(
                function($parameters){
                    return $this->menuParameterSumGenerator->hash($parameters);
                },
                $values->{$defaultLocale->getLanguageCode()}->name,
                $values->{$defaultLocale->getLanguageCode()}->metaDescription,
                $values->{$defaultLocale->getLanguageCode()}->metaKeywords,
                $values->metaRobots,
                $values->{$defaultLocale->getLanguageCode()}->title,
                $values->{$defaultLocale->getLanguageCode()}->h1,
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
                $this->entityManager->persist($menu);
            }
        }

        $repository = $this->entityManager->getRepository('Gedmo\\Translatable\\Entity\\Translation');

        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            $repository->translate($menu, 'name', $activeLocale->getLanguageCode(), $values->{$activeLocale->getLanguageCode()}->name)
                ->translate($menu, 'title', $activeLocale->getLanguageCode(), $values->{$activeLocale->getLanguageCode()}->title)
                ->translate($menu, 'h1', $activeLocale->getLanguageCode(), $values->{$activeLocale->getLanguageCode()}->h1)
                //->translate($menu, 'slug', $activeLocale->getLanguageCode(), ($menu->getParent() ? $this->structureMenuRepository->getOneById($menu->getParent()->getId(), $activeLocale)->getSlug().'/' : '').Strings::webalize($values->{$activeLocale->getLanguageCode()}->name))
                ->translate($menu, 'metaDescription', $activeLocale->getLanguageCode(), $values->{$activeLocale->getLanguageCode()}->metaDescription)
                ->translate($menu, 'metaKeywords', $activeLocale->getLanguageCode(), $values->{$activeLocale->getLanguageCode()}->metaKeywords);
        }

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