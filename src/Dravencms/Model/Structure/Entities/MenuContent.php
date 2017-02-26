<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Structure\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Nette;

/**
 * Class MenuContent
 * @package App\Model\Structure\Entities
 * @ORM\Entity
 * @ORM\Table(name="structureMenuContent")
 */
class MenuContent extends Nette\Object
{
    use Identifier;
    use TimestampableEntity;

    /**
     * @var Menu
     * @ORM\ManyToOne(targetEntity="Menu", inversedBy="menuContents")
     * @ORM\JoinColumn(name="menu_id", referencedColumnName="id")
     */
    private $menu;
    
    /**
     * @var string
     * @ORM\Column(type="string",length=255, nullable=true)
     */
    private $factory;

    /**
     * @var array
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $parameters;

    /**
     * @var string
     * @ORM\Column(type="string",length=32, nullable=true)
     */
    private $parametersSum;

    /**
     * @var integer
     * @ORM\Column(type="integer",nullable=true)
     */
    private $oldId;

    /**
     * MenuContent constructor.
     * @param Menu $menu
     * @param $factory
     * @param array $parameters
     * @param callable $parameterSumGenerator
     */
    public function __construct(Menu $menu, $factory, array $parameters, callable $parameterSumGenerator)
    {
        $this->setMenu($menu);
        $this->factory = $factory;
        $this->setParameters($parameters, $parameterSumGenerator);
    }

    /**
     * @param $parameters
     * @param callable $parameterSumGenerator
     */
    public function setParameters($parameters, callable $parameterSumGenerator)
    {
        $this->parameters = $parameters;
        $this->parametersSum = $parameterSumGenerator($parameters);
    }

    /**
     * @param Menu $menu
     */
    public function setMenu(Menu $menu)
    {
        $menu->addMenuContent($this);
        $this->menu = $menu;
    }
    
    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return Menu
     */
    public function getMenu()
    {
        return $this->menu;
    }

    /**
     * @return string
     */
    public function getFactory()
    {
        return $this->factory;
    }
}