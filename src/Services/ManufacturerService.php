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
            $manufacturerData = [
                'name' => 'Calin Meters STS',
                'website' => 'http://www.calinmeter.com/',
                'api_name' => 'CalinApi'
            ];
            return $this->createRelatedDataIfDoesNotExists($manufacturerData);

    }
}