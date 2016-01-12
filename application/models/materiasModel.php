<?php
	class MateriasModel extends CI_Model{
		
		public function __construct(){
			$this->load->database();
		}
		
		public function listarMaterias(){
			$mensaje = '';
			$this->db
			->select("*",false)
			->from("materia")
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
		
		public function listarMateriasAsesor($nickname){
			$mensaje = '';
			$this->db
			->select("am.*",false)
			->from("materia m")
			->join("asistentemateria am","am.idmateria=m.id","inner")
			->join("usuarios u","u.id=am.idasistente","inner")
			->where(array("am.estado"=>1,"u.nickname"=>$nickname,"m.estado"=>1));
			$res = $this->db->get();
			if($res->num_rows()>0){
				$cont1 = 0;
				foreach($res->result() as $row){
					if($cont1==0) $cont1 = 1;
					else $mensaje .= ',';
					$mensaje .= '{"id":"'.($row->id).'","nombre":"'.($row->nombre).'","estado":"'.($row->estado).'"}';
				}
			}
			return $mensaje;
		}
		
	}