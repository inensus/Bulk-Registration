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
            'name' => $csvData[$personConfig['name']] . ' ' . $csvData[$personConfig['m-name']],
            'surname' => $csvData[$personConfig['surname']],
            'sex' => $csvData[$personConfig['sex']],
        ];
        return $this->createRelatedDataIfDoesNotExists($personData);
    }
}