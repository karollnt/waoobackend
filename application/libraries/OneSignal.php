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
  private $DEVICE_KEYS = array('Android' => $this->$GOOGLE_KEY, 'iOS' => $this->$IOS_KEY);
  private $DEVICE_TYPES = array('iOS', 'Android', 'Amazon', 'WinCE', 'browser', 'browser', 'WinCE', 'Mac OS X', 'Firefox OS', 'Mac OS X')

  function __construct() {
    # code...
  }

  private function postToAPI($fields, $api) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $this->API_URL.$api);
    curl_setopt($ch, CURLOPT_HTTPHEADER,
      array(
        'Content-Type: application/json charset=utf-8',
        'Authorization: Basic MWRjZTAzZDgtMGMzNC00YTVhLWJlMjgtMGE1NzljMGI3YjFl'
      )
    );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    $response = curl_exec($ch);
    curl_close($ch);

    $return["allresponses"] = $response;
    $return = json_encode($return);
    return $return;
  }

  public function sendMessageToUsers($msg, $tokens) {
    $fields = array(
      'app_id' => $this->APP_ID,
      'include_player_ids' => $tokens
      'data' => array("foo" => "bar"),
      'contents' => array(
        'es' => $msg
      )
    );

    $fields = json_encode($fields);
    return $this->postToAPI($fields, "notifications");
  }

}
