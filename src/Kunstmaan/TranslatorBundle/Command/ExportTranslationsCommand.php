<?php

namespace Kunstmaan\TranslatorBundle\Command;

use Kunstmaan\TranslatorBundle\Model\Export\ExportCommand;
use Kunstmaan\TranslatorBundle\Service\Command\Exporter\ExportCommandHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

final class ExportTranslationsCommand extends Command
{
    /**
     * @var ExportCommandHandler
     */
    private $exportCommandHandler;

    public function __construct(ExportCommandHandler $exportCommandHandler)
    {
        parent::__construct();

        $this->exportCommandHandler = $exportCommandHandler;
    }

    protected function configure(): void
    {
        $this
            ->setName('kuma:translator:export')
            ->setDescription('Export stashed translations into files (gzip compressed)')
            ->addOption('domains', 'd', InputOption::VALUE_REQUIRED, 'Specify which domains to export, default all domains in the stash')
            ->addOption('format', 'f', InputOption::VALUE_REQUIRED, 'Specify which format files should be, default is yaml')
            ->addOption('locales', 'l', InputOption::VALUE_REQUIRED, 'Specifiy which locales to export, default all in the stash')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $domains = $input->getOption('domains');
        $format = $input->getOption('format');
        $locales = $input->getOption('locales');

        if (null === $format) {
            throw new InvalidArgumentException('A format should be defined, e.g --format yml');
        }

        $exportCommand = new ExportCommand();
        $exportCommand
            ->setDomains($domains === null ? false : $domains)
            ->setFormat($format === null ? false : $format)
            ->setLocales($locales === null ? false : $locales);

        $this->exportCommandHandler->executeExportCommand($exportCommand);

        return 0;
    }
}
