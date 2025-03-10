<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;

class NMI extends Model
{
    public $service_url = "https://secure.nmi.com/api/transact.php";
    public $username = "insureshipcom";
    public $password = "daza4242";

    public $username_test = "demo";
    public $password_test = "password";

    private $post_fields;
    protected $result;
    protected $response;
    protected $transaction_information = array();
    protected $transaction_type = null;
    protected $transactions;

    protected $transactionTypes = [
        'sale',
        'auth',
        'credit',
    ];
    public function do_sale(&$data)
    {
        $this->transaction_type = 'sale';

        $this->map_data_order($data);

        $this->prepareInformation();

        $results = $this->send_info_to_nmi();

        if (empty($results)) {
            return false;
        }

        $this->formatResult($results);

        $this->mapResult();

        $this->response['gateway']          = "NMI";
        $this->response['transaction_type'] = "sale";
        $this->response['cc_last_four']     = substr($data['cc_number'], strlen($data['cc_number']) - 5, 4);
        $this->response['order_id']         = $data['order_id'];

        return $this->response;
    }

    public function do_void(&$data)
    {
        $this->transaction_type = 'void';

        $this->map_data_void($data);

        $this->prepareInformation();

        $results = $this->send_info_to_nmi();

        if (empty($results)) {
            return false;
        }

        $this->formatResult($results);

        $this->mapResult();

        if ($this->response['http_code'] != 201) {
            return $this->do_refund($data);
        }

        $this->response['gateway']          = "NMI";
        $this->response['transaction_type'] = "void";
        $this->response['order_id']         = $data['order_id'];

        return $this->response;
    }

    public function do_refund(&$data)
    {
        $this->transaction_type = 'refund';

        $this->map_data_refund($data);

        $this->prepareInformation();

        $results = $this->send_info_to_nmi();

        if (empty($results)) {
            return false;
        }

        $this->formatResult($results);

        $this->mapResult();

        $this->response['gateway']          = "NMI";
        $this->response['transaction_type'] = "refund";
        $this->response['order_id']         = $data['order_id'];
        $this->response['amount']           = $data['amount'] * 100;

        return $this->response;
    }

    protected function map_data_order($data)
    {
        $billing['email']             = isset($data['email']) ? $data['email'] : '';
        $billing['firstname']         = isset($data['firstname']) ? $data['firstname'] : '';
        $billing['lastname']          = isset($data['lastname']) ? $data['lastname'] : '';
        $billing['phone']             = isset($data['phone']) ? $data['phone'] : '';
        $billing['billing_address']   = isset($data['billing_address1']) ? $data['billing_address1'] : '';
        $billing['billing_city']      = isset($data['billing_city']) ? $data['billing_city'] : '';
        $billing['billing_state']     = isset($data['billing_state']) ? $data['billing_state'] : '';
        $billing['billing_country']   = isset($data['billing_country']) ? $data['billing_country'] : '';

        $shipping['carrier_code']     = isset($data['carrier_code']) ? $data['carrier_code'] : '';
        $shipping['shipping_address'] = isset($data['shipping_address1']) ? $data['shipping_address1'] : '';
        $shipping['shipping_city']    = isset($data['shipping_city']) ? $data['shipping_city'] : '';
        $shipping['shipping_state']   = isset($data['shipping_state']) ? $data['shipping_state'] : '';
        $shipping['shipping_country'] = isset($data['shipping_country']) ? $data['shipping_country'] : '';

        $order = array(
            'order_id' => $data['order_number'],
            'amount'   => $data['coverage_amount']
        );

        $cc = array(
            'cc_number' => $data['cc_number'],
            'cvv'       => $data['cvv'],
            'cc_exp'    => $data['cc_exp']
        );

        $this->transaction_information['creditcard'] = $cc;
        $this->transaction_information['order']      = $order;
        $this->transaction_information['shipping']   = $shipping;
        $this->transaction_information['billing']    = $billing;

        $this->transaction_information['login']['username'] = stristr($_SERVER['HTTP_HOST'], "timbur") ? $this->username_test : $this->username;
        $this->transaction_information['login']['password'] = stristr($_SERVER['HTTP_HOST'], "timbur") ? $this->password_test : $this->password;

        return true;
    }

    protected function map_data_void(&$data)
    {
        $this->transaction_information['void']['type']          = 'void';
        $this->transaction_information['void']['transactionid'] = $data['transaction_id'];
        $this->transaction_information['void']['void_reason']   = 'user_cancel';

        $this->transaction_information['login']['username'] = stristr($_SERVER['HTTP_HOST'], "timbur") ? $this->username_test : $this->username;
        $this->transaction_information['login']['password'] = stristr($_SERVER['HTTP_HOST'], "timbur") ? $this->password_test : $this->password;

        return true;
    }

    protected function map_data_refund(&$data)
    {
        $this->transaction_information['refund']['type']          = 'refund';
        $this->transaction_information['refund']['transactionid'] = $data['transaction_id'];
        $this->transaction_information['refund']['amount']        = $data['amount'];

        $this->transaction_information['login']['username'] = stristr($_SERVER['HTTP_HOST'], "timbur") ? $this->username_test : $this->username;
        $this->transaction_information['login']['password'] = stristr($_SERVER['HTTP_HOST'], "timbur") ? $this->password_test : $this->password;

        return true;
    }

    protected function prepareInformation()
    {
        $fields = array();

        $fields = $this->transaction_information['login'];

        if ($this->transaction_type == 'sale' || $this->transaction_type == 'auth' || $this->transaction_type == 'credit') {
            $fields = array_merge($fields, $this->transaction_information['order'], $this->transaction_information['shipping'], $this->transaction_information['billing'], $this->transaction_information['creditcard']);
        } else {
            if (!isset($this->transaction_information[$this->transaction_type])) {
                die("You didn't provide $this->transaction_type information");
            }

            $fields = array_merge($fields, $this->transaction_information[$this->transaction_type]);
        }

        $fields_string = "";
        foreach ($fields as $key => $value) {
            $fields_string .= $key . '=' . urlencode($value) . '&';
        }
        rtrim($fields_string, '&');
        $this->post_fields = $fields_string;

        return true;
    }

    protected function send_info_to_nmi()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->service_url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->post_fields);
        curl_setopt($ch, CURLOPT_POST, 1);

        if (!($data = curl_exec($ch))) {
            // use logger
            return false;
        }
        curl_close($ch);

        return $data;
    }

    protected function formatResult($result)
    {
        $result = explode("&", $result);

        for ($i = 0; $i < count($result); $i++) {
            $rdata = explode("=", $result[$i]);
            $this->result[$rdata[0]] = $rdata[1];
        }

        return $this->result;
    }

    protected function mapResult()
    {
        if (!isset($this->result)) {
            throw new Exception('Use formatResult method first');
        }

        if ($this->result['response'] != 1 || $this->result['response_code'] != 100) {
            $this->response['http_code']          = 402;
            $this->response['message']            = 'Error';
            $this->response['status']             = 'Error';
            $this->response['transaction_status'] = 'Error';
        } else {
            $this->response['http_code']          = 201;
            $this->response['message']            = 'Approved';
            $this->response['status']             = 'Approved';
            $this->response['transaction_status'] = 'Approved';
        }

        $this->response['auth_code']        = $this->result['authcode'];
        $this->response['transaction_id']   = $this->result['transactionid'];
        $this->response['avs_response']     = $this->result['avsresponse'];
        $this->response['cvv_response']     = $this->result['cvvresponse'];
        $this->response['response_code']    = $this->result['response_code'];
        $this->response['response_message'] = $this->result['responsetext'];

        if (in_array($this->transaction_type, $this->transactionTypes)) {
            $this->response['amount']               = $this->transaction_information['order']['amount'] * 100;
            $this->response['transaction_order_id'] = $this->result['orderid'];
        }
        $this->response['raw_response'] = json_encode($this->result);

        return true;
    }
}
