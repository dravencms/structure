<?php declare(strict_types = 1);

namespace Dravencms\Structure\Console;

use Dravencms\Structure\Cms;
use Dravencms\Model\Structure\Repository\MenuRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

class GeneratePresentersCommand extends Command
{
    protected static $defaultName = 'cms:presenters:generate';
    protected static $defaultDescription = 'Generates presenters for all menu content';
    
    /**
     * @var Cms
     */
    private $cms;
    
    /**
     * @var MenuRepository
     */
    private $menuRepository;

    public function __construct(
        Cms $cms,
        MenuRepository $menuRepository
    )
    {
        parent::__construct(null);

        $this->cms = $cms;
        $this->menuRepository = $menuRepository;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            foreach($this->menuRepository->getAll() AS $menu)
            {
                $this->cms->generateMenuPage($menu);
            }
            $output->writeLn('All presenters successfully generated');
            return 0; // zero return code means everything is ok

        } catch (\Exception $e) {
            $output->writeLn('<error>' . $e->getMessage() . '</error>');
            return 1; // non-zero return code means error
        }
    }
}