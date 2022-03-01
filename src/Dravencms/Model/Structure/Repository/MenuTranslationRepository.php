<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Structure\Repository;

use Dravencms\Model\Locale\Entities\Locale;
use Dravencms\Model\Structure\Entities\Menu;
use Dravencms\Model\Structure\Entities\MenuTranslation;
use Dravencms\Structure\MenuSlugGenerator;
use Dravencms\Database\EntityManager;
use Dravencms\Model\Locale\Entities\ILocale;


class MenuTranslationRepository
{
    /** @var \Doctrine\Persistence\ObjectRepository|MenuTranslation */
    private $menuTranslationRepository;

    /** @var MenuSlugGenerator */
    private $menuSlugGenerator;

    /** @var EntityManager */
    private $entityManager;

    private $isMenuTranslationSlugRuntimeCacheInitialized = false;

    private $menuTranslationSlugRuntimeCache = [];

    /**
     * MenuTranslationRepository constructor.
     * @param EntityManager $entityManager
     * @param MenuSlugGenerator $menuSlugGenerator
     */
    public function __construct(
        EntityManager $entityManager,
        MenuSlugGenerator $menuSlugGenerator
    )
    {
        $this->entityManager = $entityManager;
        $this->menuSlugGenerator = $menuSlugGenerator;
        $this->menuTranslationRepository = $entityManager->getRepository(MenuTranslation::class);
    }


    /**
     * @param $name
     * @param ILocale $locale
     * @param Menu|null $parentMenu
     * @param Menu|null $ignoreMenu
     * @return bool
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isNameFree(string $name, ILocale $locale, Menu $parentMenu = null, Menu $ignoreMenu = null): bool
    {
        $qb = $this->menuTranslationRepository->createQueryBuilder('t')
            ->select('t')
            ->join('t.menu', 'm')
            ->where('t.name = :name')
            ->andWhere('t.locale = :locale')
            ->setParameters([
                'name' => $name,
                'locale' => $locale,
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
        return (is_null($query->getOneOrNullResult()));
    }

    /**
     * @param Menu|null $parentMenu
     * @param bool $isSystem
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getMenuTranslationQueryBuilder(Menu $parentMenu = null, bool $isSystem = false)
    {
        $qb = $this->menuTranslationRepository->createQueryBuilder('t')
            ->select('t')
            ->join('t.menu', 'm')
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
     * @param null $query
     * @param null $limit
     * @param null $offset
     * @return Menu[]
     */
    public function search(string $query = null, int $limit = null, int $offset = null)
    {
        $qb = $this->menuTranslationRepository->createQueryBuilder('t')
            ->select('t')
            ->join('t.menu', 'm')
            ->where('m.isActive = :isActive')
            ->andWhere('m.isContent = :isContent')
            ->setParameters(
                [
                    'isActive' => true,
                    'isContent' => true
                ]
            );

        if ($query)
        {
            $qb->andWhere('t.name LIKE :query')
                ->orWhere('t.slug LIKE :query')
                ->orWhere('t.metaDescription LIKE :query')
                ->orWhere('t.metaKeywords LIKE :query')
                ->orWhere('t.title LIKE :query')
                ->orWhere('t.h1 LIKE :query')
                ->setParameter('query', '%'.$query.'%');
        }

        if ($limit)
        {
            $qb->setMaxResults($limit);
        }

        if ($offset)
        {
            $qb->setFirstResult($offset);
        }

        $qb->orderBy('m.sitemapPriority', 'DESC');
        return $qb->getQuery()->getResult();
    }


    /**
     * @param $slug
     * @param array $requestParams
     * @param ILocale $locale
     * @return array
     */
    public function getOneBySlug(string $slug, array $requestParams = [], ILocale $locale = null)
    {
        $found = $this->menuTranslationRepository->findOneBy(['slug' => $slug, 'locale' => $locale]);
        if ($found)
        {
            return [$found->getMenu(), ($found->getMenu()->getParameters() ? array_merge($found->getMenu()->getParameters(), $requestParams): [])];
        }

        $qb = $this->menuTranslationRepository->createQueryBuilder('t')
            ->select('t')
            ->join('t.menu', 'm')
            ->where('t.locale = :locale')
            ->andWhere('m.isRegularExpression = :isRegularExpression')
            ->setParameters([
                'isRegularExpression' => true,
                'locale' => $locale
            ]);
        /** @var MenuTranslation $regexpRow */
        foreach ($qb->getQuery()->getResult() AS $regexpRow) {
            if ($regexpRow->getMenu()->isRegularExpressionMatchArguments() && !empty($requestParams)) {
                $slugMatch = $slug . '?' . http_build_query($requestParams);
            } else {
                $slugMatch = $slug;
            }

            if (preg_match('/' . $regexpRow->getSlug() . '/i', $slugMatch, $matches)) {
                $parameters = [];
                if (count($matches)) {
                    //Strip numbered keys
                    foreach ($matches as $k => $v) {
                        if (is_int($k)) {
                            unset($matches[$k]);
                        }
                    }
                    $parameters = $matches;
                }

                return [$regexpRow->getMenu(), array_merge($parameters, $requestParams)];
                break;
            }
        }

        if (!$slug)
        {
            $qb = $this->menuTranslationRepository->createQueryBuilder('t')
                ->select('t')
                ->join('t.menu', 'm')
                ->where('t.locale = :locale')
                ->andWhere('m.isHomePage = :isHomePage')
                ->setParameters([
                    'isHomePage' => true,
                    'locale' => $locale
                ]);
            $result = $qb->getQuery()->getOneOrNullResult();
            if ($result)
            {
                return [$result->getMenu(), []];
            }
        }
        
        return null;
    }


    /**
     * @param Menu $menu
     * @param ILocale $locale
     * @return MenuTranslation
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getTranslation(Menu $menu, ILocale $locale): ?MenuTranslation
    {
        return $this->menuTranslationRepository->findOneBy(['menu' => $menu, 'locale' => $locale]);
    }

    /**
     * @param Menu $menu
     * @param ILocale $locale
     * @return mixed|null
     */
    public function getSlug(Menu $menu, ILocale $locale): string
    {
        if (!$this->isMenuTranslationSlugRuntimeCacheInitialized)
        {
            foreach($this->getAll() AS $menuTranslation)
            {
                $this->menuTranslationSlugRuntimeCache[$menuTranslation->getMenu()->getId().$menuTranslation->getLocale()->getLanguageCode()] = $menuTranslation->getSlug();
            }

            $this->isMenuTranslationSlugRuntimeCacheInitialized = true;
        }

        $key = $menu->getId().$locale->getLanguageCode();
        return (array_key_exists($key, $this->menuTranslationSlugRuntimeCache) ? $this->menuTranslationSlugRuntimeCache[$key] : null);
    }


    /**.
     * @param Menu $menu
     * @param Locale $locale
     * @param $h1
     * @param $metaDescription
     * @param $metaKeywords
     * @param $title
     * @param $name
     * @param $slug
     * @return MenuTranslation
     * @throws \Exception
     */
    public function translateMenu(
            Menu $menu, 
            Locale $locale, 
            string $h1, 
            string $metaDescription, 
            string $metaKeywords, 
            string $title, 
            string $name, 
            string $slug = null
            ): ?MenuTranslation
    {
        if ($foundTranslation = $this->getTranslation($menu, $locale))
        {
            $foundTranslation->setH1($h1);
            $foundTranslation->setName($name);
            $foundTranslation->setMetaDescription($metaDescription);
            $foundTranslation->setMetaKeywords($metaKeywords);
            $foundTranslation->setTitle($title);
        }
        else
        {
            $foundTranslation = new MenuTranslation($menu, $locale, $name, $metaDescription, $metaKeywords, $title, $h1, function($menuTranslation){
                return $this->menuSlugGenerator->slugify($menuTranslation);
            });
        }

        if ($slug)
        {
            $foundTranslation->setSlug($slug);
        }

        $this->entityManager->persist($foundTranslation);

        $this->entityManager->flush();

        $this->menuTranslationSlugRuntimeCache[$foundTranslation->getMenu()->getId().$foundTranslation->getLocale()->getLanguageCode()] = $foundTranslation->getSlug();

        return $foundTranslation;
    }

    /**
     * @return MenuTranslation[]
     */
    public function getAll()
    {
        return $this->menuTranslationRepository->findAll();
    }
}