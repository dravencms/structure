<?php declare(strict_types = 1);

namespace Dravencms\Structure\Console;

use Dravencms\Structure\Structure;
use Dravencms\Database\EntityManager;
use Dravencms\Model\Structure\Repository\MenuRepository;
use Dravencms\Model\Structure\Repository\MenuTranslationRepository;
use Dravencms\Model\Locale\Repository\LocaleRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

class FixIncorrectStructureCommand extends Command
{
    protected static $defaultName = 'structure:structure:fix';
    protected static $defaultDescription = 'Attempts to fix incorrectly generated structure items';
    
    /**
     * @var Structure
     */
    private $structure;
    
    /**
     * @var MenuRepository
     */
    private $menuRepository;

    /**
     * @var LocaleRepository
     */
    private $localeRepository;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * MenuTranslationRepository
     */
    private $menuTranslationRepository;

    public function __construct(
        EntityManager $entityManager,
        Structure $structure,
        MenuRepository $menuRepository,
        MenuTranslationRepository $menuTranslationRepository,
        LocaleRepository $localeRepository
    )
    {
        parent::__construct(null);
        $this->entityManager = $entityManager;
        $this->structure = $structure;
        $this->menuRepository = $menuRepository;
        $this->localeRepository = $localeRepository;
        $this->menuTranslationRepository = $menuTranslationRepository;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $activeLocales = $this->localeRepository->getActive();
   
            $systemMenuToDelete = [];

            foreach($this->menuRepository->getAll() AS $menu)
            {
                // Every menu item should have translation record for every active locale
                foreach($activeLocales AS $activeLocale) {
                    $foundTranslation = $this->menuTranslationRepository->getTranslation($menu, $activeLocale);
                    if (!$foundTranslation) {
                        $output->writeLn(sprintf('Menu(%d) "%s" has missing translation for locale %s', $menu->getId(), $menu->getIdentifier(), $activeLocale->getName()));
                        if ($menu->isSystem()) {
                            $output->writeLn('Broken menu item is system, marking for deletion...');

                            if (!array_key_exists($menu->getId(), $systemMenuToDelete)) {
                                $systemMenuToDelete[$menu->getId()] = $menu;
                            }
                        }
                    }
                }
            }

            foreach($systemMenuToDelete AS $systemMenuDelete) {
                // first delete any content
                foreach($systemMenuDelete->getMenuContents() AS $content){
                    $this->entityManager->remove($content);
                }

                // then delete translations
                foreach($systemMenuDelete->getTranslations() AS $translation){
                    $this->entityManager->remove($translation);
                }

                // Delete menu
                $this->entityManager->remove($systemMenuDelete);
            }

            $this->entityManager->flush();
            $output->writeLn('Done');
            return 0; // zero return code means everything is ok

        } catch (\Exception $e) {
            $output->writeLn('<error>' . $e->getMessage() . '</error>');
            return 1; // non-zero return code means error
        }
    }
}
