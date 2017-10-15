<?php

/**
 * Clase para manejar pushNotification
 * @category  Library
 */
class OneSignal {

  private $APP_ID = "2456ad57-ed56-498f-b352-e8ebd9c51cee";
  private $GOOGLE_KEY = "AIzaSyCsx4LCDDHkRPGwbEdpGMniPoYFA_lW9pw";
  private $IOS_KEY = "";
  private $API_URL = "https://onesignal.com/api/v1/";
  private $DEVICE_KEYS;
  private $DEVICE_TYPES = array('iOS', 'Android', 'Amazon', 'WinCE', 'browser', 'browser', 'WinCE', 'Mac OS X', 'Firefox OS', 'Mac OS X');

  function __construct() {
    $this->DEVICE_KEYS = array('Android' => $this->GOOGLE_KEY, 'iOS' => $this->IOS_KEY);
  }

  private function postToAPI($fields, $api) {
    $ch = curl_init();
    curl_setopt_array($ch, array(
      CURLOPT_URL => ($this->API_URL).$api,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => $fields,
      CURLOPT_HTTPHEADER => array(
        "authorization: Basic MWRjZTAzZDgtMGMzNC00YTVhLWJlMjgtMGE1NzljMGI3YjFl",
        "Content-Type: application/json"
      ),
    ));

    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
      $return = "cURL Error #:" . $err;
    }
    else {
      $return["allresponses"] = $response;
      $return = json_encode($return);
    }
    return $return;
  }

  public function sendMessageToUsers($msg, $tokens, $extraData=null) {
    $fields = array(
      'app_id' => $this->APP_ID,
      'include_player_ids' => $tokens,
      'contents' => array(
        'en' => $msg
      )
    );
    if ($extraData) {
      $fields['data'] = $extraData;
    }

    $fields = json_encode($fields);
    return $this->postToAPI($fields, "notifications");
  }

}
