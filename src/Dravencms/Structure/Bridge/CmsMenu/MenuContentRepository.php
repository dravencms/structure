<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Structure\Bridge\CmsMenu;


use Nette;
use Salamek\Cms\Models\IMenuContentRepository;
use Salamek\Cms\Models\IMenu;

class MenuContentRepository implements IMenuContentRepository
{
    /** @var \Dravencms\Model\Structure\Repository\MenuContentRepository */
    private $menuContentRepository;

    /** @var MenuRepository */
    private $menuRepository;

    /**
     * MenuContentRepository constructor.
     * @param \Dravencms\Model\Structure\Repository\MenuContentRepository $menuContentRepository
     * @param \Dravencms\Model\Structure\Repository\MenuRepository $menuRepository
     */
    public function __construct(
            \Dravencms\Model\Structure\Repository\MenuContentRepository $menuContentRepository, 
            \Dravencms\Model\Structure\Repository\MenuRepository $menuRepository)
    {
        $this->menuContentRepository = $menuContentRepository;
        $this->menuRepository = $menuRepository;
    }

    /**
     * @param IMenu $menu
     * @param $factory
     * @param array $parameters
     * @return array
     */
    public function getOneByMenuFactoryParameters(IMenu $menu, string $factory, array $parameters): ?MenuContent
    {
        $menuNative = $this->menuRepository->getOneById($menu->getId());
        $menuContentNative = $this->menuContentRepository->getOneByMenuFactoryParameters($menuNative, $factory, $parameters);

        return ($menuContentNative ? new MenuContent($menuContentNative) : null);
    }

    /**
     * @param IMenu $menu
     * @param $factory
     * @param array $parameters
     * @return MenuContent
     * @throws \Exception
     */
    public function saveMenuContent(IMenu $menu, string $factory, array $parameters): MenuContent
    {
        $menuNative = $this->menuRepository->getOneById($menu->getId());

        return new MenuContent($this->menuContentRepository->saveMenuContent($menuNative, $factory, $parameters));
    }

    /**
     * @param $id
     * @return null|object
     */
    public function getOneById(int $id): ?MenuContent
    {
        $nativeMenuContent = $this->menuContentRepository->getOneById($id);
        return ($nativeMenuContent ? new MenuContent($nativeMenuContent) : null);
    }

    /**
     * @param IMenu $menu
     * @throws \Exception
     * @return void
     */
    public function clearMenuContent(IMenu $menu): void
    {
        $menuNative = $this->menuRepository->getOneById($menu->getId());

        $this->menuContentRepository->clearMenuContent($menuNative);
    }
    
    /**
     * @param $factory
     * @param array $parameters
     * @param bool $isSystem
     * @return Menu|null
     */
    public function getOneByFactoryAndParametersAndIsSystem(string $factory, array $parameters = [], bool $isSystem = false): ?MenuContent
    {
        $found = $this->menuContentRepository->getOneByFactoryAndParametersAndIsSystem($factory, $parameters, $isSystem);
        if (!$found)
        {
            return null;
        }
        return new MenuContent($found);
    }

}