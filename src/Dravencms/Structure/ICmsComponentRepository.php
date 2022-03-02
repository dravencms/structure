<?php declare(strict_types = 1);
namespace Dravencms\Structure;


/**
 * Description of iCmsComponentRepository
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
interface ICmsComponentRepository
{
    /**
     * @param string $componentAction
     * @return ICmsActionOption[]|false|null
     */
    public function getActionOptions(string $componentAction);

    /**
     * @param string $componentAction
     * @param array $parameters
     * @return ICmsActionOption
     */
    public function getActionOption(string $componentAction, array $parameters);
}
