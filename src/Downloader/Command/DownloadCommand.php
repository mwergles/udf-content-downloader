<?php
/**
 * Created by PhpStorm.
 * User: wergles
 * Date: 13/01/17
 * Time: 15:51
 */

namespace Downloader\Command;

use Downloader\Service\DownloadService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DownloadCommand extends Command
{

    /**
     * @var DownloadService
     */
    protected $service;

    protected $wordlistService;

    public function __construct(DownloadService $service)
    {
        parent::__construct('download');
        $this->service = $service;
    }

    protected function configure()
    {
        $this->setDescription('download content');
        $this->setDefinition(new InputDefinition([
            new InputArgument('data', InputArgument::REQUIRED, 'data directotory to download')
        ]));
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->service->download($input->getArgument('data'));
            $output->writeln('success');
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }
}