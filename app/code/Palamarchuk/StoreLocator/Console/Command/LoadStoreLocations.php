<?php

declare(strict_types=1);

namespace Palamarchuk\StoreLocator\Console\Command;

use Palamarchuk\StoreLocator\Console\Command\Helper\LoadStoreLocationsHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class LoadStoreLocations extends Command
{
    private const COMMAND_NAME = 'storelocator:load';

    private LoadStoreLocationsHelper $helper;

    public function __construct(
        LoadStoreLocationsHelper $helper
    )
    {
        parent::__construct();
        $this->helper = $helper;
    }

    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Load store locations from csv file to DB')
            ->addOption(
                'file',
                null,
                InputOption::VALUE_REQUIRED,
                'Print path to files with store locations',
                null
            )
            ->addOption(
                'quantity',
                null,
                InputOption::VALUE_REQUIRED,
                'Print quantity of lines loaded from file',
                null
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return $this->helper->execute($input, $output);
    }
}
