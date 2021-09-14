<?php


namespace Inensus\BulkRegistration\Services;


use App\Models\AssetPerson;
use App\Models\AssetRate;
use App\Models\AssetType;
use App\Models\Log;
use App\Models\MainSettings;
use App\Models\PaymentHistory;
use App\Models\Person\Person;
use App\Models\Transaction\CashTransaction;
use App\Models\Transaction\Transaction;
use App\Services\AppliancePaymentService;
use App\Services\CashTransactionService;
use Carbon\Carbon;

class AppliancePersonService extends CreatorService
{

    private $cashTransactionService;
    private $appliancePaymentService;
    private $cashTransaction;
    private $transaction;
    private $mainSettings;
    private $log;

    public function __construct(
        CashTransaction $cashTransaction,
        AssetPerson $assetPerson,
        CashTransactionService $cashTransactionService,
        AppliancePaymentService $appliancePaymentService,
        Log $log,
        MainSettings $mainSettings,
        Transaction $transaction
    ) {
        parent::__construct($assetPerson);
        $this->cashTransactionService = $cashTransactionService;
        $this->appliancePaymentService = $appliancePaymentService;
        $this->log = $log;
        $this->mainSettings = $mainSettings;
        $this->cashTransaction = $cashTransaction;
        $this->transaction = $transaction;
    }

    public function createRelatedDataIfDoesNotExists($sales)
    {
        $creatorId = auth('api')->user()->id;
        collect($sales)->each(function ($sale) use ($creatorId) {

            $applianceType = AssetType::query()->firstOrCreate([
                'name' => $sale['Asset Purchased'],
                'price' => $sale['Total Loan Amount'] + ($sale['Fines'] ?? 0)
            ],
                ['name' => $sale['Asset Purchased'], 'price' => $sale['Total Loan Amount'] + ($sale['Fines'] ?? 0)]);

            $appliancePersonData = [
                'asset_type_id' => $applianceType->id,
                'person_id' => $sale['person_id'],
                'total_cost' => $sale['Total Loan Amount'] + ($sale['Fines'] ?? 0),
                'rate_count' => $sale['Loan Duration in months'],
                'first_payment_date' => $sale['Invoice Date'],
                'down_payment' => $sale['Deposit'],
                'created_at' => $sale['Invoice Date'],
                'updated_at' => $sale['Invoice Date'],
                'creator_id' => $creatorId,
                'creator_type' => 'user'
            ];
            $appliancePerson = AssetPerson::query()->firstOrCreate($appliancePersonData, $appliancePersonData);
            if (!$appliancePerson->wasRecentlyCreated) {
                return true;
            }
            $baseTime = $appliancePerson->first_payment_date ?? date('Y-m-d');

            if ($appliancePerson->down_payment > 0) {
                $appliancePerson = $this->createDownPaymentRecordForAppliance($appliancePerson, $applianceType,
                    $creatorId, $baseTime);

            }
            $this->createAssetRatesWithRemainingCosts($appliancePerson, $baseTime, $sale['Monthly Installment']);

            collect($sale['payments'])->each(function ($payment) use ($applianceType, $appliancePerson, $creatorId) {

                $paymentDate = Carbon::createFromTimestampMsUTC($payment['date']);
                $person = $appliancePerson->person()->first();
                $applianceRates = $this->getCustomerDueRates($appliancePerson->person_id, $applianceType->id);
                $this->payLoans($person, $applianceRates, $payment['amount'], $appliancePerson, $paymentDate,
                    $creatorId, $payment['payment_type']);
            });
            return true;
        });

    }

    public function resolveCsvDataFromComingRow($csvData)
    {

        $appliancePersonConfig = config('bulk-registration.csv_fields.appliance_person_json');

        $sales = json_decode($csvData[$appliancePersonConfig['appliance_people']], true);

        $payments = json_decode($csvData[$appliancePersonConfig['appliance_rates']], true);
        $salesCollection = array();


        foreach ($sales as $key => $sale) {
            $sale = collect($sales[$key]);
            $sale['person_id'] = $csvData['person_id'];
            $paymentCollection = array();
            if ($payments) {
                foreach ($payments as $key2 => $payment) {
                    $payment = collect($payments[$key2]);

                    if ($payment['loan_ID'] == $sale['Loan ID']) {

                        array_push($paymentCollection, $payment);
                    }
                    $sale['payments'] = $paymentCollection;
                }
            }
            array_push($salesCollection, $sale);
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

    private function payLoans($person, $loans, $amount, $appliancePerson, $paymentDate, $creatorId, $paymentType)
    {

        $buyerAddress = $person->addresses()->where('is_primary', 1)->first();
        $sender = $buyerAddress == null ? '-' : $buyerAddress->phone;
        $transaction = $this->createCashTransaction($creatorId,
            $amount, $sender, $paymentDate);
        $this->createPaymentLog($creatorId, $appliancePerson, $amount, $paymentDate);
        if (is_array($loans)){
            foreach ($loans as $loan) {
                if ($loan->remaining > $amount) {

                    $this->createPayment(
                        $amount,
                        'web',
                        $paymentType === 'installment' ? 'loan rate' : 'deposit',
                        $person->addresses()->first()->phone,
                        $person, $loan, $transaction, $paymentDate);
                    $loan->remaining -= $amount;
                    $loan->update();
                    $this->updateLastPaymentCreationDate($paymentDate);
                    break;
                } else {
                    $this->createPayment(
                        $loan->remaining,
                        'web',
                        $paymentType === 'installment' ? 'loan rate' : 'down payment',
                        $person->addresses()->first()->phone,
                        $person, $loan, $transaction, $paymentDate);
                    $amount -= $loan->remaining;
                    $loan->remaining = 0;
                    $loan->update();
                    $this->updateLastPaymentCreationDate($paymentDate);
                }
            }
        }

    }

    private function createPaymentLog($creatorId, $appliancePerson, $amount, $paymentDate)
    {
        $mainSettings = $this->mainSettings->newQuery()->first();
        $currency = $mainSettings === null ? '€' : $mainSettings->currency;
        $log = $this->log->newQuery()->make([
            'user_id' => $creatorId,
            'action' => $amount . ' ' . $currency . ' of payment is made ',
            'created_at' => $paymentDate,
            'updated_at' => $paymentDate
        ]);
        $log->affected()->associate($appliancePerson);
        $log->save();

    }

    private function updateLastPaymentCreationDate($paymentDate)
    {
        $lastPayment = PaymentHistory::latest()->first();
        $lastPayment->update([
            'created_at' => $paymentDate
        ]);
    }

    private function createDownPaymentRecordForAppliance($appliancePerson, $applianceType, $creatorId, $baseTime)
    {
        AssetRate::query()->create(
            [
                'asset_person_id' => $appliancePerson->id,
                'rate_cost' => $appliancePerson->down_payment,
                'remaining' => $appliancePerson->down_payment,
                'due_date' => $appliancePerson->created_at,
                'remind' => 0
            ]
        );

        return $appliancePerson;
    }

    private function createAssetRatesWithRemainingCosts($appliancePerson, $baseTime, $rateCost)
    {
        foreach (range(1, $appliancePerson->rate_count) as $rate) {

            $rate_date = date('Y-m-d', strtotime('+' . $rate . ' month', strtotime($baseTime)));
            AssetRate::query()->create(
                [
                    'asset_person_id' => $appliancePerson->id,
                    'rate_cost' => $rateCost,
                    'remaining' => $rateCost,
                    'due_date' => $rate_date,
                    'remind' => 0
                ]
            );
        }
    }

    private function createPayment(
        $amount,
        $payment_service,
        $paymentType,
        $sender,
        $payer,
        $paidFor,
        $transaction,
        $paymentDate
    ) {
        $paymentHistory = PaymentHistory::query()->make([
            'amount' => $amount,
            'payment_service' => $payment_service,
            'payment_type' => $paymentType,
            'sender' => $sender,
            'created_at' => $paymentDate,
            'updated_at' => $paymentDate,
        ]);
        $paymentHistory->payer()->associate($payer);
        $paymentHistory->paidFor()->associate($paidFor);
        $paymentHistory->transaction()->associate($transaction);
        $paymentHistory->save();
    }

    public function createCashTransaction($creatorId, $amount, $sender, $paymentDate)
    {
        $cashTransaction = $this->cashTransaction->newQuery()->create(
            [
                'user_id' => $creatorId,
                'status' => 1,
                'created_at' => $paymentDate,
                'updated_at' => $paymentDate
            ]
        );

        $transaction = $this->transaction->newQuery()->make(
            [
                'amount' => $amount,
                'sender' => $sender,
                'type' => 'deferred_payment',
                'message' => '-',
                'created_at' => $paymentDate,
                'updated_at' => $paymentDate
            ]
        );
        $transaction->originalTransaction()->associate($cashTransaction);
        $transaction->save();

        return $transaction;
    }
}