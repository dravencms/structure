<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Structure\Repository;

use Dravencms\Model\Structure\Entities\Menu;
use Dravencms\Model\Structure\Entities\MenuTranslation;
use Dravencms\Structure\MenuParameterSumGenerator;
use Doctrine\ORM\Query;
use Kdyby\Doctrine\EntityManager;
use Nette;
use Dravencms\Model\Locale\Entities\ILocale;

class MenuTranslationRepository
{
    /** @var \Kdyby\Doctrine\EntityRepository */
    private $menuTranslationRepository;

    /** @var EntityManager */
    private $entityManager;

    /**
     * MenuTranslationRepository constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
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
    public function isNameFree($name, ILocale $locale, Menu $parentMenu = null, Menu $ignoreMenu = null)
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
    public function getMenuTranslationQueryBuilder(Menu $parentMenu = null, $isSystem = false)
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
    public function search($query = null, $limit = null, $offset = null)
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
    public function getOneBySlug($slug, $requestParams = [], $locale = null)
    {
        $parameters = [];

        $this->buildCache(false);

        $found = array_key_exists($slug, $this->cacheRegexFalse) ? $this->cacheRegexFalse[$slug] : null;

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
     * @param Menu $menu
     * @param ILocale $locale
     * @return MenuTranslation
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getTranslation(Menu $menu, ILocale $locale)
    {
        $qb = $this->menuTranslationRepository->createQueryBuilder('t')
            ->select('t')
            ->where('t.locale = :locale')
            ->andWhere('t.menu = :menu')
            ->setParameters([
                'menu' => $menu,
                'locale' => $locale
            ]);
        return $qb->getQuery()->getOneOrNullResult();
    }
}