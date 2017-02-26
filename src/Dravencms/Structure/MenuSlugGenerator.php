<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Structure;


use Doctrine\Common\Collections\Criteria;
use Dravencms\Model\Structure\Entities\MenuTranslation;
use Nette\Utils\Strings;

class MenuSlugGenerator
{
    public function slugify(MenuTranslation $menuTranslation)
    {
        $slugParts = [];
        $menu = $menuTranslation->getMenu();
        if ($menu->getParent())
        {
            $criteria = Criteria::create()->where(Criteria::expr()->eq("locale", $menuTranslation->getLocale()));
            $results = $menu->getParent()->getTranslations()->matching($criteria);
            //We should get only single result
            if (count($results))
            {
                /** @var MenuTranslation $result */
                $result = $results[0];
                $slugParts[] = $result->getSlug();
            }
        }

        $slugParts[] = Strings::webalize($menuTranslation->getName());
        
        return implode('/', $slugParts);
    }
}