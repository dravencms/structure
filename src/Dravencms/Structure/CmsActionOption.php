<?php declare(strict_types = 1);

/*
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301  USA
 */

namespace Dravencms\Structure;

/**
 * Class CmsActionOption
 * @package Salamek\Cms
 */
class CmsActionOption implements ICmsActionOption
{
    /** @var string */
    private $identifier;

    /** @var string */
    private $metaRobots = 'index, follow';

    /** @var array */
    private $parameters = [];

    /** @var ICmsActionOptionTranslation[] */
    private $translations = [];

    /** @var null|string */
    private $templatePath = null;

    /**
     * CmsActionOption constructor.
     * @param $identifier
     * @param array $parameters
     * @param string $metaRobots
     */
    public function __construct(string $identifier, array $parameters = [], string $metaRobots = 'index, follow')
    {
        $this->identifier = $identifier;
        $this->parameters = $parameters;
        $this->metaRobots = $metaRobots;
    }

    /**
     * @param $templatePath
     */
    public function setTemplatePath(string $templatePath): void
    {
        $this->templatePath = $templatePath;
    }

    /**
     * @param array $parameters
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    /**
     * @param string $metaRobots
     */
    public function setMetaRobots(string $metaRobots): void
    {
        $this->metaRobots = $metaRobots;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }
    
    /**
     * @return string
     */
    public function getMetaRobots(): string
    {
        return $this->metaRobots;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getParameter(string $name)
    {
        if (!array_key_exists($name, $this->parameters))
        {
            throw new \InvalidArgumentException(sprintf('Parameter %s was not found in parameters', $name));
        }

        return $this->parameters[$name];
    }

    /**
     * @param ICmsActionOptionTranslation $cmsActionOptionTranslation
     * @return $this
     */
    public function addTranslation(ICmsActionOptionTranslation $cmsActionOptionTranslation): CmsActionOption
    {
        $this->translations[] = $cmsActionOptionTranslation;
        return $this;
    }

    /**
     * @return ICmsActionOptionTranslation[]
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param $default
     * @return null|string
     */
    public function getTemplatePath(string $default): string
    {
        return ($this->templatePath ? $this->templatePath : $default);
    }
}
