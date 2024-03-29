<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Structure\Repository;

use Dravencms\Model\Structure\Entities\Menu;
use Dravencms\Structure\MenuParameterSumGenerator;
use Dravencms\Database\EntityManager;
use Dravencms\Model\Locale\Entities\ILocale;

class MenuRepository
{
    /** @var \Doctrine\Persistence\ObjectRepository|Menu */
    private $menuRepository;
    
    /** @var EntityManager */
    private $entityManager;

    /** @var MenuParameterSumGenerator */
    private $menuParameterSumGenerator;

    /** @var bool */
    private $presenterCacheInitialized = false;

    /** @var Menu[] */
    private $cachePresenter = [];
    
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
     * @return \Kdyby\Doctrine\EntityRepository
     */
    public function getMenuRepository()
    {
        return $this->menuRepository;
    }

    /**
     * @param Menu|null $parentMenu
     * @param bool $isSystem
     * @return static
     */
    public function getMenuQueryBuilder(Menu $parentMenu = null, bool $isSystem = false)
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
    public function isIdentifierFree(string $identifier, Menu $ignoreMenu = null): bool
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
     * @deprecated
     * @param Menu $menu
     * @return Menu[]
     */
    private function buildParentTreeResolver(Menu $menu): array
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
     * @deprecated
     * @param Menu $menu
     * @return Menu[]
     */
    public function buildParentTree(Menu $menu): array
    {
        trigger_error('Method ' . __METHOD__ . ' is deprecated, use getPath instead', E_USER_DEPRECATED);
        return array_reverse($this->buildParentTreeResolver($menu));
    }

    /**
     * @param Menu $menu
     * @return Menu[]
     */
    public function getPath(Menu $menu)
    {
        return $this->menuRepository->getPath($menu);
    }

    /**
     * @param $options
     * @param ILocale $locale
     * @return mixed
     */
    public function getTree(array $options, ILocale $locale = null)
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
     */
    public function getOneById(int $id): ?Menu
    {
        return $this->menuRepository->find($id);
    }

    /**
     * @param $identifier
     * @return Menu|null
     */
    public function getOneByIdentifier(string $identifier): ?Menu
    {
        return $this->menuRepository->findOneBy(['identifier' => $identifier]);
    }
    
    /**
     * @return Menu[]
     */
    public function getAll()
    {
        return $this->menuRepository->findBy(['isActive' => true]);
    }

    /**
     * @param bool $isSystem
     * @return Menu[]
     */
    public function getAllByIsSystem($isSystem = true)
    {
        return $this->menuRepository->findBy(['isSystem' => $isSystem]);
    }

    /**
     * @throws \Exception
     */
    public function resetIsHomePage(): void
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
    public function getOneByPresenterAction(string $presenter, string $action): ?Menu
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
    public function persistAsLastChildOf(Menu $child, Menu $root): void
    {
        $this->menuRepository->persistAsLastChildOf($child, $root);
    }

    /**
     * @param Menu $what
     * @param Menu $behindWhat
     */
    public function persistAsNextSiblingOf(Menu $what, Menu $behindWhat): void
    {
        $this->menuRepository->persistAsNextSiblingOf($what, $behindWhat);
    }


    /**
     * @param Menu $menu
     * @param string $latteTemplate
     * @throws \Exception
     * @return void
     */
    public function saveLatteTemplate(Menu $menu, string $latteTemplate): void
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
    public function savePresenterAction(Menu $menu, string $presenterName, string $actionName): void
    {
        $menu->setPresenter($presenterName);
        $menu->setAction($actionName);
        $this->entityManager->persist($menu);
        $this->entityManager->flush();

        $this->cachePresenter[$presenterName . ':' . $actionName] = $menu;

    }

    /**
     * @param bool $isSitemap
     * @return array
     */
    public function getSitemap(bool $isSitemap = true)
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
        string $identifier,
        bool $isActive = true,
        bool $isHidden = false,
        bool $isHomePage = false,
        float $sitemapPriority = 0.5,
        bool $isSitemap = true,
        bool $isShowH1 = true,
        string $presenter = null,
        string $action = null,
        bool $isSystem = false,
        array $parameters = [],
        bool $isRegularExpression = false,
        bool $isRegularExpressionMatchArguments = false,
        string $layoutName = 'layout'
    ): Menu {
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
     * @return null|Menu
     */
    public function getHomePage(): ?Menu
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
    public function moveUp(Menu $menu, int $number = 1): void
    {
        $this->menuRepository->moveUp($menu, $number);
    }

    /**
     * @param Menu $menu
     * @param int $number
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Exception
     */
    public function moveDown(Menu $menu, int $number = 1): void
    {
        $this->menuRepository->moveDown($menu, $number);
    }

    /**
     * @param null|Menu $parentMenu
     * @param bool $isSystem
     * @return null|Menu
     */
    public function getLastMenuItem(Menu $parentMenu = null, bool $isSystem = false): ?Menu
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

        $qb->orderBy('m.lft', 'DESC');
        $qb->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
