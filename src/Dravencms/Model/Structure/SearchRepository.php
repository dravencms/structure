<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace App\Model\Structure\Repository;

use App\Model\Structure\Entities\Menu;
use App\Model\Structure\Entities\MenuParameterSumGenerator;
use Doctrine\Common\Collections\ArrayCollection;
use Kdyby\Doctrine\EntityManager;
use Nette;
use Salamek\Cms\CmsActionOption;
use Salamek\Cms\ICmsActionOption;
use Salamek\Cms\ICmsComponentRepository;
use Salamek\Cms\Models\ILocale;
use Salamek\Cms\Models\IMenu;
use Salamek\Cms\Models\IMenuRepository;

class SearchRepository implements ICmsComponentRepository
{
    /** @var \Kdyby\Doctrine\EntityRepository */
    private $menuRepository;

    /** @var EntityManager */
    private $entityManager;

    /**
     * MenuRepository constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
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
     * @param null $query
     * @param null $limit
     * @param null $offset
     * @return Menu[]
     */
    public function search($query = null, $limit = null, $offset = null)
    {
        $qb = $this->menuRepository->createQueryBuilder('m')
            ->select('m')
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
            $qb->andWhere('m.name LIKE :query')
                ->orWhere('m.slug LIKE :query')
                ->orWhere('m.metaDescription LIKE :query')
                ->orWhere('m.metaKeywords LIKE :query')
                ->orWhere('m.title LIKE :query')
                ->orWhere('m.h1 LIKE :query')
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
     * @param string $componentAction
     * @return ICmsActionOption[]
     */
    public function getActionOptions($componentAction)
    {
        switch ($componentAction)
        {
            case 'Overview':
                return null;
                break;

            default:
                return false;
                break;
        }
    }

    /**
     * @param string $componentAction
     * @param array $parameters
     * @param ILocale $locale
     * @return null
     */
    public function getActionOption($componentAction, array $parameters, ILocale $locale)
    {
        switch ($componentAction)
        {
            case 'Overview':
                return new CmsActionOption('Search');
                break;

            default:
                return null;
                break;
        }

    }
}