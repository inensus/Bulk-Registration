<?php
return [
    'csv_fields' => [

        'person' => [
            'name' => 'Customer_Name',
            'account_title' => 'AccountNo',

        ],

        'cluster' => [
            'name' => 'Region'
        ],

        'mini_grid' => [
            'cluster_id' => 'cluster_id',
            'name' => 'Township'
        ],

        'city' => [
            'cluster_id' => 'cluster_id',
            'mini_grid_id' => 'mini_grid_id',
            'name' => 'Village'
        ],

        'address' => [
            'person_id' => 'person_id',
            'city_id' => 'city_id',
            'phone' => 'Customer Phone'
        ],

        'tariff' => [
            'name' => 'MeterType',
            'currency' => 'K',
            'price' => 'UnitCost',
            'total_price' => 'UnitCost',

        ],
        'access_rate' => [
            'tariff_id' => 'tariff_id',
            'connection_fee' => 'ConnectionFees',
            'period' => 30
        ],
        'connection_type' => [
            'name' => 'MeterType'
        ],

        'connection_group' => [
            'name' => 'MeterType'
        ],

        'appliance_type' => [
            'name' => 'What appliance would you like to purchase?',
            'price' => 0
        ],
        'meter_type' => [
            'max_current' => 'LoadLimit',
            'online' => 1,
            'phase' => 1,
        ],
        'meter' => [
            'serial_number' => 'MeterID&VillgePCode',
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
            'points' => 'points'
        ]

    ],
    'appliance_types' => ['TV - 24', 'Option 5', 'Fridge', 'Freezer', 'Fan'],

    'geocoder' => [
        'key' => 'AIzaSyCSKRhRzHc8Kx0GXBqIKg_VWljmGXqANzI',
        'country' => 'MM',
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
        'AccessRateService' => 'Inensus\BulkRegistration\Services\AccessRateService',
        'MeterTypeService' => 'Inensus\BulkRegistration\Services\MeterTypeService',
    ]
];