<?php


namespace Inensus\BulkRegistration\Services;


use App\Models\Meter\MeterTariff;

class TariffService extends CreatorService
{
    public function __construct(MeterTariff $meterTariff)
    {
        parent::__construct($meterTariff);
    }
    public function resolveCsvDataFromComingRow($csvData)
    {
        $tariffConfig = config('bulk-registration.csv_fields.tariff');
        $tariffData = [
            'name' => $csvData[$tariffConfig['name']],
            'price' => $csvData[$tariffConfig['price']],
            'currency' => "K",
            'total_price' => $csvData[$tariffConfig['total_price']]
        ];
        return $this->createRelatedDataIfDoesNotExists($tariffData);
    }
}