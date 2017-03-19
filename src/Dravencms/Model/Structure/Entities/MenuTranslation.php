<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Structure\Entities;

use Doctrine\ORM\Mapping as ORM;
use Dravencms\Model\Locale\Entities\Locale;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Nette;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * Class MenuTranslation
 * @package App\Model\Structure\Entities
 * @ORM\Entity
 * @ORM\Table(name="structureMenuTranslation", uniqueConstraints={@UniqueConstraint(name="slug_unique", columns={"slug", "locale_id"})})
 */
class MenuTranslation extends Nette\Object
{
    use Identifier;
    use TimestampableEntity;

    /**
     * @var string
     * @ORM\Column(type="string",length=255,nullable=false)
     */
    private $name;

    /**
     * @ORM\Column(type="string",length=255)
     */
    private $slug;

    /**
     * @var string
     * @ORM\Column(type="string",length=255)
     */
    private $metaDescription;

    /**
     * @var string
     * @ORM\Column(type="string",length=255)
     */
    private $metaKeywords;

    /**
     * @var string
     * @ORM\Column(type="string",length=255)
     */
    private $title;

    /**
     * @var string
     * @ORM\Column(type="string",length=255)
     */
    private $h1;

    /**
     * @var Menu
     * @ORM\ManyToOne(targetEntity="Menu", inversedBy="translations")
     * @ORM\JoinColumn(name="menu_id", referencedColumnName="id")
     */
    private $menu;

    /**
     * @var Locale
     * @ORM\ManyToOne(targetEntity="Dravencms\Model\Locale\Entities\Locale")
     * @ORM\JoinColumn(name="locale_id", referencedColumnName="id")
     */
    private $locale;

    /**
     * MenuTranslation constructor.
     * @param Menu $menu
     * @param Locale $locale
     * @param $name
     * @param $metaDescription
     * @param $metaKeywords
     * @param $title
     * @param $h1
     * @param callable $slugGenerator
     */
    public function __construct(Menu $menu, Locale $locale, $name, $metaDescription, $metaKeywords, $title, $h1, callable $slugGenerator)
    {
        $this->name = $name;
        $this->metaDescription = $metaDescription;
        $this->metaKeywords = $metaKeywords;
        $this->title = $title;
        $this->h1 = $h1;
        $this->menu = $menu;
        $this->locale = $locale;
        $this->generateSlug($slugGenerator);
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    public function generateSlug(callable  $slugGenerator)
    {
        $this->setSlug($slugGenerator($this));
    }

    /**
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * @param string $metaDescription
     */
    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;
    }

    /**
     * @param string $metaKeywords
     */
    public function setMetaKeywords($metaKeywords)
    {
        $this->metaKeywords = $metaKeywords;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @param string $h1
     */
    public function setH1($h1)
    {
        $this->h1 = $h1;
    }

    /**
     * @param Menu $menu
     */
    public function setMenu($menu)
    {
        $this->menu = $menu;
    }

    /**
     * @param Locale $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @return string
     */
    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    /**
     * @return string
     */
    public function getMetaKeywords()
    {
        return $this->metaKeywords;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getH1()
    {
        return $this->h1;
    }

    /**
     * @return Menu
     */
    public function getMenu()
    {
        return $this->menu;
    }

    /**
     * @return Locale
     */
    public function getLocale()
    {
        return $this->locale;
    }
}