<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Structure;

use Doctrine\DBAL\Exception\TableNotFoundException;
use Dravencms\Application\IRouterFactory;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Dravencms\Model\Structure\Repository\MenuTranslationRepository;
use Nette\Application\Routers\RouteList;
use Dravencms\Model\Structure\Repository\MenuRepository;

/**
 * Class RouteFactory
 * @package Salamek\Cms
 */
class RouteFactory implements IRouterFactory
{
    /** @var MenuRepository @inject */
    private $structureMenuRepository;

    /** @var MenuTranslationRepository */
    private $menuTranslationRepository;

    /** @var LocaleRepository */
    private $localeRepository;

    /**
     * RouteFactory constructor.
     * @param MenuRepository $structureMenuRepository
     * @param MenuTranslationRepository $menuTranslationRepository
     * @param LocaleRepository $localeRepository
     */
    public function __construct(
        MenuRepository $structureMenuRepository,
        MenuTranslationRepository $menuTranslationRepository,
        LocaleRepository $localeRepository
    )
    {
        $this->structureMenuRepository = $structureMenuRepository;
        $this->menuTranslationRepository = $menuTranslationRepository;
        $this->localeRepository = $localeRepository;
    }

    /**
     * @return \Nette\Application\IRouter
     */
    public function createRouter()
    {
        $router = new RouteList();

        $frontEnd = new RouteList('Front');

        try
        {
            if ($this->localeRepository->getDefault())
            {
                $defaultLanguageCode = $this->localeRepository->getDefault()->getLanguageCode();
            }
            else
            {
                $defaultLanguageCode = 'en';
            }

            $frontEnd->add(new SlugRouter('[<locale='.$defaultLanguageCode.' [a-z]{2}>/][<slug .*>]', $this->structureMenuRepository, $this->menuTranslationRepository, $this->localeRepository));
        }
        catch(TableNotFoundException $e)
        {
            //!FIXME Ignore missing table, this is only way i can find to prevent this  part of code from crashing console when database is not created
        }

        $router->add($frontEnd);
        
        return $router;
    }
}