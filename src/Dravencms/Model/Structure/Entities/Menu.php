<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Structure\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
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
class Menu extends Nette\Object implements IMenu
{
    use Identifier;
    use TimestampableEntity;

    /**
     * @var string
     * @Gedmo\Translatable
     * @ORM\Column(type="string",length=255,nullable=false)
     */
    private $name;

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
     * @Gedmo\Slug(handlers={
     *      @Gedmo\SlugHandler(class="Gedmo\Sluggable\Handler\TreeSlugHandler", options={
     *          @Gedmo\SlugHandlerOption(name="parentRelationField", value="parent"),
     *          @Gedmo\SlugHandlerOption(name="separator", value="/")
     *      })
     * }, fields={"name"})
     * @Doctrine\ORM\Mapping\Column(length=255, unique=true,nullable=false)
     */
    private $slug;

    /**
     * @var string
     * @Gedmo\Translatable
     * @ORM\Column(type="string",length=255)
     */
    private $metaDescription;

    /**
     * @var string
     * @Gedmo\Translatable
     * @ORM\Column(type="string",length=255)
     */
    private $metaKeywords;

    /**
     * @var string
     * @ORM\Column(type="string",length=255)
     */
    private $metaRobots;

    /**
     * @var string
     * @Gedmo\Translatable
     * @ORM\Column(type="string",length=255)
     */
    private $title;

    /**
     * @var string
     * @Gedmo\Translatable
     * @ORM\Column(type="string",length=255)
     */
    private $h1;

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
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     * and it is not necessary because globally locale can be set in listener
     */
    private $locale;

    /**
     * @var integer
     * @ORM\Column(type="integer",nullable=true)
     */
    private $oldId;

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
     * @Gedmo\TreeRoot
     * @ORM\Column(name="root", type="integer", nullable=true)
     */
    private $root;

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
     * Menu constructor.
     * @param callable $parameterSumGenerator
     * @param $name
     * @param $metaDescription
     * @param $metaKeywords
     * @param $metaRobots
     * @param $title
     * @param $h1
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
        $name,
        $metaDescription,
        $metaKeywords,
        $metaRobots,
        $title,
        $h1,
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
        $isContent = true
    ) {
        $this->name = $name;
        $this->isActive = $isActive;
        $this->isHidden = $isHidden;
        $this->metaDescription = $metaDescription;
        $this->metaKeywords = $metaKeywords;
        $this->metaRobots = $metaRobots;
        $this->title = $title;
        $this->h1 = $h1;
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
    }

    /**
     * @param IMenu|null $parent
     * @return $this
     */
    public function setParent(IMenu $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @param boolean $isHomePage
     */
    public function setIsHomePage($isHomePage)
    {
        $this->isHomePage = $isHomePage;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
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
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * @param string $metaDescription
     */
    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;
    }

    /**
     * @param string $metaKeywords
     */
    public function setMetaKeywords($metaKeywords)
    {
        $this->metaKeywords = $metaKeywords;
    }

    /**
     * @param string $metaRobots
     */
    public function setMetaRobots($metaRobots)
    {
        $this->metaRobots = $metaRobots;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @param string $h1
     */
    public function setH1($h1)
    {
        $this->h1 = $h1;
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
     * @param int $oldId
     */
    public function setOldId($oldId)
    {
        $this->oldId = $oldId;
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
     * @param $locale
     */
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return mixed
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
    public function getRoot()
    {
        return $this->root;
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
    public function getName()
    {
        return $this->name;
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
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @return string
     */
    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    /**
     * @return string
     */
    public function getMetaKeywords()
    {
        return $this->metaKeywords;
    }

    /**
     * @return string
     */
    public function getMetaRobots()
    {
        return $this->metaRobots;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getH1()
    {
        return $this->h1;
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
     * @param mixed $root
     */
    public function setRoot($root)
    {
        $this->root = $root;
    }
}