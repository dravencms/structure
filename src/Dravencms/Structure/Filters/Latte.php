<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Salamek\Structure\Filters;

use Dravencms\Structure\Structure;

/**
 * Class Latte
 */
class Latte
{
    /** @var Structure */
    private $structure;

    /**
     * Latte constructor.
     * @param Structure $structure
     */
    public function __construct(Structure $structure)
    {
        $this->structure = $structure;
    }

   /**
     * @param $name
     * @param array $parameters
     * @return string
     */
    public function cmsLink(string $name, array $parameters = []): string
    {
        return $this->structure->getLinkForMenu($this->structure->findComponentActionPresenter($name, $parameters));
    }

    /**
     * @return Cms
     */
    public function getStructure(): Structure
    {
        user_error('getStructure is deprecated', E_USER_DEPRECATED);
        return $this->structure;
    }
}