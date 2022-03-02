<?php

namespace Dravencms\Structure;
use Salamek\Cms\Models\ILocale;

/**
 * Description of ICmsActionOptionTranslation
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
interface ICmsActionOptionTranslation
{
    /**
     * @param ILocale $locale
     * @return void
     */
    public function setLocale(ILocale $locale);

    /**
     * @param string $name
     * @return void
     */
    public function setName($name);

    /**
     * @param string $title
     * @return void
     */
    public function setTitle($title);

    /**
     * @param string $metaDescription
     * @return void
     */
    public function setMetaDescription($metaDescription);

    /**
     * @param string $metaKeywords
     * @return void
     */
    public function setMetaKeywords($metaKeywords);

    /**
     * @param null|string $slug
     * @return mixed
     */
    public function setSlug($slug = null);

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @return string
     */
    public function getMetaDescription();

    /**
     * @return string
     */
    public function getMetaKeywords();

    /**
     * @return ILocale
     */
    public function getLocale();

    /**
     * @return string|null
     */
    public function getSlug();
}
