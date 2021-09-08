<?php
return [
    'csv_fields' => [

        'person' => [
            'name' => 'Customer Name',
            'gender'=> 'Sex / Gender',
            'title'=>'customer_ID'
        ],

        'cluster' => [
            'name' => 'State'
        ],

        'mini_grid' => [
            'cluster_id' => 'cluster_id',
            'name' => 'LGA'
        ],

        'city' => [
            'cluster_id' => 'cluster_id',
            'mini_grid_id' => 'mini_grid_id',
            'name' => 'Location'
        ],

        'address' => [
            'person_id' => 'person_id',
            'city_id' => 'city_id',
            'phone' => 'Contacts',
            'alternative_phone' => 'Alternate phone number'
        ],

        'tariff' => [
            'name' => 'Service selected by customer',
            'currency' => 'NGN',
            'price' => 0,
            'total_price' => 0
        ],

        'connection_type' => [
            'name' => 'Connection package'
        ],

        'connection_group' => [
            'name' => 'Purpose of connection'
        ],

        'appliance_type' => [
            'name' => 'Asset Purchased',
            'price' => 0
        ],
        'asset_rate'=>[

        ],

        'manufacturer' => [
            'name' => 'Specify meter manufacturer '
        ],

        'meter' => [
            'serial_number' => 'Scan meter Barcode',
            'in_use' => 1,
            'manufacturer_id' => 'manufacturer_id',
        ],

        'meter_parameter' => [
            'owner_type' => 'person',
            'owner_id' => 'person_id',
            'meter_id' => 'meter_id',
            'connection_type_id' => 'connection_type_id',
            'connection_group_id' => 'connection_group_id',
            'tariff_id' => 'tariff_id'
        ],

        'geographical_information' => [
            'owner_type' => 'owner_type',
            'owner_id' => 'owner_id',
            'points' => 'points',
            'household_latitude' => '_GPS location of household_latitude',
            'household_longitude' => '_GPS location of household_longitude',
            'household' => 'GPS location of household'
        ],

        'person_docs' => [
            'customer_picture' => [
                'person_id'=>'person_id',
                'name' => 'name',
                'type' => 'Customer Picture',
                'location' => null
            ],
            'signed_contract' => [
                'person_id'=>'person_id',
                'name' => 'name',
                'type' => 'Take picture of signed contract',
                'location' => null
            ],
            'customer_id' => [
                'person_id'=>'person_id',
                'name' => 'name',
                'type' => 'Take picture of customer ID',
                'location' => null
            ],
            'payment receipt' => [
                'person_id'=>'person_id',
                'name' => 'name',
                'type' => 'Take picture of customer payment reciept',
                'location' => null
            ],
        ],

        'appliance_person_json' =>[
            'appliance_people'=>'loan_json',
            'appliance_rates'=>'payments_json'
        ]

    ],
    'appliance_types' => ['TV - 24', 'Option 5', 'Fridge', 'Freezer', 'Fan'],

    'geocoder' => [
        'key' => '-',
        'country' => 'UG',
    ],

    'reflections' => [
        'PersonService' => 'Inensus\BulkRegistration\Services\PersonService',
        'PersonDocumentService' => 'Inensus\BulkRegistration\Services\PersonDocumentService',
        'ClusterService' => 'Inensus\BulkRegistration\Services\ClusterService',
        'MiniGridService' => 'Inensus\BulkRegistration\Services\MiniGridService',
        'GeographicalInformationService' => 'Inensus\BulkRegistration\Services\GeographicalInformationService',
        'CityService' => 'Inensus\BulkRegistration\Services\CityService',
        'AddressService' => 'Inensus\BulkRegistration\Services\AddressService',
        'TariffService' => 'Inensus\BulkRegistration\Services\TariffService',
        'ConnectionTypeService' => 'Inensus\BulkRegistration\Services\ConnectionTypeService',
        'ConnectionGroupService' => 'Inensus\BulkRegistration\Services\ConnectionGroupService',
        'ApplianceTypeService' => 'Inensus\BulkRegistration\Services\ApplianceTypeService',
        'MeterParameterService' => 'Inensus\BulkRegistration\Services\MeterParameterService',
        'MeterService' => 'Inensus\BulkRegistration\Services\MeterService',
        'ManufacturerService' => 'Inensus\BulkRegistration\Services\ManufacturerService',
        'AppliancePersonService' => 'Inensus\BulkRegistration\Services\AppliancePersonService',
    ]
];