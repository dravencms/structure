<?php declare(strict_types = 1);

namespace Dravencms\Structure\Console;

use Dravencms\Model\Structure\Repository\MenuRepository;
use Kdyby\Doctrine\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

class FixMenuTreeCommand extends Command
{
    protected static $defaultName = 'structure:menuTree:recover';
    protected static $defaultDescription = 'Recovers structure menu tree';
    
    /** @var EntityManager */
    private $entityManager;

    /** @var MenuRepository */
    private $menuRepository;

    public function __construct(
        EntityManager $entityManager,
        MenuRepository $menuRepository
    )
    {
        parent::__construct(null);

        $this->entityManager = $entityManager;
        $this->menuRepository = $menuRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        try {
            $this->menuRepository->getMenuRepository()->recover();
            $this->entityManager->flush();
            $output->writeLn('Menu tree has been recovered!');
            return 0; // zero return code means everything is ok

        } catch (\Exception $e) {
            $output->writeLn('<error>' . $e->getMessage() . '</error>');
            return 1; // non-zero return code means error
        }
    }
}
