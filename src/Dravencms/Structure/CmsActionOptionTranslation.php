<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: sadam
 * Date: 27.2.17
 * Time: 6:45
 */

namespace Dravencms\Structure;

use Dravencms\Model\Locale\Entities\Locale;

class CmsActionOptionTranslation
{
    /** @var Locale */
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
    public function __construct(Locale $locale, string $name, string $title, string $metaDescription, string $metaKeywords, string $slug = null)
    {
        $this->locale = $locale;
        $this->name = $name;
        $this->title = $title;
        $this->metaDescription = $metaDescription;
        $this->metaKeywords = $metaKeywords;
        $this->slug = $slug;
    }


    /**
     * @param Locale $locale
     */
    public function setLocale(Locale $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
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
     * @param null $slug
     * @return void
     */
    public function setSlug(slug $slug = null): void
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
    public function getLocale(): Locale
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