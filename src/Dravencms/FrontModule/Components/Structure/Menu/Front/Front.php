<?php

namespace Dravencms\FrontModule\Components\Structure\Menu\Front;

use Dravencms\Components\BaseControl\BaseControl;
use Dravencms\Locale\CurrentLocale;
use Dravencms\Model\Structure\Repository\MenuRepository;
use Dravencms\Model\Structure\Repository\MenuTranslationRepository;
use Tracy\Debugger;

class Front extends BaseControl
{
    /** @var MenuRepository */
    private $menuRepository;

    /** @var MenuTranslationRepository */
    private $menuTranslationRepository;

    /** @var CurrentLocale */
    private $currentLocale;

    public function __construct(
        MenuRepository $menuRepository,
        MenuTranslationRepository $menuTranslationRepository,
        CurrentLocale $currentLocale
    )
    {
        parent::__construct();
        $this->menuRepository = $menuRepository;
        $this->menuTranslationRepository = $menuTranslationRepository;
        $this->currentLocale = $currentLocale;
    }

    public function render($options = [])
    {
        $options['class'] = (array_key_exists('class', $options) ? $options['class'] : 'nav navbar-nav');
        $options['subClass'] = (array_key_exists('subClass', $options) ? $options['subClass'] : 'dropdown-menu');

        $template = $this->template;

        $options = [
            'decorate' => true,
            'rootOpen' => function ($tree) use($options) {
                if (count($tree) && ($tree[0]['lvl'] == 0)) {
                    return '<ul class="'.$options['class'].'">';
                } else {
                    return '<ul class="'.$options['subClass'].'" id="menu-item-'.$tree[0]['lvl'].'">';
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
                return '<a href="' . (!empty($node['__children']) && !$node['isContent'] ? '#' : $this->presenter->link($node['presenter'].':'.$node['action'])) . '" '.(!empty($node['__children']) ? ' data-hover="dropdown" data-toggle="dropdown" class="dropdown-toggle" data-close-others="false" data-target="#menu-item-'.$node['id'].'"' : '').'>' . $node['translations'][0]['name'] . ' ' . (!empty($node['__children']) ? '<span class="caret"></span>' : '') . '</a>';
            }
        ];

        $template->htmlTree = $this->menuRepository->getTree($options, $this->currentLocale);

        $template->setFile(__DIR__ . '/front.latte');
        $template->render();
    }
}
