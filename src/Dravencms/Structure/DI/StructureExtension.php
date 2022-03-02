<?php declare(strict_types = 1);

namespace Dravencms\Structure\DI;

use Dravencms\Structure\Structure;
use Nette\DI\CompilerExtension;
use Dravencms\Structure\ICmsComponentRepository;


/**
 * Class StructureExtension
 * @package Dravencms\Structure\DI
 */
class StructureExtension extends CompilerExtension
{

    public function loadConfiguration(): void
    {
        $builder = $this->getContainerBuilder();

        $builder->addDefinition($this->prefix('structure'))
            ->setFactory(Structure::class);

        $this->loadCmsComponents();
        $this->loadCmsModels();

        $this->loadComponents();
        $this->loadModels();
        $this->loadConsole();
    }
    
    protected function loadCmsModels(): void
    {
        $builder = $this->getContainerBuilder();
        foreach ($this->loadFromFile(__DIR__ . '/cmsModels.neon') as $i => $command) {
            $cli = $builder->addDefinition($this->prefix('cmsModels.' . $i));
            if (is_string($command)) {
                $cli->setFactory($command);
            } else {
                throw new \InvalidArgumentException;
            }
        }
    }

    protected function loadCmsComponents(): void
    {
        $builder = $this->getContainerBuilder();
        foreach ($this->loadFromFile(__DIR__ . '/cmsComponents.neon') as $i => $command) {
            $cli = $builder->addFactoryDefinition($this->prefix('cmsComponent.' . $i))
                ->addTag(CmsExtension::TAG_COMPONENT);
            if (is_string($command)) {
                $cli->setImplement($command);
            } else {
                throw new \InvalidArgumentException;
            }
        }
    }

    protected function loadComponents(): void
    {
        $builder = $this->getContainerBuilder();
        foreach ($this->loadFromFile(__DIR__ . '/components.neon') as $i => $command) {
            $cli = $builder->addFactoryDefinition($this->prefix('components.' . $i));
            if (is_string($command)) {
                $cli->setImplement($command);
            } else {
                throw new \InvalidArgumentException;
            }
        }
    }

    protected function loadModels(): void
    {
        $builder = $this->getContainerBuilder();
        foreach ($this->loadFromFile(__DIR__ . '/models.neon') as $i => $command) {
            $cli = $builder->addDefinition($this->prefix('models.' . $i));
            if (is_string($command)) {
                $cli->setFactory($command);
            } else {
                throw new \InvalidArgumentException;
            }
        }
    }

    protected function loadConsole(): void
    {
        $builder = $this->getContainerBuilder();

        foreach ($this->loadFromFile(__DIR__ . '/console.neon') as $i => $command) {
            $cli = $builder->addDefinition($this->prefix('cli.' . $i))
                ->setAutowired(false);

            if (is_string($command)) {
                $cli->setFactory($command);
            } else {
                throw new \InvalidArgumentException;
            }
        }
    }
    
    public function beforeCompile()
    {
        $builder = $this->getContainerBuilder();
        $cms = $builder->getDefinition($this->prefix('cms'));
        

        foreach ($builder->findByType(ICmsComponentRepository::class) AS $serviceName => $service) {
            $match = $this->findRepositoryMapping($service->getClass());
            if ($match)
            {
                list($module, $component, $action) = $match;
                $cms->addSetup('addComponentRepository', ['@' . $serviceName, $module, $component, $service->getClass()]);
            }
        }
        
        foreach ($builder->findByTag(self::TAG_COMPONENT) AS $serviceName => $bool) {
            $service = $builder->getDefinition($serviceName);
            $match = $this->findComponentMapping($service->getImplement());
            if ($match)
            {
                list($module, $component, $action) = $match;
                $cms->addSetup('addComponent', ['@' . $serviceName, $module, $component, $action, $service->getImplement()]);
            }
        }

        $registerToLatte = function (Nette\DI\ServiceDefinition $def) {
            $def->addSetup('?->onCompile[] = function($engine) { Salamek\Cms\Macros\Latte::install($engine->getCompiler()); }', ['@self']);

            if (method_exists('Latte\Engine', 'addProvider')) { // Nette 2.4
                $def->addSetup('addProvider', ['cms', $this->prefix('@cms')])
                    ->addSetup('addFilter', ['cmsLink', [$this->prefix('@helpers'), 'cmsLinkFilterAware']]);
            } else {
                $def->addSetup('addFilter', ['getCms', [$this->prefix('@helpers'), 'getCms']])
                    ->addSetup('addFilter', ['cmsLink', [$this->prefix('@helpers'), 'cmsLink']]);
            }
        };

        $latteFactoryService = $builder->getByType('Nette\Bridges\ApplicationLatte\ILatteFactory');
        if (!$latteFactoryService || !self::isOfType($builder->getDefinition($latteFactoryService)->getClass(), 'Latte\engine')) {
            $latteFactoryService = 'nette.latteFactory';
        }

        if ($builder->hasDefinition($latteFactoryService) && self::isOfType($builder->getDefinition($latteFactoryService)->getClass(), 'Latte\Engine')) {
            $registerToLatte($builder->getDefinition($latteFactoryService));
        }

        if ($builder->hasDefinition('nette.latte')) {
            $registerToLatte($builder->getDefinition('nette.latte'));
        }
    }
    
     /**
     * @param $class
     * @return array|null
     */
    private function findRepositoryMapping(string $class): ?array
    {
        $config = $this->getConfig();
        foreach($config['mappings'] AS $mappingComponent => $mappingRepository)
        {
            $match = $this->matchMapping($mappingRepository, $class);
            if ($match)
            {
                return $match;
            }
        }
        return null;
    }

    /**
     * @param string $class
     * @return array|null
     */
    private function findComponentMapping(string $class): ?string
    {
        $config = $this->getConfig();
        foreach($config['mappings'] AS $mappingComponent => $mappingRepository)
        {
            $match = $this->matchMapping($mappingComponent, $class);
            if ($match)
            {
                return $match;
            }
        }
        return null;
    }

    /**
     * @param $mapping
     * @return string
     */
    private function mappingToRegexp(string $mapping): string
    {
        if (!Strings::contains($mapping, '*'))
        {
            throw new \InvalidArgumentException(sprintf('There are no wildcards in mapping %s', $mapping));
        }

        $mapping = preg_quote($mapping, '/');

        $replaceWildcard = '\*';
        $wildcardsReplaces = [
            '(?P<module>[^\\\\\\\\]*?)',
            '(?P<component>[^\\\\\\\\]*?)',
            '(?P<action>[^\\\\\\\\]*?)'
        ];

        $occurrence = substr_count($mapping, $replaceWildcard);
        for ($i=0; $i < $occurrence; $i++)
        {
            $from = '/'.preg_quote($replaceWildcard, '/').'/';
            $mapping = preg_replace($from, $wildcardsReplaces[$i], $mapping, 1);
        }

        $mapping = preg_replace('/'.preg_quote('\-', '/').'/', '([^\\\\\\\\]*?)', $mapping);

        return '/^'.$mapping.'$/i';
    }

    /**
     * @param $mapping
     * @param $class
     * @return array|null
     */
    private function matchMapping(string $mapping, string $class): ?array
    {
        $regexp = $this->mappingToRegexp($mapping);
        $matches = [];
        if (preg_match($regexp, $class, $matches))
        {
            return [$matches['module'], $matches['component'], (array_key_exists('action', $matches) ? $matches['action'] : null)];
        }

        return null;
    }
}
