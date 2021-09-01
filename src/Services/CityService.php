<?php


namespace Inensus\BulkRegistration\Services;

use App\Models\City;

class CityService extends CreatorService
{

    public function __construct(City $city)
    {
        parent::__construct($city);
    }

    public function resolveCsvDataFromComingRow($csvData)
    {
        $cityConfig = config('bulk-registration.csv_fields.city');
        $cityData = [
            'cluster_id'=>$csvData[$cityConfig['cluster_id']],
            'country_id'=>0,
            'mini_grid_id'=>$csvData[$cityConfig['mini_grid_id']],
            'name' => $csvData[$cityConfig['name']]
        ];
        return $this->createRelatedDataIfDoesNotExists($cityData);
    }
}