<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Structure\Repository;

use Dravencms\Structure\CmsActionOption;
use Dravencms\Structure\ICmsActionOption;
use Dravencms\Structure\ICmsComponentRepository;

class SearchCmsRepository implements ICmsComponentRepository
{
    /**
     * @param string $componentAction
     * @return ICmsActionOption[]
     */
    public function getActionOptions(string $componentAction)
    {
        switch ($componentAction)
        {
            case 'Overview':
                return null;
                break;

            default:
                return false;
                break;
        }
    }

    /**
     * @param string $componentAction
     * @param array $parameters
     * @return null
     */
    public function getActionOption(string $componentAction, array $parameters)
    {
        switch ($componentAction)
        {
            case 'Overview':
                return new CmsActionOption('Search');
                break;

            default:
                return null;
                break;
        }
    }
}