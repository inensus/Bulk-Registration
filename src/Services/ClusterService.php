<?php


namespace Inensus\BulkRegistration\Services;



use App\Models\Cluster;
use App\Models\User;

class ClusterService extends CreatorService
{

    public function __construct(Cluster $cluster)
    {
        parent::__construct($cluster);
    }

    public function resolveCsvDataFromComingRow($csvData)
    {
        $clusterConfig = config('bulk-registration.csv_fields.cluster');
        $user = User::query()->first();
        $clusterData = [
            'name' => $csvData[$clusterConfig['name']],
            'manager_id' => $user->id
        ];
        return $this->createRelatedDataIfDoesNotExists($clusterData);
    }
}