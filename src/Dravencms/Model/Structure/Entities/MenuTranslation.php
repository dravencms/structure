<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Structure\Entities;

use Doctrine\ORM\Mapping as ORM;
use Dravencms\Model\Locale\Entities\Locale;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Dravencms\Database\Attributes\Identifier;
use Nette;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * Class MenuTranslation
 * @package App\Model\Structure\Entities
 * @ORM\Entity
 * @ORM\Table(name="structureMenuTranslation", uniqueConstraints={@UniqueConstraint(name="slug_unique", columns={"slug_sum", "locale_id"})})
 */
class MenuTranslation
{
    use Nette\SmartObject;
    use Identifier;
    use TimestampableEntity;

    /**
     * @var string
     * @ORM\Column(type="string",length=255,nullable=false)
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     */
    private $slug;

    /**
     * @var string
     * @ORM\Column(type="string",length=32)
     */
    private $slugSum;

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
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $customUrl;

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
    public function __construct(
            Menu $menu, 
            Locale $locale, 
            string $name, 
            string $metaDescription, 
            string $metaKeywords, 
            string $title, 
            string $h1, 
            callable $slugGenerator, 
            string $customUrl = null
            )
    {
        $this->name = $name;
        $this->metaDescription = $metaDescription;
        $this->metaKeywords = $metaKeywords;
        $this->title = $title;
        $this->h1 = $h1;
        $this->menu = $menu;
        $this->locale = $locale;
        $this->customUrl = $customUrl;
        $this->generateSlug($slugGenerator);
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param string $slugSum
     */
    public function setSlugSum(string $slugSum): void
    {
        $this->slugSum = $slugSum;
    }

    public function generateSlug(callable  $slugGenerator): void
    {
        $this->setSlug($slugGenerator($this));
    }

    /**
     * @param string $slug
     */
    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
        $this->setSlugSum(md5($slug));
    }

    /**
     * @param string $metaDescription
     */
    public function setMetaDescription(string $metaDescription): void
    {
        $this->metaDescription = $metaDescription;
    }

    /**
     * @param string $metaKeywords
     */
    public function setMetaKeywords(string $metaKeywords): void
    {
        $this->metaKeywords = $metaKeywords;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @param string $h1
     */
    public function setH1(string $h1): void
    {
        $this->h1 = $h1;
    }

    /**
     * @param null|string $customUrl
     */
    public function setCustomUrl(string $customUrl = null): void
    {
        $this->customUrl = $customUrl;
    }

    /**
     * @param Menu $menu
     */
    public function setMenu(Menu $menu): void
    {
        $this->menu = $menu;
    }

    /**
     * @param Locale $locale
     */
    public function setLocale(Locale $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @return string
     */
    public function getMetaDescription(): string
    {
        return $this->metaDescription;
    }

    /**
     * @return string
     */
    public function getMetaKeywords(): string
    {
        return $this->metaKeywords;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getH1(): string
    {
        return $this->h1;
    }

    /**
     * @return Menu
     */
    public function getMenu(): Menu
    {
        return $this->menu;
    }

    /**
     * @return Locale
     */
    public function getLocale(): Locale
    {
        return $this->locale;
    }

    /**
     * @return string
     */
    public function getSlugSum(): string
    {
        return $this->slugSum;
    }

    /**
     * @return null|string
     */
    public function getCustomUrl(): ?string
    {
        return $this->customUrl;
    }
}