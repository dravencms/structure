<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: sadam
 * Date: 27.2.17
 * Time: 6:45
 */

namespace Dravencms\Structure;

use Dravencms\Model\Locale\Entities\ILocale;
use Nette\Utils\Strings;

class CmsActionOptionTranslation implements ICmsActionOptionTranslation
{
    /** @var ILocale */
    private $locale;

    /** @var string */
    private $name;

    /** @var string */
    private $title;

    /** @var string */
    private $metaDescription;

    /** @var string */
    private $metaKeywords;

    /** @var string|null */
    private $slug = null;

    /**
     * CmsActionOptionTranslation constructor.
     * @param $locale
     * @param $name
     * @param $title
     * @param $metaDescription
     * @param $metaKeywords
     * @param $slug
     */
    public function __construct(ILocale $locale, string $name, string $title, string $metaDescription, string $metaKeywords, string $slug = null)
    {
        $this->locale = $locale;
        $this->setName($name);
        $this->setTitle($title);
        $this->setMetaDescription($metaDescription);
        $this->setMetaKeywords($metaKeywords);
        $this->slug = $slug;
    }


    /**
     * @param ILocale $locale
     */
    public function setLocale(ILocale $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = Strings::truncate($name, 255);
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = Strings::truncate($title, 255);
    }

    /**
     * @param string $metaDescription
     */
    public function setMetaDescription(string $metaDescription): void
    {
        $this->metaDescription = Strings::truncate($metaDescription, 255);
    }

    /**
     * @param string $metaKeywords
     */
    public function setMetaKeywords(string $metaKeywords): void
    {
        $this->metaKeywords = Strings::truncate($metaKeywords, 255);
    }
    
    /**
     * @param null $slug
     * @return void
     */
    public function setSlug(string $slug = null): void
    {
        $this->slug = $slug;
    }

    /**
     * @return mixed
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return mixed
     */
    public function getMetaDescription(): string
    {
        return $this->metaDescription;
    }

    /**
     * @return mixed
     */
    public function getMetaKeywords(): string
    {
        return $this->metaKeywords;
    }

    /**
     * @return mixed
     */
    public function getLocale(): ILocale
    {
        return $this->locale;
    }

    /**
     * @return null|string
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

}

