<?php
	class BancosModel extends CI_Model{
		
		public function __construct(){
			$this->load->database();
		}
		
		public function listaBancos(){
			$mensaje = '';
			$this->db
			->select("*",false)
			->from("bancos")
			->where("estado",1);
			$res = $this->db->get();
			if($res->num_rows()>0){
				$cont1 = 0;
				foreach($res->result() as $row){
					if($cont1==0) $cont1 = 1;
					else $mensaje .= ',';
					$mensaje .= '{"id":"'.($row->id).'","nombre":"'.($row->nombre).'"}';
				}
			}
			return $mensaje;
		}
		
	}