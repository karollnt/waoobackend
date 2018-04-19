<?php
class KeysModel extends CI_Model {
  public function __construct(){
    $this->load->database();
  }

  public function get_key($key_name) {
    $key_value = '';
    $res = $this->db->query("SELECT key_value FROM app_keys WHERE key_name='{$key_name}'");
    if($res->num_rows()>0) {
      foreach($res->result() as $row) {
        $key_value = $row->key_value;
      }
    }
    return $key_value;
  }
}
