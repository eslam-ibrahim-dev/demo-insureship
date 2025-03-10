<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Webhook extends Model
{
    protected $fillable = [
        'id', 'action',
        'domain', 'superclient_id', 'client_id', 'subclient_id',
        'endpoint', 'fields',
        'status', 'created', 'updated'
    ];
    public $fields = array(
        'id', 'action',
        'domain', 'superclient_id', 'client_id', 'subclient_id',
        'endpoint', 'fields',
        'status', 'created', 'updated'
    );

    public static $fields_static = array(
        'id', 'action',
        'domain', 'superclient_id', 'client_id', 'subclient_id',
        'endpoint', 'fields',
        'status', 'created', 'updated'
    );

    public $required_fields = array(
        'action', 'endpoint'
    );

    public $actions = array(
        'policy_created', 'claim_filed', 'claim_validated', 'void_policy'
    );

    public static $actions_static = array(
        'policy_created', 'claim_filed', 'claim_validated', 'void_policy'
    );

    public $db_table = "osis_webhook";
    public static $db_table_static = "osis_webhook";

    // ShopGuarantee key
    public $api_key = "A8FK3ssbRnZ6malJtRSoRCtEsBUqkKedsCxkgT1cORVrGdMzLuNVaB8SceSvITQD";
    public static $api_key_static = "A8FK3ssbRnZ6malJtRSoRCtEsBUqkKedsCxkgT1cORVrGdMzLuNVaB8SceSvITQD";

    private $payload;
    private $endpoint;
    private $action;
    private $client_id;
    private $subclient_id;

    // $params are action, client_id, and subclient_id (if available)

    public function fire($params, $payload)
    {
        if (stristr($_SERVER['SERVER_NAME'], "timbur")) {
            // do nothing
        } else {
            $this->payload = $payload;
            $this->action = $params['action'];
            $this->client_id = $params['client_id'];
            $this->subclient_id = !empty($params['subclient_id']) ? $params['subclient_id'] : 0;

            $this->set_endpoint();

            if (!empty($this->endpoint)) {
                $this->fire_webhook();
            }
        }
    }

    private function set_endpoint()
    {
        $endpoint = "";
        if (!empty($this->subclient_id)) {
            if ($this->exists('subclient')) {
                $endpoint = $this->get_endpoint('subclient');
            } elseif ($this->exists('client')) {
                $endpoint = $this->get_endpoint('client');
            }
        } else {
            if ($this->exists('client')) {
                $endpoint = $this->get_endpoint('client');
            }
        }

        $this->endpoint = $endpoint;
    }

    private function exists($parent_type)
    {
        $sql = "SELECT EXISTS(SELECT 1 FROM osis_webhook WHERE action = ? AND status = ? AND {$parent_type}_id = ?) AS exist";

        $params = array($this->action, 'Active');
        if ($parent_type == 'subclient') {
            $params[] = $this->subclient_id;
        } elseif ($parent_type == 'client') {
            $params[] = $this->client_id;
        }

        $results = $this->selectone($sql, $params);

        return $results['exist'];
    }

    private function get_endpoint($parent_type)
    {
        $sql = "SELECT endpoint FROM osis_webhook WHERE action = ? AND status = ? AND {$parent_type}_id = ?";

        $params = array($this->action, 'Active');
        if ($parent_type == 'subclient') {
            $params[] = $this->subclient_id;
        } elseif ($parent_type == 'client') {
            $params[] = $this->client_id;
        }

        $results = $this->selectone($sql, $params);

        return $results['endpoint'];
    }

    private function fire_webhook()
    {
        $this->logger->error("Webhook Request - " . $this->endpoint . " - " . $this->payload);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->endpoint);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: application/json", 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

        $result = curl_exec($ch);

        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (in_array($http_status, array(200, 201, 202, 203, 204))) {
            $this->logger->error("Webhook Successful - " . $this->payload);
        } else {
            $this->logger->error("Webhook Failed - {$http_status} - " . $this->payload);
        }

        curl_close($ch);
    }

    public function webhook_api_get_client($api_key, $salt)
    {
        $client_id = 0;

        $binds = [
            ':salt' => $salt,
            ':api_key' => $api_key,
        ];

        $sql = "SELECT
                osis_client.id as client_id,
                osis_subclient.id as subclient_id
            FROM osis_client
            LEFT JOIN osis_subclient ON (
                osis_subclient.client_id = osis_client.id
            )
            WHERE
            osis_client.webhooks_enabled = 1
            AND (
                SHA2(CONCAT(osis_client.apikey, :salt), 512) = :api_key
                OR
                    SHA2(CONCAT(osis_subclient.apikey, :salt), 512) = :api_key
                )
            ;
        ";

        $results = $this->select($sql, $binds);

        foreach ($results as $row) {
            if (isset($row['client_id'])) {
                $client_id = $row['client_id'];
                break;
            }
        }

        return $client_id;
    }

    public function subclient_matches_client($client_id, $subclient_id)
    {
        $matches = false;

        $binds = [
            ':client_id' => $client_id,
            ':subclient_id' => $subclient_id,
        ];

        $sql = "SELECT count(*) as count
            FROM shoppersprotection.osis_subclient
            LEFT JOIN osis_client ON
                (
                    osis_subclient.client_id = osis_client.id
                )
            WHERE osis_subclient.id = :subclient_id
            AND osis_client.id = :client_id
            ;
        ";

        $results = $this->selectone($sql, $binds);
        syslog(LOG_DEBUG, json_encode($results));

        if (\intval($results['count']) > 0) {
            $matches = true;
        }

        return $matches;
    }

    public function get_all_api($client_id, $moderncommerce = 0)
    {
        // Removes InsureShip, CyCoverPro, and Test superclients
        //$sql = "SELECT domain,client_id,subclient_id,action,endpoint FROM osis_webhook WHERE status = 'active' AND client_id > 0 AND client_id NOT IN (SELECT id FROM osis_client WHERE superclient_id = 1 OR superclient_id = 0 OR superclient_id = 5)";

        if ($client_id == 0 && $moderncommerce == 1) {
            $sql = "SELECT client_id,subclient_id,action,endpoint FROM osis_webhook WHERE status = 'Active' AND client_id > 0 AND client_id NOT IN (SELECT id FROM osis_client WHERE superclient_id = 1 OR superclient_id = 0 OR superclient_id = 5)";
            $params = array();
        } else {
            $sql = "SELECT a.client_id,a.subclient_id,a.action,a.endpoint FROM osis_webhook a WHERE a.status = 'Active' AND a.client_id = ?";
            $params = array($client_id);
        }

        return $this->select($sql, $params);
    }

    public function webhook_save(&$data)
    {
        $values = "";
        $question_marks = "";

        $insert_vals = array();

        foreach ($data as $key => $value) {
            if (in_array($key, $this->fields)) {
                $values .= $key . ",";
                $question_marks .= "?,";
                $insert_vals[] = $value;
            }
        }

        $values = rtrim($values, ',');
        $question_marks = rtrim($question_marks, ',');

        $sql = "INSERT INTO osis_webhook ({$values}) VALUES ({$question_marks})";

        return $this->execute_query($sql, $insert_vals);
    }

    public function update_webhook(&$data)
    {
        $where = "";
        $where_arr = array();
        $updates = "";
        $updates_arr = array();

        if (!empty($data['domain'])) {
            $where = " domain = ? ";
            $where_arr[] = $data['domain'];
        } elseif (!empty($data['client_id'])) {
            $where = " client_id = ? ";
            $where_arr[] = $data['client_id'];
        } elseif (!empty($data['subclient_id'])) {
            $where = " subclient_id = ? ";
            $where_arr[] = $data['subclient_id'];
        }

        $where .= " AND action = ? ";
        $where_arr[] = $data['action'];

        foreach ($data as $key => $value) {
            if (in_array($key, $this->fields)) {
                $updates .= $key . " = ?,";
                $updates_arr[] = $value;
            }
        }

        $updates = substr($updates, 0, strlen($updates) - 1);

        $sql = "
            UPDATE osis_webhook
            SET {$updates}
            WHERE {$where}
        ";

        $updates_arr = array_merge($updates_arr, $where_arr);

        $this->execute_query($sql, $updates_arr);
    }

    public function webhook_delete(&$data)
    {
        $where = "";
        $where_arr = array();

        if (!empty($data['domain'])) {
            $where = " domain = ? ";
            $where_arr[] = $data['domain'];
        } elseif (!empty($data['client_id'])) {
            $where = " client_id = ? ";
            $where_arr[] = $data['client_id'];
        } elseif (!empty($data['subclient_id'])) {
            $where = " subclient_id = ? ";
            $where_arr[] = $data['subclient_id'];
        }

        $where .= " AND action = ? ";
        $where_arr[] = $data['action'];

        $sql = "DELETE FROM osis_webhook WHERE {$where}";

        $this->execute_query($sql, $where_arr);
    }

    public function api_search(&$data, $moderncommerce = 0)
    {
        $search = "";

        $search_arr = array();

        foreach ($data as $key => $value) {
            if (in_array($key, $this->fields)) {
                $search .= $key . " = ? OR ";
                $search_arr[] = $value;
            }
        }

        $search = rtrim($search, " OR ");

        if ($moderncommerce) {
            $sql = "SELECT client_id,subclient_id,action,endpoint FROM osis_webhook WHERE status = 'Active' AND client_id NOT IN (SELECT id FROM osis_client WHERE superclient_id = 1) AND ({$search})";
        } else {
            $sql = "SELECT client_id,subclient_id,action,endpoint FROM osis_webhook WHERE status = 'Active' AND ({$search})";
        }

        return $this->select($sql, $search_arr);
    }

    public function webhook_exists(&$data)
    {
        $search = "";

        $search_arr = array('Active');

        foreach ($data as $key => $value) {
            if (in_array($key, $this->fields)) {
                $search .= $key . " = ? AND ";
                $search_arr[] = $value;
            }
        }

        $search = rtrim($search, " AND ");

        $sql = "SELECT EXISTS(SELECT 1 FROM osis_webhook WHERE status = ? AND {$search}) AS exist";
        $results = $this->selectone($sql, $search_arr);

        return $results['exist'];
    }

    public function get_webhook(&$data)
    {
        $search = "";

        $search_arr = array();

        foreach ($data as $key => $value) {
            if (in_array($key, $this->fields)) {
                $search .= $key . " = ? OR ";
                $search_arr[] = $value;
            }
        }

        $search = rtrim($search, " OR ");

        $sql = "SELECT client_id,subclient_id,action,endpoint FROM osis_webhook WHERE status = 'Active' AND client_id NOT IN (SELECT id FROM osis_client WHERE superclient_id = 1) AND ({$search})";

        return $this->selectone($sql, $search_arr);
    }
}
