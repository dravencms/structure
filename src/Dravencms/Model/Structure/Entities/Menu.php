<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Structure\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Dravencms\Model\Locale\Entities\ILocale;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Dravencms\Database\Attributes\Identifier;

/**
 * Class Menu
 * @package App\Model\Structure\Entities
 * @Gedmo\Tree(type="nested")
 * @ORM\Entity(repositoryClass="Gedmo\Tree\Entity\Repository\NestedTreeRepository")
 * @ORM\Table(name="structureMenu")
 */
class Menu
{
    const TARGET_BLANK = '_blank';
    const TARGET_SELF = '_self';
    const TARGET_PARENT = '_parent';
    const TARGET_TOP = '_top';

    //Uncomment when issue https://github.com/Atlantic18/DoctrineExtensions/issues/1981 is fixed, use Nette\SmartObject;
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
     * @var string
     * @ORM\Column(type="string",length=255, nullable=true)
     */
    private $target;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isAutogenerateSlug;

    /**
     * Menu constructor.
     * @param callable $parameterSumGenerator
     * @param $identifier
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
     * @param null|string $target
     */
    public function __construct(
        callable $parameterSumGenerator,
        string $identifier,
        string $metaRobots,
        bool $isActive = true,
        bool $isHidden = false,
        bool $isHomePage = false,
        float $sitemapPriority = 0.5,
        bool $isSitemap = true,
        bool $isShowH1 = true,
        string $presenter = null,
        string $action = null,
        bool $isSystem = false,
        array $parameters = [],
        bool $isRegularExpression = false,
        bool $isRegularExpressionMatchArguments = false,
        string $layoutName = 'layout',
        bool $isContent = false,
        string $target = null,
        bool $autogenerateSlug = true
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
        $this->target = $target;
        $this->isAutogenerateSlug = $autogenerateSlug;

        $this->menuContents = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    /**
     * @param Menu|null $parent
     * @return $this
     */
    public function setParent(Menu $parent = null): void
    {
        $this->parent = $parent;
    }

    /**
     * @param string $identifier
     */
    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    /**
     * @param boolean $isHomePage
     */
    public function setIsHomePage(bool $isHomePage): void
    {
        $this->isHomePage = $isHomePage;
    }

    /**
     * @param boolean $isHidden
     */
    public function setIsHidden(bool $isHidden): void
    {
        $this->isHidden = $isHidden;
    }

    /**
     * @param boolean $isActive
     */
    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    /**
     * @param string $metaRobots
     */
    public function setMetaRobots(string $metaRobots): void
    {
        $this->metaRobots = $metaRobots;
    }

    /**
     * @param float $sitemapPriority
     */
    public function setSitemapPriority(float $sitemapPriority): void
    {
        $this->sitemapPriority = $sitemapPriority;
    }

    /**
     * @param boolean $isSitemap
     */
    public function setIsSitemap(bool $isSitemap): void
    {
        $this->isSitemap = $isSitemap;
    }

    /**
     * @param boolean $isShowH1
     */
    public function setIsShowH1(bool $isShowH1): void
    {
        $this->isShowH1 = $isShowH1;
    }

    /**
     * @param null|string $latteTemplate
     */
    public function setLatteTemplate(string $latteTemplate = null): void
    {
        $this->latteTemplate = $latteTemplate;
    }

    /**
     * @param null|string $presenter
     */
    public function setPresenter(string $presenter = null): void
    {
        $this->presenter = $presenter;
    }

    /**
     * @param null|string $action
     */
    public function setAction(string $action = null): void
    {
        $this->action = $action;
    }

    /**
     * @param boolean $isSystem
     */
    public function setIsSystem(bool $isSystem): void
    {
        $this->isSystem = $isSystem;
    }

    /**
     * @param array $parameters
     * @param callable $parameterSumGenerator
     */
    public function setParameters(array $parameters, callable $parameterSumGenerator): void
    {
        $this->parameters = $parameters;
        $this->parametersSum = $parameterSumGenerator($parameters);
    }

    /**
     * @param boolean $isRegularExpression
     */
    public function setIsRegularExpression(bool $isRegularExpression): void
    {
        $this->isRegularExpression = $isRegularExpression;
    }

    /**
     * @param boolean $isRegularExpressionMatchArguments
     */
    public function setIsRegularExpressionMatchArguments(bool $isRegularExpressionMatchArguments): void
    {
        $this->isRegularExpressionMatchArguments = $isRegularExpressionMatchArguments;
    }

    /**
     * @param string $layoutName
     */
    public function setLayoutName(string $layoutName): void
    {
        $this->layoutName = $layoutName;
    }

    /**
     * @param MenuContent $menuContent
     */
    public function addMenuContent(MenuContent $menuContent): void
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
    public function removeMenuContent(MenuContent $menuContent): void
    {
        if (!$this->menuContents->contains($menuContent))
        {
            return;
        }

        $this->menuContents->removeElement($menuContent);

        $this->isContent = ($this->menuContents->count() > 0);
    }

    /**
     * @param boolean $isContent
     */
    public function setIsContent(bool $isContent): void
    {
        $this->isContent = $isContent;
    }

    /**
     * @param $rgt
     */
    public function setRgt(int $rgt): void
    {
        $this->rgt = $rgt;
    }

    /**
     * @param $lft
     */
    public function setLft(int $lft): void
    {
        $this->lft = $lft;
    }

    /**
     * @param null|string $target
     */
    public function setTarget(string $target = null): void
    {
        $this->target = $target;
    }

    /**
     * @param bool $isAutogenerateSlug
     */
    public function setIsAutogenerateSlug(bool $isAutogenerateSlug): void
    {
        $this->isAutogenerateSlug = $isAutogenerateSlug;
    }

    /**
     * @return boolean
     */
    public function isHomePage(): bool
    {
        return $this->isHomePage;
    }

    /**
     * @return null|Menu
     */
    public function getParent(): ?Menu
    {
        return $this->parent;
    }

    /**
     * @return Menu[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return int
     */
    public function getLvl(): int
    {
        return $this->lvl;
    }

    /**
     * @return null|string
     */
    public function getLatteTemplate(): ?string
    {
        return $this->latteTemplate;
    }

    /**
     * @return boolean
     */
    public function isHidden(): bool
    {
        return $this->isHidden;
    }

    /**
     * @return boolean
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @return string
     */
    public function getLayoutName(): string
    {
        return $this->layoutName;
    }

    /**
     * @return boolean
     */
    public function isIsHidden(): bool
    {
        return $this->isHidden;
    }

    /**
     * @return boolean
     */
    public function isIsActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @return string
     */
    public function getMetaRobots(): string
    {
        return $this->metaRobots;
    }

    /**
     * @return boolean
     */
    public function isIsHomePage(): bool
    {
        return $this->isHomePage;
    }

    /**
     * @return float
     */
    public function getSitemapPriority(): float
    {
        return $this->sitemapPriority;
    }

    /**
     * @return boolean
     */
    public function isSitemap(): bool
    {
        return $this->isSitemap;
    }

    /**
     * @return boolean
     */
    public function isShowH1(): bool
    {
        return $this->isShowH1;
    }

    /**
     * @return null|string
     */
    public function getPresenter(): ?string
    {
        return $this->presenter;
    }

    /**
     * @return null|string
     */
    public function getAction(): ?string
    {
        return $this->action;
    }

    /**
     * @return boolean
     */
    public function isSystem(): bool
    {
        return $this->isSystem;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return boolean
     */
    public function isRegularExpression(): bool
    {
        return $this->isRegularExpression;
    }

    /**
     * @return boolean
     */
    public function isRegularExpressionMatchArguments(): bool
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
    public function isContent(): bool
    {
        return $this->isContent;
    }

    /**
     * @return int
     */
    public function getLft(): int
    {
        return $this->lft;
    }

    /**
     * @return int
     */
    public function getRgt(): int
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
     * @param ILocale $locale
     * @return MenuTranslation
     */
    public function getTranslation(ILocale $locale): ?MenuTranslation
    {
        $criteria = Criteria::create()->where(Criteria::expr()->eq("locale", $locale));
        return $this->getTranslations()->matching($criteria)->first();
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return null|string
     */
    public function getTarget(): ?string
    {
        return $this->target;
    }

    /**
     * @return bool
     */
    public function isAutogenerateSlug(): bool
    {
        return $this->isAutogenerateSlug;
    }

}