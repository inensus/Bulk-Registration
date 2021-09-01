<?php


namespace Inensus\BulkRegistration\Services;


use App\Models\Manufacturer;

class ManufacturerService extends CreatorService
{

    public function __construct(Manufacturer $manufacturer)
    {
        parent::__construct($manufacturer);
    }
    public function resolveCsvDataFromComingRow($csvData)
    {
        $manufacturerConfig = config('bulk-registration.csv_fields.manufacturer');
        if (strlen(preg_replace('/\s+/', '', $csvData[$manufacturerConfig['name']]))>0){
            $manufacturerData = [
                'name' => $csvData[$manufacturerConfig['name']],
                'api_name' =>preg_replace('/\s+/', '', $csvData[$manufacturerConfig['name']]).'Api'
            ];
            return $this->createRelatedDataIfDoesNotExists($manufacturerData);
        }
       return false;
    }
}