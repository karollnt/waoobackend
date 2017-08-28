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

  function create_client_token(){
    $clientToken = Braintree_ClientToken::generate();
    return $clientToken;
  }

  public function create_payment($data) {
    $result = Braintree_Transaction::sale($data);
    return $result;
  }

  public function create_customer($data) {
    $result = Braintree_Customer::create($data);
    return $result->success ? $result->customer : null;
  }

  public function create_payment_method($data) {
    return Braintree_PaymentMethod::create($data);
  }
}