<?php

namespace App\Command;

use App\Component\Exporter\Export;
use App\Component\Exporter\ExportFactory;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

class ExportCreateCommand extends Command
{
    protected static $defaultName = 'export:create';

    private EntityManagerInterface $em;
    private LoggerInterface $logger;
    private Filesystem $filesystem;
    private KernelInterface $kernel;

    /**
     * ExportCreateCommand constructor.
     * @param EntityManagerInterface $em
     * @param LoggerInterface $logger
     * @param Filesystem $filesystem
     * @param KernelInterface $kernel
     * @param null $name
     */
    public function __construct(
        EntityManagerInterface $em,
        LoggerInterface $logger,
        Filesystem $filesystem,
        KernelInterface $kernel,
        $name = null
    )
    {
        parent::__construct($name);
        $this->em = $em;
        $this->logger = $logger;
        $this->filesystem = $filesystem;
        $this->kernel = $kernel;
    }

    protected function configure()
    {
        $this
            ->setDescription('Export a file with all the website data in CSV and JSON.')
            ->addArgument('format', InputArgument::OPTIONAL, 'Argument description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $csvExport = ExportFactory::create($this->filesystem, $this->em, $this->kernel->getProjectDir(), 'csv', new \DateTime());
        $csvExport->execute();

        $jsonExport = ExportFactory::create($this->filesystem, $this->em, $this->kernel->getProjectDir(), 'json', new \DateTime());
        $jsonExport->execute();
//        $jsonExport->exportFromCsv();

        $io->success('Export command has successfully generated csv and json files.');

        return 0;
    }
}
