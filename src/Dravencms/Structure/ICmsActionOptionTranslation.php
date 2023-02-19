<?php declare(strict_types = 1);

namespace Dravencms\Structure;
use Dravencms\Model\Locale\Entities\ILocale;

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
    public function setLocale(ILocale $locale): void;

    /**
     * @param string $name
     * @return void
     */
    public function setName(string $name): void;

    /**
     * @param string $title
     * @return void
     */
    public function setTitle(string $title): void;

    /**
     * @param string $metaDescription
     * @return void
     */
    public function setMetaDescription(string $metaDescription): void;

    /**
     * @param string $metaKeywords
     * @return void
     */
    public function setMetaKeywords(string $metaKeywords): void;

    /**
     * @param null|string $slug
     * @return mixed
     */
    public function setSlug(string $slug = null): void;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getTitle(): string;

    /**
     * @return string
     */
    public function getMetaDescription(): string;

    /**
     * @return string
     */
    public function getMetaKeywords(): string;

    /**
     * @return ILocale
     */
    public function getLocale(): ILocale;

    /**
     * @return string|null
     */
    public function getSlug(): ?string;
}
