<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Structure\Bridge\CmsMenu;

use Salamek\Cms\Models\IMenu;

class Menu implements IMenu
{
    /** @var \Dravencms\Model\Structure\Entities\Menu */
    private $menu;

    /**
     * Menu constructor.
     * @param \Dravencms\Model\Structure\Entities\Menu $menu
     */
    public function __construct(\Dravencms\Model\Structure\Entities\Menu $menu)
    {
        $this->menu = $menu;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->menu->getAction();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->menu->getId();
    }

    /**
     * @return null|string
     */
    public function getLatteTemplate(): ?string
    {
        return $this->menu->getLatteTemplate();
    }

    /**
     * @return null|string
     */
    public function getLayoutName(): ?string
    {
        return $this->menu->getLayoutName();
    }

    /**
     * @return \Generator|MenuContent
     */
    public function getMenuContents()
    {
        foreach ($this->menu->getMenuContents() AS $row)
        {
            yield new MenuContent($row);
        }
    }

    /**
     * @return string
     */
    public function getMetaRobots(): string
    {
        return $this->menu->getMetaRobots();
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->menu->getParameters();
    }

    /**
     * @return string
     */
    public function getPresenter(): string
    {
        return $this->menu->getPresenter();
    }

    /**
     * @return bool
     */
    public function isHomePage(): bool
    {
        return $this->menu->isHomePage();
    }

    /**
     * @return bool|mixed
     */
    public function isShowH1(): bool
    {
        return $this->menu->isShowH1();
    }

    /**
     * @return bool
     */
    public function isSystem(): bool
    {
        return $this->menu->isSystem();
    }

    public function getIdentifier(): string
    {
        return $this->menu->getIdentifier();
    }
}