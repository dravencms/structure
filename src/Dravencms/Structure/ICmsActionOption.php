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
    public function setIdentifier($identifier);

    /**
     * @param string $metaRobots
     * @return void
     */
    public function setMetaRobots($metaRobots);

    /**
     * @param array $parameters
     * @return void
     */
    public function setParameters(array $parameters);

    /**
     * @param ICmsActionOptionTranslation $cmsActionOptionTranslation
     * @return mixed
     */
    public function addTranslation(ICmsActionOptionTranslation $cmsActionOptionTranslation);

    /**
     * @return string
     */
    public function getMetaRobots();

    /**
     * @return array
     */
    public function getParameters();

    /**
     * @param $name
     * @return mixed
     */
    public function getParameter($name);

    /**
     * @return string
     */
    public function getIdentifier();

    /**
     * @return ICmsActionOptionTranslation[]
     */
    public function getTranslations();

    /**
     * @param $default
     * @return string
     */
    public function getTemplatePath($default);
}
