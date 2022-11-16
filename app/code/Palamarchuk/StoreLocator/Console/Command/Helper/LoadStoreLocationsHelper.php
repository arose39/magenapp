<?php

declare(strict_types=1);

namespace Palamarchuk\StoreLocator\Console\Command\Helper;

use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Palamarchuk\StoreLocator\Model\StoreLocationFactory;
use Palamarchuk\StoreLocator\Model\StoreLocationRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LoadStoreLocationsHelper
{
    const MAX_LINE_LENGTH = 10000;

    public function __construct(
        private StoreLocationRepository $storeLocationRepository,
        private State                   $state,
        private StoreLocationFactory    $storeLocationFactory
    ) {
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $folderPath = $input->getOption('file');
        $quantity = $input->getOption('quantity');
        if (!$folderPath) {
            $output->writeln("Укажите путь к папке с csv файлом (store:locator:load --file folder_path)");
        } else {
            $data = $this->parseCsvFile($folderPath, $quantity);
            foreach ($data as $item) {
                $storeLocation = $this->storeLocationFactory->create();
                $storeLocation->setName($item['name']);
                $storeLocation->setDescription($item['description']);
                $storeLocation->setAddress($item['address']);
                $storeLocation->setCity($item['city']);
                $storeLocation->setCountry($item['country']);
                $storeLocation->setState((int)$item['state']);
                $storeLocation->setZip($item['zip']);
                $storeLocation->setPhone($item['phone']);
                $this->storeLocationRepository->save($storeLocation);
            }
            $output->writeln("Data successfully imported to DB");
            return Cli::RETURN_SUCCESS;
        }

        return Cli::RETURN_FAILURE;
    }

    private function setState(): void
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
    }

    private function parseCsvFile($csvfile, $quantity): ?array
    {
        $csv = [];
        $rowCount = 0;
        if (($handle = fopen($csvfile, "r")) !== false) {
            $maxLineLength = self::MAX_LINE_LENGTH;
            $header = fgetcsv($handle, $maxLineLength);
            $headerColumnsCount = count($header);
            $i = 0;
            while (($row = fgetcsv($handle, $maxLineLength)) !== false && $i < $quantity) {
                $rowColumnsCount = count($row);
                if ($rowColumnsCount == $headerColumnsCount) {
                    $entry = array_combine($header, $row);
                    $csv[] = $entry;
                } else {
                    error_log("csvreader: Invalid number of columns at line " . ($rowCount + 2) . " (row " . ($rowCount + 1) . "). Expected=$headerColumnsCount Got=$rowColumnsCount");

                    return null;
                }
                $rowCount++;
                $i++;
            }
            //echo "Totally $rowcount rows found\n";
            fclose($handle);
        } else {
            error_log("csvreader: Could not read CSV \"$csvfile\"");

            return null;
        }

        return $csv;
    }
}
