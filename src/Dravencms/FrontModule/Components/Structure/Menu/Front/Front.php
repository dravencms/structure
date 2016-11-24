<?php

namespace Dravencms\FrontModule\Components\Structure\Menu\Front;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Dravencms\Model\Structure\Repository\MenuRepository;

class Front extends BaseControl
{
    /** @var MenuRepository */
    private $menuRepository;

    /** @var LocaleRepository */
    private $localeRepository;

    public function __construct(MenuRepository $menuRepository, LocaleRepository $localeRepository)
    {
        $this->menuRepository = $menuRepository;
        $this->localeRepository = $localeRepository;
    }

    public function render()
    {
        $template = $this->template;
        $menuLinkingType = 'normal'; //!FIXME INTO CONFIG

        $options = [
            'decorate' => true,
            'rootOpen' => function ($tree) {
                if (count($tree) && ($tree[0]['lvl'] == 0)) {
                    return '<ul class="nav navbar-nav">';
                } else {
                    return '<ul class="dropdown-menu" id="menu-item-'.$tree[0]['root'].'">';
                }
            },
            'rootClose' => function ($tree) {
                if (count($tree) && ($tree[0]['lvl'] == 0)) {
                    return '</ul>';
                } else {
                    return '</ul>';
                }
            },
            'childOpen' => function ($tree) {
                $active = false;
                if (array_key_exists('__children', $tree) && count($tree['__children'])) {
                    foreach ($tree['__children'] AS $child) {
                        if ($this->presenter->isLinkCurrent($child['presenter'].':'.$child['action'])) {
                            $active = true;
                        }
                    }
                } else {
                    $active = $this->presenter->isLinkCurrent($tree['presenter'].':'.$tree['action']);
                }

                return '<li class="'.($tree['lvl'] == 0 ? 'nav-item' : '').' ' . ($active ? 'active ' : '') . (!empty($tree['__children']) ? 'dropdown': '') . '">';
            },
            'childClose' => '</li>',
            'nodeDecorator' => function ($node) {
                return '<a href="' . (!empty($node['__children']) && !$node['isContent'] ? '#' : $this->presenter->link($node['presenter'].':'.$node['action'])) . '" '.(!empty($node['__children']) ? ' data-hover="dropdown" data-toggle="dropdown" class="dropdown-toggle" data-close-others="false" data-target="#menu-item-'.$node['id'].'"' : '').'>' . $node['name'] . ' ' . (!empty($node['__children']) ? '<span class="caret"></span>' : '') . '</a>';
            }
        ];

        $template->htmlTree = $this->menuRepository->getTree($options, $this->localeRepository->getCurrentLocale());

        $template->setFile(__DIR__ . '/front-' . $menuLinkingType . '.latte');
        $template->render();
    }
}
