<?php

namespace Inensus\BulkRegistration\Services;

use App\Models\Meter\MeterTariff;
use App\PaymentHandler\AccessRate;

class AccessRateService extends CreatorService
{
    public function __construct(AccessRate $accessRate)
    {
        parent::__construct($accessRate);
    }
    public function resolveCsvDataFromComingRow($csvData)
    {
        $accessRateConfig = config('bulk-registration.csv_fields.access_rate');
        $accessRateData = [
            'tariff_id' => $csvData[$accessRateConfig['tariff_id']],
            'amount' => $accessRateConfig['connection_fee'],
            'period' => $accessRateConfig['period'],
        ];
        return $this->createRelatedDataIfDoesNotExists($accessRateData);
    }
}