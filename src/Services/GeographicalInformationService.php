<?php


namespace Inensus\BulkRegistration\Services;


use App\Models\GeographicalInformation;
use App\Models\Meter\MeterParameter;
use App\Models\MiniGrid;
use Inensus\BulkRegistration\Helpers\GeographicalLocationFinder;


class GeographicalInformationService
{
    private $geographicalInformationConfig;

    public function createRelatedDataIfDoesNotExists($geographicalInformationData, $ownerModel)
    {
        if ($geographicalInformationData) {
            $geographicalInformation = GeographicalInformation::query()->make($geographicalInformationData);
            $geographicalInformation->owner()->associate($ownerModel);
            $geographicalInformation->save();
        }
    }

    public function resolveCsvDataFromComingRow($csvData, $ownerModel)
    {
        $this->geographicalInformationConfig = config('bulk-registration.csv_fields.geographical_information');
        $geographicalInformationData = ['points' => ''];
        if ($ownerModel instanceof MiniGrid) {
            $this->createMiniGridRelatedGeographicalInformation($ownerModel);
        } else {
            $geographicalInformationData =
                $this->createMeterParameterRelatedGeographicalInformation($geographicalInformationData,
                    $csvData, $ownerModel);
            $this->createRelatedDataIfDoesNotExists($geographicalInformationData, $ownerModel);
        }
    }

    private function createMiniGridRelatedGeographicalInformation($ownerModel)
    {
        $miniGridId = $ownerModel->id;
        $geographicalInformation = GeographicalInformation::query()->with(['owner'])
            ->whereHasMorph(
                'owner',
                [MiniGrid::class],
                function ($q) use ($miniGridId) {
                    $q->where('id', $miniGridId);
                }
            )->first();
        if ($geographicalInformation->points !== "") {
            return false;
        }
        //$geographicalLocationFinder = app()->make(GeographicalLocationFinder::class);
        // $geographicalCoordinatesResult = $geographicalLocationFinder->getCoordinatesGivenAddress
        //($geographicalInformation->owner->name);
        $geographicalInformation->points = '12.9727867,98.6373245';
        return $geographicalInformation->save();
    }

    private function createMeterParameterRelatedGeographicalInformation(
        $geographicalInformationData,
        $csvData,
        $ownerModel
    ) {
        $meterParameterId = $ownerModel->id;
        $geographicalInformation = GeographicalInformation::query()->with(['owner'])
            ->whereHasMorph(
                'owner',
                [MeterParameter::class],
                function ($q) use ($meterParameterId) {
                    $q->where('id', $meterParameterId);
                }
            )->first();
        if ($geographicalInformation) {
            return false;
        }
        $geographicalInformationData['points'] =
            $this->generateRandomFloatNumber(12.9727867) .
            ',' .
            $this->generateRandomFloatNumber(98.6373245);
        return $geographicalInformationData;
    }

    // generate random float number
    private function generateRandomFloatNumber($coordinate, $min = -1.5, $max = 1.2)
    {
        return strval($coordinate + round($min + mt_rand() / mt_getrandmax() * ($max - $min), 2));
    }
}