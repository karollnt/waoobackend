<?php
/**
 * Clase para manejar pushNotification
 * @category  Library
 */
class Pushwoosh {
  private $API_TOKEN = 'VO36G0kuQ86ls0FNe1BaEgI9GlDQSx9ywzk1w2jGaQtyA6eU753BuLOeoXNaM0Q8w7qhqWsvwQVYD1DY6FWp';
  private $APPLICATION_CODE = '0E566-1EDAC';
  private $PW_DEBUG = false;

  function __construct() {
    # code...
  }

  private function pwCall($method, $options=array()) {
    $data = array (
      'application' => $this->APPLICATION_CODE,
      'auth' => $this->API_TOKEN,
      'notifications' => $options
    );
    $url = 'https://cp.pushwoosh.com/json/1.3/' . $method;
    $request = json_encode(['request' => $data]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $request);

    $response = curl_exec($ch);
    $info = curl_getinfo($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($this->PW_DEBUG) {
      print "[PW] request: $request\n";
      print "[PW] response: $response\n";
      print '[PW] info: ' . print_r($info, true);
    }

    // if ($err) {
    //   print "cURL Error #:" . $err;
    //   return false;
    // }
    // else {
    //   print $response;
    // }
  }

  public function sendMessage($msg,$devices=array()) {
    $options = array(
      array(
        'send_date' => 'now',
        'content' => $msg,
        'link' => '',
        'devices' => $devices
      )
    );
    $this->pwCall('createMessage',$options);
  }
}
