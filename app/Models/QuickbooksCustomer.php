<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Suppoert\Facades\DB;
class QuickbooksCustomer extends Model
{
    protected $table = "osis_qbo_cutsomer";
    protected $fillable = [
        'id', 'qb_customer_id', 'FullyQualifiedName', 'CompanyName', 'DisplayName', 'PrintOnCheckName',
        'BillAddr', 'ShipAddr', 'Balance', 'BalanceWithJobs', 'GivenName', 'FamilyName', 'PrimaryPhone', 'PrimaryEmail', 'raw', 'created',
    ];
    public $db_table = "osis_qbo_customer";
    public $fields = array(
        'id', 'qb_customer_id', 'FullyQualifiedName', 'CompanyName', 'DisplayName', 'PrintOnCheckName',
        'BillAddr', 'ShipAddr', 'Balance', 'BalanceWithJobs', 'GivenName', 'FamilyName', 'PrimaryPhone', 'PrimaryEmail', 'raw', 'created'
    );

    public function getCustomers()
    {
        return $this->where('qb_customer_id', '!=', 0)
                    ->orderBy('DisplayName', 'asc')
                    ->get();
    }

    public function getByClientId($client_id)
    {
        return DB::table('osis_qbo_customer as a')
                ->join('osis_qb_customer_client as b', 'a.qb_customer_id', '=', 'b.qb_customer_id')
                ->where('b.client_id', $client_id)
                ->first();
    }

    public function importQboCustomers()
    {
        $limit = 1000;
        $count = $this->getQboCustomersCount();
        $pages = ceil($count / $limit);

        for ($i = 0; $i < $pages; $i++) {
            $start = $i * $limit + 1;
            $qbo_customers = $this->getQboCustomers($start, $limit);

            foreach ($qbo_customers as $customer) {
                $exists = $this->where('qb_customer_id', $customer->Id)->exists();

                if (!$exists) {
                    $this->create([
                        'qb_customer_id' => $customer->Id,
                        'FullyQualifiedName' => $customer->FullyQualifiedName,
                        'CompanyName' => $customer->CompanyName,
                        'DisplayName' => $customer->DisplayName,
                        'PrintOnCheckName' => $customer->PrintOnCheckName,
                    ]);
                } else {
                    $this->updateByQboId($customer->Id);
                }
            }
        }
    }

    public function getQboCustomersCount()
    {
        $dataService = $this->getDataService();

        if (empty($dataService)) {
            return 0;
        }

        $count = $dataService->Count("SELECT COUNT(*) FROM Customer");

        return $count['totalCount'];
    }

    public function getQboCustomers($start, $limit)
    {
        $dataService = $this->getDataService();

        if (empty($dataService)) {
            return [];
        }

        return $dataService->Query("SELECT * FROM Customer STARTPOSITION {$start} MAXRESULTS {$limit}");
    }

    public function createCustomerRelationship($customer_id, $entity_id, $entity_type)
    {
        DB::table('osis_qb_customer_client')->insert([
            'qb_customer_id' => $customer_id,
            "{$entity_type}_id" => $entity_id
        ]);
    }

    public function existsByQboIdRelationship($qb_id)
    {
        return DB::table('osis_qb_customer_client')
            ->where('qb_customer_id', $qb_id)
            ->exists();
    }

    public function importQboCustomerById($qb_id)
    {
        $customer = $this->getQboById($qb_id);

        if (!$customer) {
            return false;
        }

        $this->create([
            'qb_customer_id' => $customer->Id,
            'FullyQualifiedName' => $customer->FullyQualifiedName,
            'CompanyName' => $customer->CompanyName,
            'DisplayName' => $customer->DisplayName,
            'PrintOnCheckName' => $customer->PrintOnCheckName
        ]);

        return true;
    }

    public function getQboById($qb_id)
    {
        $dataService = $this->getDataService();

        if (empty($dataService)) {
            return null;
        }

        return $dataService->Query("SELECT * FROM Customer WHERE Id = '{$qb_id}'");
    }

    public function updateByQboId($qb_id)
    {
        $customer = $this->getQboById($qb_id);

        if (!$customer) {
            return false;
        }

        $this->where('qb_customer_id', $qb_id)->update([
            'FullyQualifiedName' => $customer->FullyQualifiedName,
            'CompanyName' => $customer->CompanyName,
            'DisplayName' => $customer->DisplayName,
            'PrintOnCheckName' => $customer->PrintOnCheckName,
            'Balance' => $customer->Balance,
            'BalanceWithJobs' => $customer->BalanceWithJobs,
        ]);

        return true;
    }

    public function deleteByQboId($qb_id)
    {
        return $this->where('qb_customer_id', $qb_id)->delete();
    }

    public function createdNewQboCustomerByDisplayName($display_name)
    {
        $customerObj = new \IPPCustomer();
        $customerObj->DisplayName = substr($display_name, 0, 25);

        $customer = $this->add($customerObj);

        if (empty($customer->Id)) {
            return null;
        }

        return $customer->Id;
    }
}
