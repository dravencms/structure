<?php declare(strict_types = 1);

namespace Dravencms\Structure\DI;

use Nette\Bridges\ApplicationLatte\LatteFactory;
use Dravencms\Structure\Structure;
use Nette\DI\CompilerExtension;
use Dravencms\Structure\ICmsComponentRepository;
use Dravencms\Structure\Filters\Latte;
use Nette\Utils\Strings;
use Nette\Schema\Expect;
use Nette\Schema\Schema;


/**
 * Class StructureExtension
 * @package Dravencms\Structure\DI
 */
class StructureExtension extends CompilerExtension
{
    const TAG_COMPONENT = 'salamek.cms.component';
    
    public function getConfigSchema(): Schema
    {
        return Expect::structure([
            'tempPath' => Expect::string()->required(),
            'layoutDir' => Expect::string()->required(),
            'parentClass' => Expect::string()->required(),
            'defaultLayout' => Expect::string()->required(),
            'presenterModule' => Expect::string()->required(),
            'presenterMapping' => Expect::string()->required(),
            'mappings' => Expect::arrayOf(Expect::string(), Expect::string())->required(),
            'templateOverrides' => Expect::arrayOf(Expect::string(), Expect::string())
        ]);
    }

    
    public function loadConfiguration(): void
    {
        $builder = $this->getContainerBuilder();
        $config = (array) $this->getConfig();
     
        $builder->addDefinition($this->prefix('structure'))
            ->setFactory(Structure::class, [$config['tempPath'], $config['presenterModule'], $config['presenterMapping'], $config['layoutDir'], $config['parentClass'], $config['mappings'], $config['defaultLayout']])
            ->addSetup('setTemplateOverrides', [$config['templateOverrides']]);
        
        $builder->addDefinition($this->prefix('filters'))
            ->setFactory(Latte::class)
            ->setAutowired(false);
        
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
                ->addTag(self::TAG_COMPONENT);
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
        
        $structure = $builder->getDefinition($this->prefix('structure'));
        

        foreach ($builder->findByType(ICmsComponentRepository::class) AS $serviceName => $service) {
            $match = $this->findRepositoryMapping($service->getClass());
            if ($match)
            {
                list($module, $component, $action) = $match;
                $structure->addSetup('addComponentRepository', ['@' . $serviceName, $module, $component, $service->getClass()]);
            }
        }
        
        foreach ($builder->findByTag(self::TAG_COMPONENT) AS $serviceName => $bool) {
            $service = $builder->getDefinition($serviceName);
            $match = $this->findComponentMapping($service->getImplement());
            if ($match)
            {
                list($module, $component, $action) = $match;
                $structure->addSetup('addComponent', ['@' . $serviceName, $module, $component, $action, $service->getImplement()]);
            }
        }
        
        $latteFactoryService = $builder->getDefinitionByType(LatteFactory::class)->getResultDefinition();
        $latteFactoryService->addSetup('addFilter', ['cmsLink', [$this->prefix('@filters'), 'cmsLink']]);
        //$latteFactoryService->addSetup('addFilter', ['getCms', [$this->prefix('@filters'), 'getCms']]);
        $latteFactoryService->addSetup('Dravencms\Structure\Macros\Latte::install(?->getCompiler())', ['@self']);
    }
    
     /**
     * @param $class
     * @return array|null
     */
    private function findRepositoryMapping(string $class): ?array
    {
        $config = $this->getConfig();
        foreach($config->mappings AS $mappingComponent => $mappingRepository)
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
    private function findComponentMapping(string $class): ?array
    {
        $config = $this->getConfig();
        foreach($config->mappings AS $mappingComponent => $mappingRepository)
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
