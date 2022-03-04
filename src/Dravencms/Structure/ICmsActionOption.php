<?php

namespace Dravencms\Structure;

/**
 * Description of iCmsActionData
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
interface ICmsActionOption
{
    /**
     * @param string $identifier
     * @return void
     */
    public function setIdentifier(string $identifier): void;

    /**
     * @param string $metaRobots
     * @return void
     */
    public function setMetaRobots(string $metaRobots): void;

    /**
     * @param array $parameters
     * @return void
     */
    public function setParameters(array $parameters): void;

    /**
     * @param ICmsActionOptionTranslation $cmsActionOptionTranslation
     * @return mixed
     */
    public function addTranslation(ICmsActionOptionTranslation $cmsActionOptionTranslation);

    /**
     * @return string
     */
    public function getMetaRobots(): string;

    /**
     * @return array
     */
    public function getParameters(): array;

    /**
     * @param $name
     * @return mixed
     */
    public function getParameter(string $name);

    /**
     * @return string
     */
    public function getIdentifier(): string;

    /**
     * @return ICmsActionOptionTranslation[]
     */
    public function getTranslations();

    /**
     * @param $default
     * @return string
     */
    public function getTemplatePath(string $default);
}
