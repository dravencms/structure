<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Structure\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Nette;

/**
 * Class Menu
 * @package App\Model\Structure\Entities
 * @Gedmo\Tree(type="nested")
 * @ORM\Entity(repositoryClass="Gedmo\Tree\Entity\Repository\NestedTreeRepository")
 * @ORM\Table(name="structureMenu")
 */
class Menu extends Nette\Object
{
    use Identifier;
    use TimestampableEntity;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false, unique=true)
     */
    private $identifier;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isHidden;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isActive;

    /**
     * @var string
     * @ORM\Column(type="string",length=255)
     */
    private $metaRobots;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isHomePage;

    /**
     * @var double
     * @ORM\Column(type="float", nullable=true)
     */
    private $sitemapPriority;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isSitemap;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isShowH1;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $latteTemplate;

    /**
     * @var string
     * @ORM\Column(type="string",length=500,nullable=true)
     */
    private $presenter;

    /**
     * @var string
     * @ORM\Column(type="string",length=500,nullable=true)
     */
    private $action;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isSystem;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isContent;

    /**
     * @var array
     * @ORM\Column(type="json_array", nullable=false)
     */
    private $parameters;

    /**
     * @var string
     * @ORM\Column(type="string",length=32, nullable=true)
     */
    private $parametersSum;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isRegularExpression;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isRegularExpressionMatchArguments;

    /**
     * @var string
     * @ORM\Column(type="string",length=255)
     */
    private $layoutName;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     */
    private $lft;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     */
    private $rgt;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    private $lvl;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Menu", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="Menu", mappedBy="parent")
     */
    private $children;

    /**
     * @var ArrayCollection|MenuContent[]
     * @ORM\OneToMany(targetEntity="MenuContent", mappedBy="menu",cascade={"remove"})
     */
    private $menuContents;

    /**
     * @var ArrayCollection|MenuTranslation[]
     * @ORM\OneToMany(targetEntity="MenuTranslation", mappedBy="menu",cascade={"persist", "remove"})
     */
    private $translations;

    /**
     * Menu constructor.
     * @param callable $parameterSumGenerator
     * @param $metaRobots
     * @param bool $isActive
     * @param bool $isHidden
     * @param bool $isHomePage
     * @param float $sitemapPriority
     * @param bool $isSitemap
     * @param bool $isShowH1
     * @param null $presenter
     * @param null $action
     * @param bool $isSystem
     * @param array $parameters
     * @param bool $isRegularExpression
     * @param bool $isRegularExpressionMatchArguments
     * @param string $layoutName
     * @param bool $isContent
     */
    public function __construct(
        callable $parameterSumGenerator,
        $identifier,
        $metaRobots,
        $isActive = true,
        $isHidden = false,
        $isHomePage = false,
        $sitemapPriority = 0.5,
        $isSitemap = true,
        $isShowH1 = true,
        $presenter = null,
        $action = null,
        $isSystem = false,
        array $parameters = [],
        $isRegularExpression = false,
        $isRegularExpressionMatchArguments = false,
        $layoutName = 'layout',
        $isContent = false
    ) {
        $this->identifier = $identifier;
        $this->isActive = $isActive;
        $this->isHidden = $isHidden;
        $this->metaRobots = $metaRobots;
        $this->isHomePage = $isHomePage;
        $this->sitemapPriority = $sitemapPriority;
        $this->isSitemap = $isSitemap;
        $this->isShowH1 = $isShowH1;
        $this->presenter = $presenter;
        $this->action = $action;
        $this->isSystem = $isSystem;
        $this->setParameters($parameters, $parameterSumGenerator);
        $this->isRegularExpression = $isRegularExpression;
        $this->isRegularExpressionMatchArguments = $isRegularExpressionMatchArguments;
        $this->layoutName = $layoutName;
        $this->isContent = $isContent;

        $this->menuContents = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    /**
     * @param Menu|null $parent
     * @return $this
     */
    public function setParent(Menu $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @param boolean $isHomePage
     */
    public function setIsHomePage($isHomePage)
    {
        $this->isHomePage = $isHomePage;
    }

    /**
     * @param boolean $isHidden
     */
    public function setIsHidden($isHidden)
    {
        $this->isHidden = $isHidden;
    }

    /**
     * @param boolean $isActive
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

    /**
     * @param string $metaRobots
     */
    public function setMetaRobots($metaRobots)
    {
        $this->metaRobots = $metaRobots;
    }

    /**
     * @param float $sitemapPriority
     */
    public function setSitemapPriority($sitemapPriority)
    {
        $this->sitemapPriority = $sitemapPriority;
    }

    /**
     * @param boolean $isSitemap
     */
    public function setIsSitemap($isSitemap)
    {
        $this->isSitemap = $isSitemap;
    }

    /**
     * @param boolean $isShowH1
     */
    public function setIsShowH1($isShowH1)
    {
        $this->isShowH1 = $isShowH1;
    }

    /**
     * @param string $latteTemplate
     */
    public function setLatteTemplate($latteTemplate)
    {
        $this->latteTemplate = $latteTemplate;
    }

    /**
     * @param string $presenter
     */
    public function setPresenter($presenter)
    {
        $this->presenter = $presenter;
    }

    /**
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @param boolean $isSystem
     */
    public function setIsSystem($isSystem)
    {
        $this->isSystem = $isSystem;
    }

    /**
     * @param array $parameters
     * @param callable $parameterSumGenerator
     */
    public function setParameters($parameters, callable $parameterSumGenerator)
    {
        $this->parameters = $parameters;
        $this->parametersSum = $parameterSumGenerator($parameters);
    }

    /**
     * @param boolean $isRegularExpression
     */
    public function setIsRegularExpression($isRegularExpression)
    {
        $this->isRegularExpression = $isRegularExpression;
    }

    /**
     * @param boolean $isRegularExpressionMatchArguments
     */
    public function setIsRegularExpressionMatchArguments($isRegularExpressionMatchArguments)
    {
        $this->isRegularExpressionMatchArguments = $isRegularExpressionMatchArguments;
    }

    /**
     * @param string $layoutName
     */
    public function setLayoutName($layoutName)
    {
        $this->layoutName = $layoutName;
    }

    /**
     * @param MenuContent $menuContent
     */
    public function addMenuContent(MenuContent $menuContent)
    {
        if ($this->menuContents->contains($menuContent))
        {
            return;
        }
        $this->menuContents->add($menuContent);
        $menuContent->setMenu($this);

        $this->isContent = ($this->menuContents->count() > 0);
    }

    /**
     * @param MenuContent $menuContent
     */
    public function removeMenuContent(MenuContent $menuContent)
    {
        if (!$this->menuContents->contains($menuContent))
        {
            return;
        }

        $this->menuContents->removeElement($menuContent);
        //$menuContent->setMenu(null); //!FIXME NEEDED ???

        $this->isContent = ($this->menuContents->count() > 0);
    }

    /**
     * @param boolean $isContent
     */
    public function setIsContent($isContent)
    {
        $this->isContent = $isContent;
    }

    /**
     * @return boolean
     */
    public function isHomePage()
    {
        return $this->isHomePage;
    }

    /**
     * @return Menu
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return Menu
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return mixed
     */
    public function getLvl()
    {
        return $this->lvl;
    }

    /**
     * @return string
     */
    public function getLatteTemplate()
    {
        return $this->latteTemplate;
    }

    /**
     * @return boolean
     */
    public function isHidden()
    {
        return $this->isHidden;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * @return string
     */
    public function getLayoutName()
    {
        return $this->layoutName;
    }

    /**
     * @return boolean
     */
    public function isIsHidden()
    {
        return $this->isHidden;
    }

    /**
     * @return boolean
     */
    public function isIsActive()
    {
        return $this->isActive;
    }

    /**
     * @return string
     */
    public function getMetaRobots()
    {
        return $this->metaRobots;
    }

    /**
     * @return boolean
     */
    public function isIsHomePage()
    {
        return $this->isHomePage;
    }

    /**
     * @return float
     */
    public function getSitemapPriority()
    {
        return $this->sitemapPriority;
    }

    /**
     * @return boolean
     */
    public function isSitemap()
    {
        return $this->isSitemap;
    }

    /**
     * @return boolean
     */
    public function isShowH1()
    {
        return $this->isShowH1;
    }

    /**
     * @return string
     */
    public function getPresenter()
    {
        return $this->presenter;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return boolean
     */
    public function isSystem()
    {
        return $this->isSystem;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return boolean
     */
    public function isRegularExpression()
    {
        return $this->isRegularExpression;
    }

    /**
     * @return boolean
     */
    public function isRegularExpressionMatchArguments()
    {
        return $this->isRegularExpressionMatchArguments;
    }

    /**
     * @return MenuContent[]|ArrayCollection
     */
    public function getMenuContents()
    {
        return $this->menuContents;
    }

    /**
     * @return boolean
     */
    public function isContent()
    {
        return $this->isContent;
    }

    /**
     * @return mixed
     */
    public function getLft()
    {
        return $this->lft;
    }

    /**
     * @return mixed
     */
    public function getRgt()
    {
        return $this->rgt;
    }

    /**
     * @return ArrayCollection|MenuTranslation[]
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }
}
