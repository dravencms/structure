<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Structure\Repository;


use Dravencms\Model\Structure\Entities\Menu;
use Dravencms\Model\Structure\Entities\MenuContent;
use Dravencms\Structure\MenuParameterSumGenerator;
use Dravencms\Database\EntityManager;

class MenuContentRepository
{
    /** @var \Doctrine\Persistence\ObjectRepository|MenuContent */
    private $menuContentRepository;

    /** @var EntityManager */
    private $entityManager;

    /** @var MenuParameterSumGenerator */
    private $menuParameterSumGenerator;

    /** @var MenuContent[] */
    private $cacheFactory = [];

    /** @var bool */
    private $isCacheFactoryInitialized = false;


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
     * @param Menu $menu
     * @param $factory
     * @param array $parameters
     * @return MenuContent
     */
    public function getOneByMenuFactoryParameters(Menu $menu, string $factory, array $parameters): ?MenuContent
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
     * @param Menu $menu
     * @param $factory
     * @param array $parameters
     * @return MenuContent
     * @throws \Exception
     */
    public function saveMenuContent(Menu $menu, string $factory, array $parameters): MenuContent
    {
        $menuContent = new MenuContent($menu, $factory, $parameters, function($parameters){
            return $this->menuParameterSumGenerator->hash($parameters);
        });

        $this->entityManager->persist($menuContent);
        $this->entityManager->flush();

        $parametersSum = $this->menuParameterSumGenerator->hash($menuContent->getParameters());
        $key = $menuContent->getFactory() . $parametersSum . ($menuContent->getMenu()->isSystem() ? 't' : 'f');
        $this->cacheFactory[$key] = $menuContent;

        return $menuContent;
    }

    /**
     * @param $factory
     * @param array $parameters
     * @param bool $isSystem
     * @return MenuContent
     */
    public function getOneByFactoryAndParametersAndIsSystem(string $factory, array $parameters = [], bool $isSystem = false): ?MenuContent
    {
        if (!$this->isCacheFactoryInitialized)
        {
            $qb = $this->menuContentRepository->createQueryBuilder('mc')
                ->select('mc')
                ->join('mc.menu', 'm')
                ->where('m.isActive = :isActive')
                ->setParameters([
                    'isActive' => true
                ]);

            /** @var MenuContent $menuContent */
            foreach($qb->getQuery()->getResult() AS $menuContent)
            {
                $parametersSum = $this->menuParameterSumGenerator->hash($menuContent->getParameters());
                $key = $menuContent->getFactory() . $parametersSum . ($menuContent->getMenu()->isSystem() ? 't' : 'f');
                $this->cacheFactory[$key] = $menuContent;
            }

            $this->isCacheFactoryInitialized = true;
        }

        $parametersSum = $this->menuParameterSumGenerator->hash($parameters);
        $key = $factory . $parametersSum . ($isSystem ? 't' : 'f');
        return array_key_exists($key, $this->cacheFactory) ? $this->cacheFactory[$key] : null;
    }

    /**
     * @param $id
     * @return null|MenuContent
     */
    public function getOneById(int $id): ?MenuContent
    {
        return $this->menuContentRepository->find($id);
    }

    /**
     * @param Menu $menu
     * @throws \Exception
     * @return void
     */
    public function clearMenuContent(Menu $menu): void
    {
        foreach ($menu->getMenuContents() AS $menuContent)
        {
            $menu->removeMenuContent($menuContent);
            $this->entityManager->remove($menuContent);
        }

        $this->entityManager->flush();
    }
}