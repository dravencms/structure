<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Structure\Repository;


use Dravencms\Model\Structure\Entities\MenuContent;
use Dravencms\Structure\MenuParameterSumGenerator;
use Kdyby\Doctrine\EntityManager;
use Nette;
use Dravencms\Model\Structure\Entities\IMenu;

class MenuContentRepository
{
    /** @var \Kdyby\Doctrine\EntityRepository */
    private $menuContentRepository;

    /** @var EntityManager */
    private $entityManager;

    /** @var MenuParameterSumGenerator */
    private $menuParameterSumGenerator;

    /**
     * MenuContentRepository constructor.
     * @param EntityManager $entityManager
     * @param MenuParameterSumGenerator $menuParameterSumGenerator
     */
    public function __construct(EntityManager $entityManager, MenuParameterSumGenerator $menuParameterSumGenerator)
    {
        $this->entityManager = $entityManager;
        $this->menuParameterSumGenerator = $menuParameterSumGenerator;
        $this->menuContentRepository = $entityManager->getRepository(MenuContent::class);
    }

    /**
     * @param IMenu $menu
     * @param $factory
     * @param array $parameters
     * @return MenuContent
     */
    public function getOneByMenuFactoryParameters(IMenu $menu, $factory, array $parameters)
    {
        $qb = $this->menuContentRepository->createQueryBuilder('mc')
            ->select('mc')
            ->where('mc.menu = :menu')
            ->andWhere('mc.factory = :factory')
            ->andWhere('mc.parametersSum = :parametersSum')
            ->setParameters(
                [
                    'menu' => $menu,
                    'factory' => $factory,
                    'parametersSum' => $this->menuParameterSumGenerator->hash($parameters)
                ]
            );

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param IMenu $menu
     * @param $factory
     * @param array $parameters
     * @return MenuContent
     * @throws \Exception
     */
    public function saveMenuContent(IMenu $menu, $factory, array $parameters)
    {
        $menuContent = new MenuContent($menu, $factory, $parameters, function($parameters){
            return $this->menuParameterSumGenerator->hash($parameters);
        });

        $this->entityManager->persist($menuContent);
        $this->entityManager->flush();

        return $menuContent;
    }

    /**
     * @param $id
     * @return null|MenuContent
     */
    public function getOneById($id)
    {
        return $this->menuContentRepository->find($id);
    }

    /**
     * @param IMenu $menu
     * @throws \Exception
     * @return void
     */
    public function clearMenuContent(IMenu $menu)
    {
        foreach ($menu->getMenuContents() AS $menuContent)
        {
            $menu->removeMenuContent($menuContent);
            $this->entityManager->remove($menuContent);
        }

        $this->entityManager->flush();
    }
}