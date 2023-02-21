<?php declare(strict_types = 1);

namespace Dravencms\Structure;


use Dravencms\Model\Locale\Repository\LocaleRepository;
use Dravencms\Model\Structure\Entities\MenuTranslation;
use Dravencms\Model\Structure\Repository\MenuRepository;
use Dravencms\Model\Structure\Repository\MenuTranslationRepository;
use Nette;
use Nette\Routing\Router;
use Nette\Application\Request;
use Nette\SmartObject;
use Nette\Utils\Strings;

/**
 * Description of SlugRouter
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class SlugRouter implements Router
{
    use SmartObject;

    const PRESENTER_KEY = 'presenter';
    const MODULE_KEY = 'module';

    /** @var array */
    private $xlat;

    /** @var int HOST, PATH, RELATIVE */
    private $type;

    /** @var string  http | https */
    private $scheme;

    /** @var int */
    private $flags;

    /** @var Nette\Http\Url */
    private $lastRefUrl;

    /** @var string */
    private $lastBaseUrl;

    /** @var MenuRepository */
    private $structureMenuRepository;

    /** @var MenuTranslationRepository */
    private $menuTranslationRepository;

    /** @var LocaleRepository */
    private $localeRepository;

    /** @var string */
    private $module;

    /** @var array */
    private $enabledLocaleCodesRuntimeCache = [];
    
    private $defaultLocaleCode = 'en';
    
    /**
     * SlugRouter constructor.
     * @param $mask
     * @param MenuRepository $menuRepository
     * @param MenuTranslationRepository $menuTranslationRepository
     * @param LocaleRepository $localeRepository
     * @param string $module
     */
    public function __construct(
        MenuRepository $menuRepository,
        MenuTranslationRepository $menuTranslationRepository,
        LocaleRepository $localeRepository,
        $module = 'Front'
    )
    {
        $this->structureMenuRepository = $menuRepository;
        $this->menuTranslationRepository = $menuTranslationRepository;
        $this->localeRepository = $localeRepository;
        $this->module = $module;
        
        if ($this->localeRepository->getDefault())
        {
            $this->defaultLocaleCode = $this->localeRepository->getDefault()->getLanguageCode();
        }
    }

    private function getEnabledLocaleCodes(): array {
        if (empty($this->enabledLocaleCodesRuntimeCache)) {
            foreach ($this->localeRepository->getActive() AS $activeLocale) {
                $this->enabledLocaleCodesRuntimeCache[] = $activeLocale->getLanguageCode();
            }
        }
        
        return $this->enabledLocaleCodesRuntimeCache;
    }
    
    
    /**
     * @param Nette\Http\IRequest $httpRequest
     * @return Request|null
     */
    public function match(Nette\Http\IRequest $httpRequest): ?array
    {
        $url = $httpRequest->getUrl();
        $path = $url->getPath();
        
        $params = $httpRequest->getQuery();
        $matches = [];
        // Match path with locale
        if (preg_match('/^\/('.implode('|', $this->getEnabledLocaleCodes()).')\/(\S+)$/i', $path, $matches)) {
            // Url has locale
            $params['locale']= $matches[1];
            $params['slug'] = $matches[2];
        } else if (preg_match('/^\/('.implode('|', $this->getEnabledLocaleCodes()).')$/i', $path, $matches)) {
            // Url has locale but no slug
            $params['locale']= $matches[1];
            $params['slug'] = null;
        } else if (preg_match('/^\/(\S+)/i', $path, $matches)) {
            // There is something that is not allowed locale, make it all slug and go for default
            $params['locale']= $this->defaultLocaleCode;
            $params['slug'] = $matches[1];
        } else {
            $params['locale']= $this->defaultLocaleCode;
            $params['slug'] = null;
        }
               
        $locale = (array_key_exists('locale', $params) ? $params['locale'] : null);

        $foundLocale = $this->localeRepository->getLocaleCache($locale);

        // Find presenter
        /** @var MenuTranslation $pageInfo */        
        list($pageInfo, $advancedParams) = $this->menuTranslationRepository->getOneBySlug($params['slug'], $params, $foundLocale);
        if (!$pageInfo) {
            return null;
        }
        
        if ($advancedParams) {
            foreach ($advancedParams AS $k => $v) {
                $params[$k] = $v;
            }
        }

        if ($this->module) {
            $params[self::PRESENTER_KEY] = str_replace(':'.$this->module.':','', $pageInfo->getPresenter());
        } else {
            $params[self::PRESENTER_KEY] = $pageInfo->getPresenter();
        }

        $params['action'] = $pageInfo->getAction();

        return $params;
    }

    /**
     * Constructs absolute URL from Request object.
     *
     * @return string|NULL
     */
    public function constructUrl(array $params, Nette\Http\UrlScript $refUrl): ?string
    {        
        if ($this->flags & self::ONE_WAY) {
            return null;
        }
        
        $url = $refUrl->getBaseUrl();
        $pageInfo = $this->structureMenuRepository->getOneByPresenterAction(
            ($this->module ? ':' . $this->module . ':' : '') . $params[self::PRESENTER_KEY],
            $params['action']
        );
        
        if ($pageInfo) {
            if (array_key_exists('locale', $params)) {
                $locale = $params['locale'];
                $foundLocale = $this->localeRepository->getLocaleCache($locale);
            } else {
                $foundLocale = null;
            }
            
            if (!$foundLocale)
            {
                $foundLocale = $this->localeRepository->getDefault();
            }

            if ($pageInfo->isHomePage()) {
                if ($foundLocale->isDefault()) {
                    $url .= '';
                } else {
                    $url .= $foundLocale->getLanguageCode();
                }
            } else {
                echo $pageInfo->getId();
                $slug = $this->menuTranslationRepository->getSlug($pageInfo, $foundLocale);

                if ($foundLocale->isDefault()) {
                    $url .= $slug;
                } else {
                    $url .= $foundLocale->getLanguageCode().'/'.$slug;
                }
            }
        } else {
            return null;
        }
        
        
        unset($params['action']);
        unset($params['presenter']);
        unset($params['locale']);
        unset($params['slug']);

        $sep = ini_get('arg_separator.input');
        $query = http_build_query($params, '', $sep ? $sep[0] : '&');
        if ($query != '') { // intentionally ==
            $url .= '?' . $query;
        }

        return $url;

    }
}
