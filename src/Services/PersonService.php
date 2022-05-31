<?php


namespace Inensus\BulkRegistration\Services;


use App\Models\Person\Person;

class PersonService extends CreatorService
{

    public function __construct(Person $person)
    {
        parent::__construct($person);
    }

    public function resolveCsvDataFromComingRow($csvData)
    {
        $personConfig = config('bulk-registration.csv_fields.person');
        $personData = [
            'name' => $csvData[$personConfig['name']],
            'surname' => '',
            'title' => $csvData[$personConfig['account_title']],
        ];
        return $this->createRelatedDataIfDoesNotExists($personData);
    }
}