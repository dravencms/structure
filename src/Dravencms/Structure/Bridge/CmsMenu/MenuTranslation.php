<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Structure\Bridge\CmsMenu;

use Salamek\Cms\Models\IMenu;
use Salamek\Cms\Models\IMenuTranslation;

class MenuTranslation implements IMenuTranslation
{
    /** @var \Dravencms\Model\Structure\Entities\MenuTranslation */
    private $menuTranslation;

    /**
     * Menu constructor.
     * @param \Dravencms\Model\Structure\Entities\MenuTranslation $menuTranslation
     */
    public function __construct(\Dravencms\Model\Structure\Entities\MenuTranslation $menuTranslation)
    {
        $this->menuTranslation = $menuTranslation;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->menuTranslation->getName();
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->menuTranslation->getSlug();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->menuTranslation->getId();
    }

    /**
     * @return string
     */
    public function getMetaDescription(): string
    {
        return $this->menuTranslation->getMetaDescription();
    }

    /**
     * @return string
     */
    public function getMetaKeywords(): string
    {
        return $this->menuTranslation->getMetaKeywords();
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->menuTranslation->getTitle();
    }

    /**
     * @return string
     */
    public function getH1(): string
    {
        return $this->menuTranslation->getH1();
    }

    /**
     * @return IMenu
     */
    public function getIMenu(): Menu
    {
        return new Menu($this->menuTranslation->getMenu());
    }
}