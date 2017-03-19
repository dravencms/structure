<?php

namespace Dravencms\Structure;


use Dravencms\Model\Locale\Repository\LocaleRepository;
use Dravencms\Model\Structure\Entities\MenuTranslation;
use Dravencms\Model\Structure\Repository\MenuRepository;
use Dravencms\Model\Structure\Repository\MenuTranslationRepository;
use Nette;
use Nette\Application\IRouter;
use Nette\Application\Request;
use Nette\Application\Routers;
use Nette\Object;
use Nette\Utils\Strings;

/**
 * Description of SlugRouter
 *
 * @author Adam Schubert <adam.schubert@sg1-game.net>
 */
class SlugRouter extends Object implements IRouter
{

    const PRESENTER_KEY = 'presenter';
    const MODULE_KEY = 'module';

    /** @internal url type */
    const HOST = 1,
        PATH = 2,
        RELATIVE = 3;

    /** key used in {@link Route::$styles} or metadata {@link Route::__construct} */
    const VALUE = 'value';
    const PATTERN = 'pattern';
    const FILTER_IN = 'filterIn';
    const FILTER_OUT = 'filterOut';
    const FILTER_TABLE = 'filterTable';
    const FILTER_STRICT = 'filterStrict';

    /** @internal fixity types - how to handle default value? {@link Route::$metadata} */
    const OPTIONAL = 0,
        PATH_OPTIONAL = 1,
        CONSTANT = 2;

    /** @var array */
    public $ignoreParameters = [];

    /** @deprecated */
    public static $defaultFlags = 0;

    /** @var array */
    public static $styles = [
        '#' => [ // default style for path parameters
            self::PATTERN => '[^/]+',
            self::FILTER_OUT => [__CLASS__, 'param2path'],
        ],
        '?#' => [ // default style for query parameters
        ],
        'module' => [
            self::PATTERN => '[a-z][a-z0-9.-]*',
            self::FILTER_IN => [__CLASS__, 'path2presenter'],
            self::FILTER_OUT => [__CLASS__, 'presenter2path'],
        ],
        'presenter' => [
            self::PATTERN => '[a-z][a-z0-9.-]*',
            self::FILTER_IN => [__CLASS__, 'path2presenter'],
            self::FILTER_OUT => [__CLASS__, 'presenter2path'],
        ],
        'action' => [
            self::PATTERN => '[a-z][a-z0-9-]*',
            self::FILTER_IN => [__CLASS__, 'path2action'],
            self::FILTER_OUT => [__CLASS__, 'action2path'],
        ],
        '?module' => [
        ],
        '?presenter' => [
        ],
        '?action' => [
        ],
    ];

    /** @var string */
    private $mask;

    /** @var array */
    private $sequence;

    /** @var string  regular expression pattern */
    private $re;

    /** @var string[]  parameter aliases in regular expression */
    private $aliases;

    /** @var array of [value & fixity, filterIn, filterOut] */
    private $metadata = [];

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
    private $localeRuntimeCache = [];
    
    /**
     * SlugRouter constructor.
     * @param $mask
     * @param MenuRepository $menuRepository
     * @param MenuTranslationRepository $menuTranslationRepository
     * @param LocaleRepository $localeRepository
     * @param string $module
     */
    public function __construct(
        $mask,
        MenuRepository $menuRepository,
        MenuTranslationRepository $menuTranslationRepository,
        LocaleRepository $localeRepository,
        $module = 'Front'
    )
    {
        $this->structureMenuRepository = $menuRepository;
        $this->menuTranslationRepository = $menuTranslationRepository;
        $this->localeRepository = $localeRepository;
        $this->setMask($mask);
        $this->module = $module;
    }

    /**
     * Rename keys in array.
     * @param  array
     * @param  array
     * @return array
     */
    private static function renameKeys($arr, $xlat)
    {
        if (empty($xlat)) {
            return $arr;
        }

        $res = [];
        $occupied = array_flip($xlat);
        foreach ($arr as $k => $v) {
            if (isset($xlat[$k])) {
                $res[$xlat[$k]] = $v;

            } elseif (!isset($occupied[$k])) {
                $res[$k] = $v;
            }
        }
        return $res;
    }

    /**
     * @param $mask
     */
    private function setMask($mask)
    {
        $metadata = [];
        $this->mask = $mask;

        // detect '//host/path' vs. '/abs. path' vs. 'relative path'
        if (preg_match('#(?:(https?):)?(//.*)#A', $mask, $m)) {
            $this->type = self::HOST;
            list(, $this->scheme, $mask) = $m;

        } elseif (substr($mask, 0, 1) === '/') {
            $this->type = self::PATH;

        } else {
            $this->type = self::RELATIVE;
        }

        if (strpbrk($mask, '?<>[]') === false) {
            $this->re = '#' . preg_quote($mask, '#') . '/?\z#A';
            $this->sequence = [$mask];
            $this->metadata = $metadata;
            return;
        }

        // PARSE MASK
        // <parameter-name[=default] [pattern]> or [ or ] or ?...
        $parts = Strings::split($mask, '/<([^<>= ]+)(=[^<> ]*)? *([^<>]*)>|(\[!?|\]|\s*\?.*)/');

        $this->xlat = [];
        $i = count($parts) - 1;

        // PARSE QUERY PART OF MASK
        if (isset($parts[$i - 1]) && substr(ltrim($parts[$i - 1]), 0, 1) === '?') {
            // name=<parameter-name [pattern]>
            $matches = Strings::matchAll($parts[$i - 1], '/(?:([a-zA-Z0-9_.-]+)=)?<([^> ]+) *([^>]*)>/');

            foreach ($matches as list(, $param, $name, $pattern)) { // $pattern is not used
                if (isset(static::$styles['?' . $name])) {
                    $meta = static::$styles['?' . $name];
                } else {
                    $meta = static::$styles['?#'];
                }

                if (isset($metadata[$name])) {
                    $meta = $metadata[$name] + $meta;
                }

                if (array_key_exists(self::VALUE, $meta)) {
                    $meta['fixity'] = self::OPTIONAL;
                }

                unset($meta['pattern']);
                $meta['filterTable2'] = empty($meta[self::FILTER_TABLE]) ? null : array_flip($meta[self::FILTER_TABLE]);

                $metadata[$name] = $meta;
                if ($param !== '') {
                    $this->xlat[$name] = $param;
                }
            }
            $i -= 5;
        }

        // PARSE PATH PART OF MASK
        $brackets = 0; // optional level
        $re = '';
        $sequence = [];
        $autoOptional = true;
        $aliases = [];
        do {
            $part = $parts[$i]; // part of path
            if (strpbrk($part, '<>') !== false) {
                throw new Nette\InvalidArgumentException("Unexpected '$part' in mask '$mask'.");
            }
            array_unshift($sequence, $part);
            $re = preg_quote($part, '#') . $re;
            if ($i === 0) {
                break;
            }
            $i--;

            $part = $parts[$i]; // [ or ]
            if ($part === '[' || $part === ']' || $part === '[!') {
                $brackets += $part[0] === '[' ? -1 : 1;
                if ($brackets < 0) {
                    throw new Nette\InvalidArgumentException("Unexpected '$part' in mask '$mask'.");
                }
                array_unshift($sequence, $part);
                $re = ($part[0] === '[' ? '(?:' : ')?') . $re;
                $i -= 4;
                continue;
            }

            $pattern = trim($parts[$i]);
            $i--; // validation condition (as regexp)
            $default = $parts[$i];
            $i--; // default value
            $name = $parts[$i];
            $i--; // parameter name
            array_unshift($sequence, $name);

            if ($name[0] === '?') { // "foo" parameter
                $name = substr($name, 1);
                $re = $pattern ? '(?:' . preg_quote($name, '#') . "|$pattern)$re" : preg_quote($name, '#') . $re;
                $sequence[1] = $name . $sequence[1];
                continue;
            }

            // pattern, condition & metadata
            if (isset(static::$styles[$name])) {
                $meta = static::$styles[$name];
            } else {
                $meta = static::$styles['#'];
            }

            if (isset($metadata[$name])) {
                $meta = $metadata[$name] + $meta;
            }

            if ($pattern == '' && isset($meta[self::PATTERN])) {
                $pattern = $meta[self::PATTERN];
            }

            if ($default !== '') {
                $meta[self::VALUE] = (string)substr($default, 1);
                $meta['fixity'] = self::PATH_OPTIONAL;
            }

            $meta['filterTable2'] = empty($meta[self::FILTER_TABLE]) ? null : array_flip($meta[self::FILTER_TABLE]);
            if (array_key_exists(self::VALUE, $meta)) {
                if (isset($meta['filterTable2'][$meta[self::VALUE]])) {
                    $meta['defOut'] = $meta['filterTable2'][$meta[self::VALUE]];

                } elseif (isset($meta[self::FILTER_OUT])) {
                    $meta['defOut'] = call_user_func($meta[self::FILTER_OUT], $meta[self::VALUE]);

                } else {
                    $meta['defOut'] = $meta[self::VALUE];
                }
            }
            $meta[self::PATTERN] = "#(?:$pattern)\\z#A";

            // include in expression
            $aliases['p' . $i] = $name;
            $re = '(?P<p' . $i . '>(?U)' . $pattern . ')' . $re;
            if ($brackets) { // is in brackets?
                if (!isset($meta[self::VALUE])) {
                    $meta[self::VALUE] = $meta['defOut'] = null;
                }
                $meta['fixity'] = self::PATH_OPTIONAL;

            } elseif (!$autoOptional) {
                unset($meta['fixity']);

            } elseif (isset($meta['fixity'])) { // auto-optional
                $re = '(?:' . $re . ')?';
                $meta['fixity'] = self::PATH_OPTIONAL;

            } else {
                $autoOptional = false;
            }

            $metadata[$name] = $meta;
        } while (true);

        if ($brackets) {
            throw new Nette\InvalidArgumentException("Missing '[' in mask '$mask'.");
        }

        $this->aliases = $aliases;
        $this->re = '#' . $re . '/?\z#A';
        $this->metadata = $metadata;
        $this->sequence = $sequence;
    }

    /**
     * @param Nette\Http\IRequest $httpRequest
     * @return Request|null
     */
    public function match(Nette\Http\IRequest $httpRequest)
    {
        // 1) URL MASK
        $url = $httpRequest->getUrl();
        $re = $this->re;

        if ($this->type === self::HOST) {
            $host = $url->getHost();
            $path = '//' . $host . $url->getPath();
            $parts = ip2long($host) ? [$host] : array_reverse(explode('.', $host));
            $re = strtr($re, [
                '/%basePath%/' => preg_quote($url->getBasePath(), '#'),
                '%tld%' => preg_quote($parts[0], '#'),
                '%domain%' => preg_quote(isset($parts[1]) ? "$parts[1].$parts[0]" : $parts[0], '#'),
                '%sld%' => preg_quote(isset($parts[1]) ? $parts[1] : '', '#'),
                '%host%' => preg_quote($host, '#'),
            ]);

        } elseif ($this->type === self::RELATIVE) {
            $basePath = $url->getBasePath();
            if (strncmp($url->getPath(), $basePath, strlen($basePath)) !== 0) {
                return null;
            }
            $path = (string)substr($url->getPath(), strlen($basePath));

        } else {
            $path = $url->getPath();
        }

        if ($path !== '') {
            $path = rtrim(rawurldecode($path), '/') . '/';
        }

        if (!$matches = Strings::match($path, $re)) {
            // stop, not matched
            return null;
        }

        // assigns matched values to parameters
        $params = [];
        foreach ($matches as $k => $v) {
            if (is_string($k) && $v !== '') {
                $params[$this->aliases[$k]] = $v;
            }
        }


        // 2) CONSTANT FIXITY
        foreach ($this->metadata as $name => $meta) {
            if (!isset($params[$name]) && isset($meta['fixity']) && $meta['fixity'] !== self::OPTIONAL) {
                $params[$name] = null; // cannot be overwriten in 3) and detected by isset() in 4)
            }
        }


        // 3) QUERY
        if ($this->xlat) {
            $params += self::renameKeys($httpRequest->getQuery(), array_flip($this->xlat));
        } else {
            $params += $httpRequest->getQuery();
        }


        // 4) APPLY FILTERS & FIXITY
        foreach ($this->metadata as $name => $meta) {
            if (isset($params[$name])) {
                if (!is_scalar($params[$name])) {

                } elseif (isset($meta[self::FILTER_TABLE][$params[$name]])) { // applies filterTable only to scalar parameters
                    $params[$name] = $meta[self::FILTER_TABLE][$params[$name]];

                } elseif (isset($meta[self::FILTER_TABLE]) && !empty($meta[self::FILTER_STRICT])) {
                    return null; // rejected by filterTable

                } elseif (isset($meta[self::FILTER_IN])) { // applies filterIn only to scalar parameters
                    $params[$name] = call_user_func($meta[self::FILTER_IN], (string)$params[$name]);
                    if ($params[$name] === null && !isset($meta['fixity'])) {
                        return null; // rejected by filter
                    }
                }

            } elseif (isset($meta['fixity'])) {
                $params[$name] = $meta[self::VALUE];
            }
        }

        if (isset($this->metadata[null][self::FILTER_IN])) {
            $params = call_user_func($this->metadata[null][self::FILTER_IN], $params);
            if ($params === null) {
                return null;
            }
        }

        $locale = (array_key_exists('locale', $params) ? $params['locale'] : null);

        $foundLocale = $this->localeRepository->getLocaleCache($locale);
        if (!$foundLocale || !$foundLocale->isActive())
        {
            $foundLocale = $this->localeRepository->getDefault();
            $params['locale'] = $foundLocale->getLanguageCode();
            //!FIXME DEFAULT SHOULD BE EMPTY!!!, but default locale detection fails in CMS if locale is not set (it fallbacks to CS instead to default)
            //!FIXME FIX Detection in kyby translation to match CMS rules, then remove this and use CurrentLocale!
        }

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

        // 5) BUILD Request
        if (!isset($params[self::PRESENTER_KEY])) {
            throw new Nette\InvalidStateException('Missing presenter in route definition.');
        } elseif (!is_string($params[self::PRESENTER_KEY])) {
            return null;
        }
        $presenter = $params[self::PRESENTER_KEY];
        unset($params[self::PRESENTER_KEY]);

        if (isset($this->metadata[self::MODULE_KEY])) {
            $presenter = (isset($params[self::MODULE_KEY]) ? $params[self::MODULE_KEY] . ':' : '') . $presenter;
            unset($params[self::MODULE_KEY]);
        }

        return new Request(
            $presenter,
            $httpRequest->getMethod(),
            $params,
            $httpRequest->getPost(),
            $httpRequest->getFiles(),
            [Request::SECURED => $httpRequest->isSecured()]
        );
    }

    /**
     * Constructs absolute URL from Request object.
     *
     * @return string|NULL
     */
    public function constructUrl(Nette\Application\Request $appRequest, Nette\Http\Url $refUrl)
    {
        $pageInfo = $this->structureMenuRepository->getOneByPresenterAction(($this->module ? ':' . $this->module . ':' : '') . $appRequest->getPresenterName(),
            $appRequest->parameters['action']);

        $params = $appRequest->parameters;
        if ($pageInfo && $pageInfo->isHomePage() == true) {
            $params['slug'] = null;
        } else {
            if ($pageInfo) {

                $locale = $appRequest->getParameter('locale');
                $foundLocale = $this->localeRepository->getLocaleCache($locale);
                if (!$foundLocale)
                {
                    $foundLocale = $this->localeRepository->getDefault();
                }

                $params['slug'] = $this->menuTranslationRepository->getSlug($pageInfo, $foundLocale);
            } else {
                return null;
            }
        }

        $appRequest->setParameters($params);

        if ($this->flags & self::ONE_WAY) {
            return null;
        }

        $params = $appRequest->getParameters();
        $metadata = $this->metadata;

        $presenter = $appRequest->getPresenterName();
        $params[self::PRESENTER_KEY] = $presenter;

        if (isset($metadata[null][self::FILTER_OUT])) {
            $params = call_user_func($metadata[null][self::FILTER_OUT], $params);
            if ($params === null) {
                return null;
            }
        }

        if (isset($metadata[self::MODULE_KEY])) { // try split into module and [submodule:]presenter parts
            $module = $metadata[self::MODULE_KEY];
            if (isset($module['fixity']) && strncmp($presenter, $module[self::VALUE] . ':', strlen($module[self::VALUE]) + 1) === 0) {
                $a = strlen($module[self::VALUE]);
            } else {
                $a = strrpos($presenter, ':');
            }
            if ($a === false) {
                $params[self::MODULE_KEY] = isset($module[self::VALUE]) ? '' : null;
            } else {
                $params[self::MODULE_KEY] = substr($presenter, 0, $a);
                $params[self::PRESENTER_KEY] = substr($presenter, $a + 1);
            }
        }

        foreach ($metadata as $name => $meta) {
            if (!isset($params[$name])) {
                continue; // retains NULL values
            }

            if (isset($meta['fixity'])) {
                if ($params[$name] === false) {
                    $params[$name] = '0';
                } elseif (is_scalar($params[$name])) {
                    $params[$name] = (string)$params[$name];
                }

                if ($params[$name] === $meta[self::VALUE]) { // remove default values; NULL values are retain
                    unset($params[$name]);
                    continue;

                } elseif ($meta['fixity'] === self::CONSTANT) {
                    return null; // missing or wrong parameter '$name'
                }
            }

            if (is_scalar($params[$name]) && isset($meta['filterTable2'][$params[$name]])) {
                $params[$name] = $meta['filterTable2'][$params[$name]];

            } elseif (isset($meta['filterTable2']) && !empty($meta[self::FILTER_STRICT])) {
                return null;

            } elseif (isset($meta[self::FILTER_OUT])) {
                $params[$name] = call_user_func($meta[self::FILTER_OUT], $params[$name]);
            }

            if (isset($meta[self::PATTERN]) && !preg_match($meta[self::PATTERN], rawurldecode($params[$name]))) {
                return null; // pattern not match
            }
        }

        // compositing path
        $sequence = $this->sequence;
        $brackets = [];
        $required = null; // NULL for auto-optional
        $url = '';
        $i = count($sequence) - 1;
        do {
            $url = $sequence[$i] . $url;
            if ($i === 0) {
                break;
            }
            $i--;

            $name = $sequence[$i];
            $i--; // parameter name

            if ($name === ']') { // opening optional part
                $brackets[] = $url;

            } elseif ($name[0] === '[') { // closing optional part
                $tmp = array_pop($brackets);
                if ($required < count($brackets) + 1) { // is this level optional?
                    if ($name !== '[!') { // and not "required"-optional
                        $url = $tmp;
                    }
                } else {
                    $required = count($brackets);
                }

            } elseif ($name[0] === '?') { // "foo" parameter
                continue;

            } elseif (isset($params[$name]) && $params[$name] != '') { // intentionally ==
                $required = count($brackets); // make this level required
                $url = $params[$name] . $url;
                unset($params[$name]);

            } elseif (isset($metadata[$name]['fixity'])) { // has default value?
                if ($required === null && !$brackets) { // auto-optional
                    $url = '';
                } else {
                    $url = $metadata[$name]['defOut'] . $url;
                }

            } else {
                return null; // missing parameter '$name'
            }
        } while (true);


        if ($this->type === self::HOST) {
            $host = $refUrl->getHost();
            $parts = ip2long($host) ? [$host] : array_reverse(explode('.', $host));
            $url = strtr($url, [
                '/%basePath%/' => $refUrl->getBasePath(),
                '%tld%' => $parts[0],
                '%domain%' => isset($parts[1]) ? "$parts[1].$parts[0]" : $parts[0],
                '%sld%' => isset($parts[1]) ? $parts[1] : '',
                '%host%' => $host,
            ]);
            $url = ($this->scheme ?: $refUrl->getScheme()) . ':' . $url;
        } else {
            if ($this->lastRefUrl !== $refUrl) {
                $scheme = ($this->scheme ?: $refUrl->getScheme());
                $basePath = ($this->type === self::RELATIVE ? $refUrl->getBasePath() : '');
                $this->lastBaseUrl = $scheme . '://' . $refUrl->getAuthority() . $basePath;
                $this->lastRefUrl = $refUrl;
            }
            $url = $this->lastBaseUrl . $url;
        }

        if (strpos($url, '//', 7) !== false) {
            return null;
        }

        // build query string
        if ($this->xlat) {
            $params = self::renameKeys($params, $this->xlat);
        }

        unset($params['action']);
        unset($params['presenter']);

        $sep = ini_get('arg_separator.input');
        $query = http_build_query($params, '', $sep ? $sep[0] : '&');
        if ($query != '') { // intentionally ==
            $url .= '?' . $query;
        }

        return $url;

    }

    /**
     * camelCaseAction name -> dash-separated.
     * @param  string
     * @return string
     */
    private static function action2path($s)
    {
        $s = preg_replace('#(.)(?=[A-Z])#', '$1-', $s);
        $s = strtolower($s);
        $s = rawurlencode($s);
        return $s;
    }

    private static function path2action($s)
    {
        $s = strtolower($s);
        $s = preg_replace('#-(?=[a-z])#', ' ', $s);
        $s = substr(ucwords('x' . $s), 1);
        //$s = lcfirst(ucwords($s));
        $s = str_replace(' ', '', $s);
        return $s;
    }

    private static function presenter2path($s)
    {
        $s = strtr($s, ':', '.');
        $s = preg_replace('#([^.])(?=[A-Z])#', '$1-', $s);
        $s = strtolower($s);
        $s = rawurlencode($s);
        return $s;
    }


    private static function param2path($s)
    {
        return str_replace('%2F', '/', rawurlencode($s));
    }
}
