<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Structure\Repository;

use Dravencms\Model\Structure\Entities\Menu;
use Doctrine\Common\Collections\ArrayCollection;
use Kdyby\Doctrine\EntityManager;
use Nette;
use Salamek\Cms\CmsActionOption;
use Salamek\Cms\ICmsActionOption;
use Salamek\Cms\ICmsComponentRepository;
use Salamek\Cms\Models\ILocale;

class SearchCmsRepository implements ICmsComponentRepository
{
    /**
     * @param string $componentAction
     * @return ICmsActionOption[]
     */
    public function getActionOptions($componentAction)
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
    public function getActionOption($componentAction, array $parameters)
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