<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include APPPATH.'third_party/Braintree/Braintree.php';

/*
 *  Braintree_lib
 *  Braintree PHP SDK v3.*
 *  For Codeigniter 3.*
 */

class Braintree_lib {

  function __construct() {
    $CI = &get_instance();
    $CI->config->load('braintree', TRUE);
    $braintree = $CI->config->item('braintree');
    Braintree_Configuration::environment($braintree['braintree_environment']);
    Braintree_Configuration::merchantId($braintree['braintree_merchant_id']);
    Braintree_Configuration::publicKey($braintree['braintree_public_key']);
    Braintree_Configuration::privateKey($braintree['braintree_private_key']);
  }

  function create_client_token($customer_id = null){
    $options = array(
      'customerId' => $customer_id
    );
    return Braintree_ClientToken::generate($options);
  }

  public function create_payment($data) {
    return Braintree_Transaction::sale($data);
  }

  public function create_customer($data) {
    return Braintree_Customer::create($data);
  }

  public function create_payment_method($data) {
    return Braintree_PaymentMethod::create($data);
  }
}
