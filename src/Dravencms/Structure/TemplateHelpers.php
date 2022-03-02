<?php declare(strict_types = 1);

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Structure;

use Latte\Engine;
use Latte\Runtime\FilterInfo;
use Nette;

/**
 * Class TemplateHelpers
 * @package Salamek\Cms
 */
class TemplateHelpers
{
    use Nette\SmartObject;

    /**
     * @var Cms
     */
    private $cms;

    /**
     * TemplateHelpers constructor.
     * @param Cms $cms
     */
    public function __construct(Cms $cms): void
    {
        $this->cms = $cms;
    }

    /**
     * @param Engine $engine
     */
    public function register(Engine $engine): void
    {
        if (class_exists('Latte\Runtime\FilterInfo')) {
            $engine->addFilter('cmsLink', [$this, 'cmsLinkFilterAware']);
        } else {
            $engine->addFilter('cmsLink', [$this, 'cmsLink']);
        }
        $engine->addFilter('getCms', [$this, 'getCms']);
    }


    /**
     * @return Cms
     */
    public function getCms(): Cms
    {
        return $this->cms;
    }


    /**
     * @param $name
     * @param array $parameters
     * @return string
     */
    public function cmsLink(string $name, array $parameters = []): string
    {
        return $this->cms->getLinkForMenu($this->cms->findComponentActionPresenter($name, $parameters));
    }

    /**
     * @param FilterInfo $filterInfo
     * @param $name
     * @param array $parameters
     * @return string
     */
    public function cmsLinkFilterAware(FilterInfo $filterInfo, string $name, array $parameters = []): string
    {
        return $this->cms->getLinkForMenu($this->cms->findComponentActionPresenter($name, $parameters));
    }

}
