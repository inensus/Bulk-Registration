<?php


namespace Inensus\BulkRegistration\Services;


use App\Models\MiniGrid;

class MiniGridService extends CreatorService
{

    public function __construct(MiniGrid $miniGrid)
    {
        parent::__construct($miniGrid);
    }
    public function resolveCsvDataFromComingRow($csvData)
    {
        $miniGridConfig = config('bulk-registration.csv_fields.mini_grid');
        $miniGridData = [
            'cluster_id' => $csvData[$miniGridConfig['cluster_id']],
            'name' => $csvData[$miniGridConfig['name']]
        ];
        return $this->createRelatedDataIfDoesNotExists($miniGridData);
    }
}