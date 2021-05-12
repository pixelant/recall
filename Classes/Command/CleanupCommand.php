<?php
declare(strict_types=1);

namespace Pixelant\Recall\Command;

use Pixelant\Recall\Domain\Repository\DataRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CleanupCommand extends Command
{
    /**
     * @var FrontendInterface
     */
    protected $cache;

    /**
     * @var DataRepository
     */
    protected $repository;

    /**
     * RecallService constructor.
     *
     * @param DataRepository $repository
     * @param FrontendInterface $cache
     */
    public function __construct(string $name = null, DataRepository $repository = null, FrontendInterface $cache = null)
    {
        parent::__construct($name);

        $this->cache = $cache ?? GeneralUtility::makeInstance(CacheManager::class)->getCache('recall_data');
        $this->repository = $repository ?? GeneralUtility::makeInstance(DataRepository::class);
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setDescription('Removes old recall data.');
        $this->setHelp(
            'Removes recall data older than [age] seconds. The default age is the same as ' .
            '$GLOBALS[\'TYPO3_CONF_VARS\'][\'FE\'][\'sessionTimeout\'] or (if that\'s not set) 86400 seconds.'
        );
        $this->addArgument(
            'age',
            InputArgument::OPTIONAL,
            'The minimum age of records to remove. Default is sessionTimeout or 86400 seconds.',
            (int)$GLOBALS['TYPO3_CONF_VARS']['FE']['sessionTimeout'] ?: 86400
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $age = (int)$input->getArgument('age');

        if ($age === 0) {
            /** @var QuestionHelper $helper */
            $helper = $this->getHelper('question');

            $question = new ConfirmationQuestion(
                'Age is zero! Do you want to continue and delete all records?',
                false
            );

            if (!$helper->ask($input, $output, $question)) {
                return 0;
            }
        }

        $this->repository->removeOlderThan(time() - $age);

        $this->cache->flush();

        return 0;
    }

}
