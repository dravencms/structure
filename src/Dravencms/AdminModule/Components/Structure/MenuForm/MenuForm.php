<?php declare(strict_types = 1);

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\AdminModule\Components\Structure\MenuForm;

use Dravencms\Components\BaseForm\BaseFormFactory;
use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Dravencms\Model\Structure\Entities\MenuTranslation;
use Dravencms\Model\Structure\Repository\MenuTranslationRepository;
use Dravencms\Structure\MenuParameterSumGenerator;
use Dravencms\Model\Structure\Repository\MenuRepository;
use Dravencms\Structure\MenuSlugGenerator;
use Dravencms\Database\EntityManager;
use Nette\Security\User;
use Dravencms\Components\BaseForm\Form;
use Dravencms\Model\Structure\Entities\Menu;
use Dravencms\Structure\Structure;

class MenuForm extends BaseControl
{
    /** @var BaseFormFactory */
    private $baseFormFactory;

    /** @var MenuRepository */
    private $structureMenuRepository;

    /** @var MenuTranslationRepository */
    private $menuTranslationRepository;

    /** @var EntityManager */
    private $entityManager;

    /** @var User */
    private $user;
    
    /** @var Structure */
    private $structure;

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
     * @param MenuTranslationRepository $menuTranslationRepository
     * @param EntityManager $entityManager
     * @param User $user
     * @param Cms $cms
     * @param MenuParameterSumGenerator $menuParameterSumGenerator
     * @param LocaleRepository $localeRepository
     * @param Menu $parentMenu
     * @param Menu $menu
     */
    public function __construct(
        BaseFormFactory $baseForm,
        MenuRepository $structureMenuRepository,
        MenuTranslationRepository $menuTranslationRepository,
        EntityManager $entityManager,
        User $user,
        Structure $structure,
        MenuParameterSumGenerator $menuParameterSumGenerator,
        LocaleRepository $localeRepository,
        Menu $parentMenu = null,
        Menu $menu = null
    )
    {
        $this->baseFormFactory = $baseForm;
        $this->structureMenuRepository = $structureMenuRepository;
        $this->menuTranslationRepository = $menuTranslationRepository;
        $this->entityManager = $entityManager;
        $this->structure = $structure;
        $this->menu = $menu;
        $this->user = $user;
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
            $defaultValues['latteTemplate'] = $this->menu->getLatteTemplate();
            $defaultValues['identifier'] = $this->menu->getIdentifier();
            $defaultValues['target'] = $this->menu->getTarget();
            $defaultValues['isAutogenerateSlug'] = $this->menu->isAutogenerateSlug();

            foreach ($this->menu->getTranslations() AS $translation)
            {
                $defaultValues[$translation->getLocale()->getLanguageCode()]['name'] = $translation->getName();
                $defaultValues[$translation->getLocale()->getLanguageCode()]['slug'] = $translation->getSlug();
                $defaultValues[$translation->getLocale()->getLanguageCode()]['h1'] = $translation->getH1();
                $defaultValues[$translation->getLocale()->getLanguageCode()]['title'] = $translation->getTitle();
                $defaultValues[$translation->getLocale()->getLanguageCode()]['metaDescription'] = $translation->getMetaDescription();
                $defaultValues[$translation->getLocale()->getLanguageCode()]['metaKeywords'] = $translation->getMetaKeywords();
                $defaultValues[$translation->getLocale()->getLanguageCode()]['customUrl'] = $translation->getCustomUrl();
            }
        }
        else{
            $defaultValues['metaRobots'] = 'index, follow';
            $defaultValues['isActive'] = true;
            $defaultValues['sitemapPriority'] = '0.5';
            $defaultValues['isShowH1'] = true;
            $defaultValues['isSitemap'] = true;
            $defaultValues['isAutogenerateSlug'] = true;
            $defaultValues['layoutName'] = $this->structure->getDefaultLayout();
        }

        $this['form']->setDefaults($defaultValues);
    }

    /**
     * @return Form
     */
    protected function createComponentForm(): Form
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

            $container->addText('slug')
                ->setRequired(false);

            $container->addText('customUrl')
                ->setRequired(false)
                ->addRule(Form::URL, 'Custom URL have incorrect format.');
        }

        $form->addText('metaRobots')
            ->setRequired(false)
            ->addRule(Form::MAX_LENGTH, 'SEO - Robots is too long.', 200);

        $sitemapPriorities = [];
        for ($pri = 0.0; $pri <= 1.0; $pri += 0.1) {
            $str = (string)round($pri, 2);
            $sitemapPriorities[$str] = $str;
        }

        $form->addSelect('sitemapPriority', null, $sitemapPriorities)
            ->setRequired('Please select valid sitemapPriority');

        $form->addSelect('layoutName', null, $this->structure->detectLayouts())
            ->setRequired('Please select valid layout');

        $form->addSelect('target', null, [
            null => 'Default',
            Menu::TARGET_BLANK => 'Open new window or tab',
            Menu::TARGET_SELF => 'Open in same frame',
            Menu::TARGET_PARENT => 'Open in parent frame',
            Menu::TARGET_TOP => 'Open in full body of the window'
        ]);

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
        $form->addCheckbox('isAutogenerateSlug');

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
    public function editFormSucceeded(Form $form): void
    {
        $values = $form->getValues();

        if ($values->isHomePage) {
            $this->structureMenuRepository->resetIsHomePage();
        }

        $target = ($values->target ? $values->target : null);

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
            $menu->setTarget($target);
            $menu->setIsAutogenerateSlug($values->isAutogenerateSlug);

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
                $values->layoutName,
                false,
                $target,
                $values->isAutogenerateSlug
            );

            if ($this->parentMenu)
            {
                $this->structureMenuRepository->persistAsLastChildOf($menu, $this->parentMenu);
            }
            else
            {
                $lastMenuItem = $this->structureMenuRepository->getLastMenuItem();
                if ($lastMenuItem) {
                    $this->structureMenuRepository->persistAsNextSiblingOf($menu, $lastMenuItem);
                } else {
                    $this->entityManager->persist($menu);
                }
            }
        }

        $this->entityManager->flush();

        foreach ($this->localeRepository->getActive() AS $activeLocale) {
            $slug = ($values->{$activeLocale->getLanguageCode()}->slug ? $values->{$activeLocale->getLanguageCode()}->slug : null);
            $customUrl = ($values->{$activeLocale->getLanguageCode()}->customUrl ? $values->{$activeLocale->getLanguageCode()}->customUrl : null);
            if ($formTranslation = $this->menuTranslationRepository->getTranslation($menu, $activeLocale))
            {
                $formTranslation->setName($values->{$activeLocale->getLanguageCode()}->name);
                $formTranslation->setMetaDescription($values->{$activeLocale->getLanguageCode()}->metaDescription);
                $formTranslation->setMetaKeywords($values->{$activeLocale->getLanguageCode()}->metaKeywords);
                $formTranslation->setTitle($values->{$activeLocale->getLanguageCode()}->title);
                $formTranslation->setH1($values->{$activeLocale->getLanguageCode()}->h1);
                $formTranslation->setCustomUrl($customUrl);
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
                    null,
                    $customUrl
                );
            }

            $uniqueSlug = $this->menuTranslationRepository->slugify($formTranslation, $slug);
            $formTranslation->setSlug($uniqueSlug);

            $this->entityManager->persist($formTranslation);
        }

        $menu->setLatteTemplate($values->latteTemplate);

        $this->entityManager->persist($menu);


        $this->entityManager->flush();

        $this->onSuccess($menu);
    }

    public function render(): void
    {
        $template = $this->template;
        $template->activeLocales = $this->localeRepository->getActive();
        $template->setFile(__DIR__ . '/MenuForm.latte');
        $template->render();
    }


}
