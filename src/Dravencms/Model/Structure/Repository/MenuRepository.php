<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Structure\Repository;

use Dravencms\Locale\TLocalizedRepository;
use Dravencms\Model\Structure\Entities\Menu;
use Dravencms\Structure\MenuParameterSumGenerator;
use Doctrine\ORM\Query;
use Gedmo\Translatable\TranslatableListener;
use Kdyby\Doctrine\EntityManager;
use Nette;
use Salamek\Cms\Models\ILocale;
use Salamek\Cms\Models\IMenu;
use Salamek\Cms\Models\IMenuRepository;

class MenuRepository implements IMenuRepository
{
    use TLocalizedRepository;

    /** @var \Kdyby\Doctrine\EntityRepository */
    private $menuRepository;

    /** @var EntityManager */
    private $entityManager;

    /** @var MenuParameterSumGenerator */
    private $menuParameterSumGenerator;

    /** @var bool */
    private $cacheInitialized = false;

    //!FIXME MOVE ALL THIS CACHING STUFF INTO CMS EXTENSION!!!

    /** @var Menu[] */
    private $cacheRegexFalse = [];

    /** @var Menu[] */
    private $cacheRegexTrue = [];

    /** @var Menu[] */
    private $cachePresenter = [];

    /** @var Menu[] */
    private $cacheFactory = [];

    /** @var Menu */
    private $cacheHomePage;

    /**
     * MenuRepository constructor.
     * @param EntityManager $entityManager
     * @param MenuParameterSumGenerator $menuParameterSumGenerator
     */
    public function __construct(EntityManager $entityManager, MenuParameterSumGenerator $menuParameterSumGenerator)
    {
        $this->entityManager = $entityManager;
        $this->menuParameterSumGenerator = $menuParameterSumGenerator;
        $this->menuRepository = $entityManager->getRepository(Menu::class);
    }

    /**
     * @param bool $force
     */
    private function buildCache($force = false)
    {
        if (!$this->cacheInitialized || $force) {
            $result = $this->menuRepository->findAll();

            /** @var Menu $item */
            foreach ($result AS $item) {
                if ($item->isRegularExpression()) {
                    $this->cacheRegexTrue[] = $item;
                } else {
                    $this->cacheRegexFalse[$item->getSlug()] = $item;
                }

                $this->cachePresenter[$item->getPresenter() . ':' . $item->getAction()] = $item;
            }

            $this->cacheInitialized = true;
        }
    }


    /**
     * @return \Kdyby\Doctrine\EntityRepository
     */
    public function getMenuRepository()
    {
        return $this->menuRepository;
    }

    /**
     * @param IMenu $menu
     * @return IMenu[]
     */
    private function buildParentTreeResolver(IMenu $menu)
    {
        $breadcrumb = [];

        $breadcrumb[] = $menu;

        if ($menu->getParent()) {
            foreach ($this->buildParentTreeResolver($menu->getParent()) AS $sub) {
                $breadcrumb[] = $sub;
            }
        }
        return $breadcrumb;
    }

    /**
     * @param IMenu $menu
     * @return IMenu[]
     */
    public function buildParentTree(IMenu $menu)
    {
        return array_reverse($this->buildParentTreeResolver($menu));
    }

    /**
     * @param $options
     * @param ILocale $locale
     * @return mixed
     */
    public function getTree($options, ILocale $locale = null)
    {
        $query = $this->menuRepository
            ->createQueryBuilder('node')
            ->select('node')
            ->orderBy('node.root, node.lft', 'ASC')
            ->where('node.isHidden = :isHidden')
            ->andWhere('node.isActive = :isActive')
            ->setParameters(
                [
                    'isHidden' => false,
                    'isActive' => true
                ]
            )
            ->getQuery();

        return $this->menuRepository->buildTree(($locale ? $this->getTranslatedArrayResult($query, $locale) : $query->getArrayResult()), $options);
    }

    /**
     * @param $id
     * @param ILocale|null $locale
     * @return array
     */
    public function getById($id, ILocale $locale = null)
    {
        $query = $this->menuRepository->createQueryBuilder('m')
            ->select('m')
            ->where('m.id IN (:id)')
            ->setParameter('id', $id)
            ->getQuery();

        if ($locale) {
            return $this->getTranslatedResult($query, $locale);
        }

        return $query->getResult();
    }

    /**
     * @param $id
     * @param ILocale|null $locale
     * @return mixed|null|object
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getOneById($id, ILocale $locale = null)
    {
        $query = $this->menuRepository->createQueryBuilder('m')
            ->select('m')
            ->where('m.id = :id')
            ->setParameter('id', $id)
            ->getQuery();

        if ($locale) {
            $this->addTranslationWalkerToQuery($query, $locale);
        }

        return $query->getOneOrNullResult();
    }

    /**
     * @param $name
     * @return mixed|null|Menu
     */
    public function getByName($name)
    {
        return $this->menuRepository->findOneBy(['name' => $name]);
    }

    /**
     * @return Menu[]
     */
    public function getAll()
    {
        return $this->menuRepository->findBy(['isActive' => true]);
    }

    /**
     * @param $name
     * @param ILocale $locale
     * @param IMenu|null $parentMenu
     * @param IMenu|null $ignoreMenu
     * @return bool
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isNameFree($name, ILocale $locale, IMenu $parentMenu = null, IMenu $ignoreMenu = null)
    {
        $qb = $this->menuRepository->createQueryBuilder('m')
            ->select('m')
            ->where('m.name = :name')
            ->setParameters([
                'name' => $name,
            ]);

        if ($parentMenu) {
            $qb->andWhere('m.parent = :parent')
                ->setParameter('parent', $parentMenu);
        } else {
            $qb->andWhere('m.parent IS NULL');
        }

        if ($ignoreMenu) {
            $qb->andWhere('m != :ignoreMenu')
                ->setParameter('ignoreMenu', $ignoreMenu);
        }

        $query = $qb->getQuery();

        $query->setHint(TranslatableListener::HINT_TRANSLATABLE_LOCALE, $locale->getLanguageCode());

        return (is_null($query->getOneOrNullResult()));
    }

    /**
     * @throws \Exception
     */
    public function resetIsHomePage()
    {
        /** @var Menu $homePageMenu */
        foreach ($this->menuRepository->findBy(['isHomePage' => true]) AS $homePageMenu) {
            $homePageMenu->setIsHomePage(false);

            $this->entityManager->persist($homePageMenu);
        }

        $this->entityManager->flush();
    }

    /**
     * @param Menu|null $parentMenu
     * @param bool $isSystem
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getMenuItemsQueryBuilder(Menu $parentMenu = null, $isSystem = false)
    {
        $qb = $this->menuRepository->createQueryBuilder('m')
            ->select('m')
            ->where('m.isSystem = :isSystem')
            ->setParameter('isSystem', $isSystem);

        if ($parentMenu) {
            $qb->andWhere('m.parent = :parent')
                ->setParameter('parent', $parentMenu);
        } else {
            $qb->andWhere('m.parent IS NULL');
        }

        $qb->orderBy('m.root, m.lft', 'ASC');

        return $qb;
    }

    /**
     * @param $presenter
     * @param $action
     * @return Menu|bool
     */
    public function getByPresenterAction($presenter, $action)
    {
        $this->buildCache(false);

        $key = $presenter . ':' . $action;
        return array_key_exists($key, $this->cachePresenter) ? $this->cachePresenter[$key] : false;
    }

    /**
     * @param IMenu $child
     * @param IMenu $root
     */
    public function persistAsLastChildOf(IMenu $child, IMenu $root)
    {
        $this->menuRepository->persistAsLastChildOf($child, $root);
    }

    /**
     * @param IMenu $menu
     * @param string $latteTemplate
     * @throws \Exception
     * @return void
     */
    public function saveLatteTemplate(IMenu $menu, $latteTemplate)
    {
        $menu->setLatteTemplate($latteTemplate);
        $this->entityManager->persist($menu);
        $this->entityManager->flush();
    }

    /**
     * @param IMenu $menu
     * @param $presenterName
     * @param $actionName
     * @throws \Exception
     * @return void
     */
    public function savePresenterAction(IMenu $menu, $presenterName, $actionName)
    {
        $menu->setPresenter($presenterName);
        $menu->setAction($actionName);
        $this->entityManager->persist($menu);
        $this->entityManager->flush();
    }

    /**
     * @param $factory
     * @param array $parameters
     * @param bool $isSystem
     * @return IMenu
     */
    public function getOneByFactoryAndParametersAndIsSystem($factory, array $parameters = [], $isSystem = false)
    {
        $parametersSum = $this->menuParameterSumGenerator->hash($parameters);
        $key = $factory . $parametersSum . ($isSystem ? 't' : 'f');
        $found = array_key_exists($key, $this->cacheFactory) ? $this->cacheFactory[$key] : false;
        if (!$found) {
            $qb = $this->menuRepository->createQueryBuilder('m')
                ->select('m')
                ->join('m.menuContents', 'mc')
                ->where('m.isSystem = :isSystem')
                ->andWhere('mc.factory = :factory')
                ->andWhere('mc.parametersSum = :parametersSum')
                ->setMaxResults(1)//It is possible that CMS will put multiple same components on multiple presenters and QueryBuilder::getOneOrNullResult() throws error on multiple result
                ->setParameters(
                    [
                        'factory' => $factory,
                        'isSystem' => $isSystem,
                        'parametersSum' => $parametersSum
                    ]
                );
            $found = $qb->getQuery()->getOneOrNullResult();
            if ($found) {
                $this->cacheFactory[$key] = $found;
            }
        }

        return $found;
    }

    /**
     * @param bool $isSitemap
     * @return array
     */
    public function getSitemap($isSitemap = true)
    {
        return $this->menuRepository->findBy(['isSitemap' => $isSitemap]);
    }

    /**
     * @param $name
     * @param $metaDescription
     * @param $metaKeywords
     * @param $metaRobots
     * @param $title
     * @param $h1
     * @param bool $isActive
     * @param bool $isHidden
     * @param bool $isHomePage
     * @param float $sitemapPriority
     * @param bool $isSitemap
     * @param bool $isShowH1
     * @param null $presenter
     * @param null $action
     * @param bool $isSystem
     * @param array $parameters
     * @param bool $isRegularExpression
     * @param bool $isRegularExpressionMatchArguments
     * @param string $layoutName
     * @return Menu
     * @throws \Exception
     */
    public function createNewMenu(
        $name,
        $metaDescription,
        $metaKeywords,
        $metaRobots,
        $title,
        $h1,
        $isActive = true,
        $isHidden = false,
        $isHomePage = false,
        $sitemapPriority = 0.5,
        $isSitemap = true,
        $isShowH1 = true,
        $presenter = null,
        $action = null,
        $isSystem = false,
        array $parameters = [],
        $isRegularExpression = false,
        $isRegularExpressionMatchArguments = false,
        $layoutName = 'layout'
    ) {
        $newMenu = new Menu(function ($parameters) {
            return $this->menuParameterSumGenerator->hash($parameters);
        },
            $name,
            $metaDescription,
            $metaKeywords,
            $metaRobots,
            $title,
            $h1,
            $isActive,
            $isHidden,
            $isHomePage,
            $sitemapPriority,
            $isSitemap,
            $isShowH1,
            $presenter,
            $action,
            $isSystem,
            $parameters,
            $isRegularExpression,
            $isRegularExpressionMatchArguments,
            $layoutName);

        $this->entityManager->persist($newMenu);
        $this->entityManager->flush();
        return $newMenu;
    }

    /**
     * @param IMenu $menu
     * @param ILocale $locale
     * @param $name
     * @param $metaDescription
     * @param $metaKeywords
     * @param $title
     * @param $h1
     * @throws \Exception
     */
    public function translateMenu(
        IMenu $menu,
        ILocale $locale,
        $name,
        $metaDescription,
        $metaKeywords,
        $title,
        $h1
    ) {
        $repository = $this->entityManager->getRepository('Gedmo\\Translatable\\Entity\\Translation');


        $repository->translate($menu, 'name', $locale->getLanguageCode(), $name)
            ->translate($menu, 'metaDescription', $locale->getLanguageCode(), $metaDescription)
            ->translate($menu, 'metaKeywords', $locale->getLanguageCode(), $metaKeywords)
            ->translate($menu, 'title', $locale->getLanguageCode(), $title)
            ->translate($menu, 'h1', $locale->getLanguageCode(), $h1);

        $this->entityManager->persist($menu);
        $this->entityManager->flush();
    }

    /**
     * @param $slug
     * @param array $requestParams
     * @param ILocale $locale
     * @return array
     */
    public function getBySlug($slug, $requestParams = [], $locale = null)
    {
        $parameters = [];

        $this->buildCache(false);

        $found = array_key_exists($slug, $this->cacheRegexFalse) ? $this->cacheRegexFalse[$slug] : false;

        //Normal not found, lets try regexp
        if (!$found) {
            foreach ($this->cacheRegexTrue AS $regexpRow) {
                if ($regexpRow->isRegularExpressionMatchArguments() && !empty($requestParams)) {
                    $slugMatch = $slug . '?' . http_build_query($requestParams);
                } else {
                    $slugMatch = $slug;
                }

                if (preg_match('/' . $regexpRow->slug . '/i', $slugMatch, $matches)) {
                    if (count($matches)) {
                        //Strip numbered keys
                        foreach ($matches as $k => $v) {
                            if (is_int($k)) {
                                unset($matches[$k]);
                            }
                        }
                        $parameters = $matches;
                    }

                    $found = $regexpRow;
                    break;
                }
            }
        }

        //Regexp not found, if slug is null lets assume its homepage
        if (!$found && is_null($slug)) {
            $found = $this->getHomePage();
        }

        if ($found) {
            $arrParams = $found->getParameters();
            if ($arrParams) {
                $parameters = array_merge($arrParams, $parameters);
            }
        }

        return [$found, $parameters];
    }

    /**
     * @return Menu
     */
    public function getHomePage()
    {
        if (!$this->cacheHomePage) {
            $this->cacheHomePage = $this->menuRepository->findOneBy(['isHomePage' => true]);
        }

        return $this->cacheHomePage;
    }

    /**
     * @param Menu $menu
     * @param int $number
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Exception
     */
    public function moveUp(Menu $menu, $number = 1)
    {
        if ($menu->getParent())
        {
            //Use standard moveUp when item has parent
            $this->menuRepository->moveUp($menu, $number);
        }
        else
        {
            if ($number != 1)
            {
                throw new \Exception('$number != 1 is not supported');
            }

            /** @var Menu $prevItem */
            $prevItem = $this->menuRepository->createQueryBuilder('node')
                ->select('node')
                ->where('node.root < :root')
                ->andWhere('node.isSystem = :isSystem')
                ->orderBy('node.root', 'DESC')
                ->setParameter('root', $menu->getRoot())
                ->setParameter('isSystem', false)
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
            
            if ($prevItem)
            {
                $prevItemRoot = $prevItem->getRoot();

                $qb = $this->menuRepository->createQueryBuilder('node');
                $qb->update()
                    ->set('node.root', $qb->expr()->literal($menu->getRoot()))
                    ->where('node = :prevItem')
                    ->setParameter('prevItem', $prevItem)
                    ->getQuery()
                    ->execute();

                $qb = $this->menuRepository->createQueryBuilder('node');
                $qb->update()
                    ->set('node.root', $qb->expr()->literal($prevItemRoot))
                    ->where('node = :prevItem')
                    ->setParameter('prevItem', $menu)
                    ->getQuery()
                    ->execute();
            }
        }
    }

    /**
     * @param Menu $menu
     * @param int $number
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Exception
     */
    public function moveDown(Menu $menu, $number = 1)
    {
        if ($menu->getParent())
        {
            //Use standard moveUp when item has parent
            $this->menuRepository->moveDown($menu, $number);
        }
        else
        {
            if ($number != 1)
            {
                throw new \Exception('$number != 1 is not supported');
            }

            /** @var Menu $prevItem */
            $nextItem = $this->menuRepository->createQueryBuilder('node')
                ->select('node')
                ->where('node.root > :root')
                ->andWhere('node.isSystem = :isSystem')
                ->orderBy('node.root', 'ASC')
                ->setParameter('root', $menu->getRoot())
                ->setParameter('isSystem', false)
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

            if ($nextItem)
            {
                $nextItemRoot = $nextItem->getRoot();
                $qb = $this->menuRepository->createQueryBuilder('node');
                $qb->update()
                    ->set('node.root', $qb->expr()->literal($menu->getRoot()))
                    ->where('node = :nextItem')
                    ->setParameter('nextItem', $nextItem)
                    ->getQuery()
                    ->execute();

                $qb = $this->menuRepository->createQueryBuilder('node');
                $qb->update()
                    ->set('node.root', $qb->expr()->literal($nextItemRoot))
                    ->where('node = :nextItem')
                    ->setParameter('nextItem', $menu)
                    ->getQuery()
                    ->execute();
            }
        }
    }
}