<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Structure\Entities;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Dravencms\Database\Attributes\Identifier;
use Nette;

/**
 * Class MenuContent
 * @package App\Model\Structure\Entities
 * @ORM\Entity
 * @ORM\Table(name="structureMenuContent")
 */
class MenuContent
{
    use Nette\SmartObject;
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
     * MenuContent constructor.
     * @param Menu $menu
     * @param $factory
     * @param array $parameters
     * @param callable $parameterSumGenerator
     */
    public function __construct(Menu $menu, string $factory, array $parameters, callable $parameterSumGenerator)
    {
        $this->setMenu($menu);
        $this->factory = $factory;
        $this->setParameters($parameters, $parameterSumGenerator);
    }

    /**
     * @param $parameters
     * @param callable $parameterSumGenerator
     */
    public function setParameters(array $parameters, callable $parameterSumGenerator): void
    {
        $this->parameters = $parameters;
        $this->parametersSum = $parameterSumGenerator($parameters);
    }

    /**
     * @param Menu $menu
     */
    public function setMenu(Menu $menu): void
    {
        $menu->addMenuContent($this);
        $this->menu = $menu;
    }
    
    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return Menu
     */
    public function getMenu(): Menu
    {
        return $this->menu;
    }

    /**
     * @return string
     */
    public function getFactory(): string
    {
        return $this->factory;
    }
}