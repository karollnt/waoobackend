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

		public function crearBanco($nombre){
			$mensaje = '';
			$this->db->insert('bancos',array("nombre"=>$nombre));
			if($this->db->affected_rows()>0) $mensaje = "Informaci&oacute;n actualizada";
			else $mensaje = "No se pudo actualizar la informaci&oacute;n";
			return $mensaje;
		}

		public function borrarBanco($id){
			$mensaje = '';
			$this->db->where('id',$id);
			$this->db->update('bancos',array("estado"=>0));
			if($this->db->affected_rows()>0) $mensaje = "Informaci&oacute;n actualizada";
			else $mensaje = "No se pudo actualizar la informaci&oacute;n";
			return $mensaje;
		}

	}
