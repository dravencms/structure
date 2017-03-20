<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Structure\Repository;

use Dravencms\Model\Structure\Entities\Menu;
use Dravencms\Structure\MenuParameterSumGenerator;
use Doctrine\ORM\Query;
use Kdyby\Doctrine\EntityManager;
use Nette;
use Dravencms\Model\Locale\Entities\ILocale;

class MenuRepository
{
    /** @var \Kdyby\Doctrine\EntityRepository */
    private $menuRepository;
    
    /** @var EntityManager */
    private $entityManager;

    /** @var MenuParameterSumGenerator */
    private $menuParameterSumGenerator;

    /** @var bool */
    private $presenterCacheInitialized = false;

    /** @var Menu[] */
    private $cachePresenter = [];

    /** @var Menu[] */
    private $cacheFactory = [];

    /** @var bool */
    private $isCacheFactoryInitialized = false;

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
     * @param Menu|null $parentMenu
     * @param bool $isSystem
     * @return static
     */
    public function getMenuQueryBuilder(Menu $parentMenu = null, $isSystem = false)
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

        $qb->orderBy('m.lft', 'ASC');

        return $qb;
    }

    /**
     * @param $identifier
     * @param Menu|null $ignoreMenu
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isIdentifierFree($identifier, Menu $ignoreMenu = null)
    {
        $qb = $this->menuRepository->createQueryBuilder('m')
            ->select('m')
            ->where('m.identifier = :identifier')
            ->setParameters([
                'identifier' => $identifier,
            ]);

        if ($ignoreMenu) {
            $qb->andWhere('m != :ignoreMenu')
                ->setParameter('ignoreMenu', $ignoreMenu);
        }

        $query = $qb->getQuery();
        return (is_null($query->getOneOrNullResult()));
    }

    /**
     * @param $id
     * @return Menu[]
     */
    public function getById($id)
    {
        return $this->menuRepository->findBy(['id' => $id]);
    }

    /**
     * @param Menu $menu
     * @return Menu[]
     */
    private function buildParentTreeResolver(Menu $menu)
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
     * @param Menu $menu
     * @return Menu[]
     */
    public function buildParentTree(Menu $menu)
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
            ->addSelect('t')
            ->join('node.translations', 't')
            ->orderBy('node.lft', 'ASC')
            ->where('node.isHidden = :isHidden')
            ->andWhere('node.isActive = :isActive')
            ->andWhere('t.locale = :locale')
            ->setParameters(
                [
                    'isHidden' => false,
                    'isActive' => true,
                    'locale' => $locale
                ]
            )
            ->getQuery();

        return $this->menuRepository->buildTree($query->getArrayResult(), $options);
    }

    /**
     * @param $id
     * @return Menu|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getOneById($id)
    {
        return $this->menuRepository->find($id);
    }
    
    /**
     * @return Menu[]
     */
    public function getAll()
    {
        return $this->menuRepository->findBy(['isActive' => true]);
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
     * @param $presenter
     * @param $action
     * @return Menu|null
     */
    public function getOneByPresenterAction($presenter, $action)
    {
        if (!$this->presenterCacheInitialized) {
            $result = $this->menuRepository->findAll();

            /** @var Menu $item */
            foreach ($result AS $item) {
                $this->cachePresenter[$item->getPresenter() . ':' . $item->getAction()] = $item;
            }

            $this->presenterCacheInitialized = true;
        }

        $key = $presenter . ':' . $action;
        return array_key_exists($key, $this->cachePresenter) ? $this->cachePresenter[$key] : null;
    }

    /**
     * @param Menu $child
     * @param Menu $root
     */
    public function persistAsLastChildOf(Menu $child, Menu $root)
    {
        $this->menuRepository->persistAsLastChildOf($child, $root);
    }

    /**
     * @param Menu $menu
     * @param string $latteTemplate
     * @throws \Exception
     * @return void
     */
    public function saveLatteTemplate(Menu $menu, $latteTemplate)
    {
        $menu->setLatteTemplate($latteTemplate);
        $this->entityManager->persist($menu);
        $this->entityManager->flush();
    }

    /**
     * @param Menu $menu
     * @param $presenterName
     * @param $actionName
     * @throws \Exception
     * @return void
     */
    public function savePresenterAction(Menu $menu, $presenterName, $actionName)
    {
        $menu->setPresenter($presenterName);
        $menu->setAction($actionName);
        $this->entityManager->persist($menu);
        $this->entityManager->flush();

        $this->cachePresenter[$presenterName . ':' . $actionName] = $menu;

    }

    /**
     * @param $factory
     * @param array $parameters
     * @param bool $isSystem
     * @return Menu
     */
    public function getOneByFactoryAndParametersAndIsSystem($factory, array $parameters = [], $isSystem = false)
    {
        if (!$this->isCacheFactoryInitialized)
        {
            $qb = $this->menuRepository->createQueryBuilder('m')
                ->select(['mc.parameters', 'mc.factory'])
                ->addSelect('m AS menu')
                ->join('m.menuContents', 'mc')
                ->where('m.isActive = :isActive')
                ->setParameters([
                    'isActive' => true
                ]);

            /** @var Menu $menu */
            foreach($qb->getQuery()->getResult() AS $mix)
            {
                $parametersSum = $this->menuParameterSumGenerator->hash($mix['parameters']);
                $key = $mix['factory'] . $parametersSum . ($mix['menu']->isSystem() ? 't' : 'f');
                $this->cacheFactory[$key] = $mix['menu'];
            }

            $this->isCacheFactoryInitialized = true;
        }

        $parametersSum = $this->menuParameterSumGenerator->hash($parameters);
        $key = $factory . $parametersSum . ($isSystem ? 't' : 'f');
        return array_key_exists($key, $this->cacheFactory) ? $this->cacheFactory[$key] : null;
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
     * @param $identifier
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
        $identifier,
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
            $identifier,
            'index, follow',
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
        $this->menuRepository->moveUp($menu, $number);
    }

    /**
     * @param Menu $menu
     * @param int $number
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Exception
     */
    public function moveDown(Menu $menu, $number = 1)
    {
        $this->menuRepository->moveDown($menu, $number);
    }
}