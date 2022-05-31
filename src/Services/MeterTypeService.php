<?php


namespace Inensus\BulkRegistration\Services;


use App\Models\Meter\MeterType;

class MeterTypeService extends CreatorService
{

    public function __construct(MeterType $meterType)
    {
        parent::__construct($meterType);

    }

    public function resolveCsvDataFromComingRow($csvData)
    {
        $meterTypeConfig = config('bulk-registration.csv_fields.meter_type');
        $meterTypeData = [
            'max_current' => $csvData[$meterTypeConfig['max_current']],
            'online' => 1,
            'phase' => 1
        ];
        return $this->createRelatedDataIfDoesNotExists($meterTypeData);
    }
}