<?php declare(strict_types = 1);

namespace Dravencms\Structure\Seo;

use Dravencms\Model\Structure\Entities\Menu;
use Dravencms\Model\Structure\Repository\MenuRepository;
use Dravencms\Seo\Robots\RobotsDirective;
use Dravencms\Seo\Robots\RobotsProviderInterface;
use Dravencms\Seo\Sitemap\SitemapAlternate;
use Dravencms\Seo\Sitemap\SitemapEntry;
use Dravencms\Seo\Sitemap\SitemapProviderInterface;

final class StructureSeoProvider implements SitemapProviderInterface, RobotsProviderInterface
{
    public function __construct(private MenuRepository $menuRepository)
    {
    }

    public function getSitemapEntries(): iterable
    {
        foreach ($this->menuRepository->getSitemap() as $menu) {
            $destination = $this->getDestination($menu);
            if ($destination === null) {
                continue;
            }

            $alternates = [];
            foreach ($menu->getTranslations() as $translation) {
                $languageCode = $translation->getLocale()->getLanguageCode();
                $parameters = $menu->getParameters();
                $parameters['locale'] = $languageCode;
                $alternates[] = new SitemapAlternate($languageCode, $destination, $parameters);
            }

            yield new SitemapEntry(
                $destination,
                $menu->getParameters(),
                $menu->getUpdatedAt(),
                'always',
                $menu->getSitemapPriority(),
                $alternates
            );
        }
    }

    public function getRobotsDirectives(): iterable
    {
        foreach ($this->menuRepository->getSitemap(false) as $menu) {
            $destination = $this->getDestination($menu);
            if ($destination !== null) {
                yield RobotsDirective::forDestination('Disallow', $destination, $menu->getParameters());
            }
        }
    }

    private function getDestination(Menu $menu): ?string
    {
        $presenter = $menu->getPresenter();
        $action = $menu->getAction();

        return $presenter !== null && $action !== null
            ? $presenter . ':' . $action
            : null;
    }
}
