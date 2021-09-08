<?php


namespace Inensus\BulkRegistration\Helpers;

use App\Models\Address\Address;
use App\Models\Cluster;
use App\Models\MiniGrid;
use Inensus\BulkRegistration\Services\AppliancePersonService;

class CsvDataProcessor
{
    private $geographicalLocationFinder;
    private $reflections;
    private $recentlyCreatedRecords;

    public function __construct(GeographicalLocationFinder $geographicalLocationFinder)
    {
        $this->geographicalLocationFinder = $geographicalLocationFinder;
        $this->reflections = config('bulk-registration.reflections');
        $this->recentlyCreatedRecords = [
            'cluster' => 0,
            'mini_grid' => 0,
            'village' => 0,
            'customer' => 0,
            'tariff' => 0,
            'connection_type' => 0,
            'connection_group' => 0,
            'meter' => 0,
        ];
    }

    public function processParsedCsvData($csvData)
    {
        $cluster = Cluster::query()->first();
        $miniGrid = MiniGrid::query()->first();
        Collect($csvData)->each(function ($row) use ($miniGrid, $cluster) {
            $person = $this->createRecordFromCsv($row, $this->reflections['PersonService']);
            $row['person_id'] = $person->id;
            $this->checkRecordWasRecentlyCreated($person, 'customer');
            $row['cluster_id'] = $cluster->id;
            $row['mini_grid_id'] = $miniGrid->id;
            $city = $this->createRecordFromCsv($row, $this->reflections['CityService']);
            $row['city_id'] = $city->id;
            $this->checkRecordWasRecentlyCreated($city, 'village');
            $this->createRecordFromCsv($row, $this->reflections['AddressService']);

            $this->createRecordFromCsv($row, $this->reflections['AppliancePersonService']);
        });
        return $this->recentlyCreatedRecords;
    }

    private function createRecordFromCsv($row, $serviceName)
    {
        $service = app()->make($serviceName);
        return $service->resolveCsvDataFromComingRow($row);
    }

    private function checkRecordWasRecentlyCreated($record, $type)
    {
        if ($record->wasRecentlyCreated) {
            $this->recentlyCreatedRecords[$type]++;
        }
    }
}