<?php


namespace Inensus\BulkRegistration\Services;


use App\Models\AssetPerson;
use App\Models\AssetRate;
use App\Models\AssetType;
use App\Models\Person\Person;
use Carbon\Carbon;

class AppliancePersonService extends CreatorService
{


    public function __construct(AssetPerson $assetPerson)
    {
        parent::__construct($assetPerson);
    }

    public function createRelatedDataIfDoesNotExists($sales)
    {

        collect($sales)->each(function ($sale) {

            $applianceType = AssetType::query()->firstOrCreate(['name' => $sale['Asset Purchased'], 'price' => 0],
                ['name' => $sale['Asset Purchased'], 'price' => 0]);
            $totalAmount = $sale['Total Loan Amount'] + ($sale['Fines'] ?? 0);
            $appliancePersonData = [
                'asset_type_id' => $applianceType->id,
                'person_id' => $sale['person_id'],
                'total_cost' => $totalAmount,
                'rate_count' => $sale['Loan Duration in months'],
                'first_payment_date' => $sale['Invoice Date'],
                'down_payment' => $sale['Deposit'],
                'created_at'=> $sale['Invoice Date'],
                'creator_id' => auth('api')->user()->id,
                'creator_type' => 'user'
            ];
            $appliancePerson = AssetPerson::query()->firstOrCreate($appliancePersonData, $appliancePersonData);
            if ($appliancePerson->wasRecentlyCreated) {
                $base_time = $appliancePerson->first_payment_date ?? date('Y-m-d');
                if ($appliancePerson->down_payment > 0) {
                    AssetRate::query()->create(
                        [
                            'asset_person_id' => $appliancePerson->id,
                            'rate_cost' => $appliancePerson->down_payment,
                            'remaining' => 0,
                            'due_date' => Carbon::parse(date('Y-m-d'))->toIso8601ZuluString(),
                            'remind' => 0
                        ]
                    );
                    $appliancePerson->total_cost -= $appliancePerson->down_payment;
                }

                foreach (range(1, $appliancePerson->rate_count) as $rate) {
                    if ($appliancePerson->rate_count === 0) {
                        $rate_cost = 0;
                    } elseif ((int)$rate === (int)$appliancePerson->rate_count) {
                        //last rate
                        $rate_cost = $appliancePerson->total_cost
                            - (($rate - 1) * floor($appliancePerson->total_cost / $appliancePerson->rate_count));
                    } else {
                        $rate_cost = floor($appliancePerson->total_cost / $appliancePerson->rate_count);
                    }
                    $rate_date = date('Y-m-d', strtotime('+' . $rate . ' month', strtotime($base_time)));
                    AssetRate::query()->create(
                        [
                            'asset_person_id' => $appliancePerson->id,
                            'rate_cost' => $rate_cost,
                            'remaining' => $rate_cost,
                            'due_date' => $rate_date,
                            'remind' => 0
                        ]
                    );
                }
                if ($appliancePerson->down_payment > 0) {
                    $buyer = Person::query()->find($appliancePerson->person_id);
                    event(
                        'payment.successful',
                        [
                            'amount' => $appliancePerson->down_payment,
                            'paymentService' => 'web',
                            'paymentType' => 'appliance',
                            'sender' => $appliancePerson->creator_id,
                            'paidFor' => $applianceType,
                            'payer' => $buyer,
                            'transaction' => null,
                        ]
                    );
                }
                collect($sale['payments'])->each(function ($payment) use ($applianceType, $appliancePerson) {
                    $paymentDate = Carbon::createFromTimestampMsUTC($payment['date']);
                    $person = $appliancePerson->person()->first();
                    $applianceRates = $this->getCustomerDueRates($appliancePerson->person_id, $applianceType->id);
                    $this->payLoans($person, $applianceRates, $payment['amount'], $appliancePerson, $paymentDate);
                });
            }
        });

    }

    public function resolveCsvDataFromComingRow($csvData)
    {

        $appliancePersonConfig = config('bulk-registration.csv_fields.appliance_person_json');

        $sales =  json_decode($csvData[$appliancePersonConfig['appliance_people']],true);

        $payments = json_decode($csvData[$appliancePersonConfig['appliance_rates']], true);
        $salesCollection = array();

        foreach ($sales as $key => $sale){

            $sale = collect($sales[$key]);
            $sale['person_id']= $csvData['person_id'];
            $paymentCollection = array();
            foreach ($payments as $key2 => $payment){
                $payment = collect($payments[$key2]);

                if ($payment['loan_ID']==$sale['Loan ID']){

                    array_push($paymentCollection,$payment);
                }
                $sale['payments']=$paymentCollection;
            }
            array_push($salesCollection,$sale);
        }


        $this->createRelatedDataIfDoesNotExists($salesCollection);

    }


    private function getCustomerDueRates($ownerId, $applianceTypeId)
    {
        $loans = AssetPerson::where('person_id', $ownerId)->where('asset_type_id', $applianceTypeId)->pluck('id');
        return AssetRate::with('assetPerson.assetType')
            ->whereIn('asset_person_id', $loans)
            ->where('remaining', '>', 0)
            ->whereDate('due_date', '<', date('Y-m-d'))
            ->get();
    }

    private function payLoans($person, $loans, $amount, $assetPerson, $paymentDate)
    {
        foreach ($loans as $loan) {
            if ($loan->remaining > $amount) {// money is not enough to cover the whole rate
                //add payment history for the loan
                event(
                    'payment.successful',
                    [
                        'amount' => $amount,
                        'paymentService' => 'web',
                        'paymentType' => 'loan rate',
                        'sender' => $assetPerson->creator_id,
                        'paidFor' => $loan,
                        'payer' => $person,
                        'transaction' => null,
                    ]
                );
                $loan->updated_at = $paymentDate;
                $loan->remaining -= $amount;
                $loan->update();
                break;
            } else {
                //add payment history for the loan
                event(
                    'payment.successful',
                    [
                        'amount' => $loan->remaining,
                        'paymentService' => 'web',
                        'paymentType' => 'loan rate',
                        'sender' => $assetPerson->creator_id,
                        'paidFor' => $loan,
                        'payer' => $person,
                        'transaction' => null,
                    ]
                );
                $loan->updated_at = $paymentDate;
                $loan->remaining = 0;
                $loan->update();
            }
        }
    }
}