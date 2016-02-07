<?php
	class UsuariosModel extends CI_Model{
		
		public function __construct(){
			$this->load->database();
		}
		
		public function verificaLogin($u,$p){
			$mensaje = "";
			$arraywhere = array("nickname"=>$u,"clave"=>md5($p),"estado"=>1);
			$this->db
			->select("id,TRIM(CONCAT(nombres,' ',apellidos)) AS nombrecompleto",false)
			->from("usuarios")
			->where($arraywhere);
			$res = $this->db->get();
			if($res->num_rows()>0){
				$mensaje = "ok";
			}
			else{
				$mensaje = "Usuario y/o constrase&ntilde;a incorrecto(a)";
			}
			return $mensaje;
		}
		
		public function cambiarClave($u,$p,$p2){
			$mensaje = "";
			$arraywhere = array("nickname"=>$u,"clave"=>md5($p),"estado"=>1);
			$arrayupd = array("clave"=>md5($p2));
			$this->db
			->select("id",false)
			->from("usuarios")
			->where($arraywhere);
			$res = $this->db->get();
			if($res->num_rows()>0){
				$this->db->where($arraywhere);
				$this->db->update("usuarios",$arrayupd);
				if($this->db->affected_rows()>0) $mensaje = "Informaci&oacute;n actualizada";
				else $mensaje = "No se pudo actualizar la informaci&oacute;n";
			}
			else{
				$mensaje = "Usuario y/o constrase&ntilde;a incorrecto(a)";
			}
			return $mensaje;
		}
		
		public function existeUsuario($columna,$valor){
			$existe = false;
			$awhere = array($columna=>$valor);
			$this->db
			->select("id",false)
			->from("usuarios")
			->where($awhere);
			$res = $this->db->get();
			if($res->num_rows()>0) $existe = true;
			return $existe;
		}
		
		public function crearUsuario($datos){
			$mensaje = "";
			$this->db->insert('usuarios',$datos);
			if($this->db->affected_rows()>0) $mensaje = "Informaci&oacute;n actualizada";
			else $mensaje = "No se pudo actualizar la informaci&oacute;n";
			return $mensaje;
		}
		
		public function crearAsistente($datos,$datosmat){
			$mensaje = $this->crearUsuario($datos);
			$this->ingresarMateriasAsesor($datos['nickname'],$datosmat);
			return $mensaje;
		}
		
		public function borrarUsuario($usuario){
			$mensaje = "";
			$this->db->where('usuario',$usuario);
			$this->db->delete('usuarios');
			if($this->db->affected_rows()>0) $mensaje = "Informaci&oacute;n actualizada";
			else $mensaje = "No se pudo actualizar la informaci&oacute;n";
			return $mensaje;
		}
		
		public function modificarUsuario($idusuario,$datos){
			$mensaje = "";
			$this->db->where('id',$idusuario);
			$this->db->update('usuarios',$datos);
			if($this->db->affected_rows()>0) $mensaje = "Informaci&oacute;n actualizada";
			else $mensaje = "No se pudo actualizar la informaci&oacute;n";
			return $mensaje;
		}
		
		public function buscarUsuarios($columna,$valor){
			$mensaje = "";
			$this->db
			->select("*",false)
			->where($columna,$valor);
			$res = $this->db->get("usuarios");
			if($res->num_rows()>0){
				$cont1 = 0;
				foreach($res->result() as $row){
					if($cont1==0) $cont1 = 1;
					else $mensaje .= ',';
					$cal = $this->calificacionAsesor($row->nickname);
					$mensaje .= '{"id":"'.($row->id).'","tipo":"'.($row->tipo).'",'
					.'"nombre":"'.($row->nombres).'","apellido":"'.($row->apellidos).'",'
					.'"celular":"'.($row->celular).'","email":"'.($row->email).'","calificacion":"'.($cal).'",'
					.'"idbanco":"'.($row->idbanco).'","numerocuenta":"'.($row->numerocuenta).'"'
					.'}';
				}
			}
			else{
				$mensaje = '{"'.$columna.'":"'.$valor.'"}';
			}
			return $mensaje;
		}
		
		public function calificacionAsesor($nickname){
			$mensaje = "0";
			$awhere = array('u.nickname'=>$nickname);
			$this->db
			->select("r.puntaje",false)
			->from("usuarios u")
			->join("rating r","r.idasistente=u.id","inner")
			->where($awhere);
			$res = $this->db->get();
			if($res->num_rows()>0){
				$acum = 0;
				foreach($res->result() as $row){
					$acum += $row->puntaje;
				}
				$mensaje = $acum/($res->num_rows());
			}
			return $mensaje;
		}
		
		public function ingresarMateriasAsesor($nickname,$arraymaterias){
			$mensaje = "";
			$usuario = $this->usuarioObj($nickname);
			$this->db->where('idasistente',$usuario->id);
			$this->db->update('asistentemateria',array("estado"=>0));
			$aexiste = array();
			$this->db
			->select("am.*",false)
			->from("materia m")
			->join("asistentemateria am","am.idmateria=m.id","inner")
			->join("usuarios u","u.id=am.idasistente","inner")
			->where(array("u.nickname"=>$nickname,"m.estado"=>1));
			$res = $this->db->get();
			if($res->num_rows()>0){
				$mensaje = $this->actualizarMateriasAsesor($nickname,$arraymaterias);
			}
			else{
				foreach($arraymaterias as $v){
					$datos = array("idasistente"=>$usuario->id,"idmateria"=>$v);
					$this->db->insert('asistentemateria',$datos);
				}
				if($this->db->affected_rows()>0) $mensaje = "Informaci&oacute;n actualizada";
				else $mensaje = "No se pudo actualizar la informaci&oacute;n";
			}
			return $mensaje;
		}
		
		public function actualizarMateriasAsesor($nickname,$arraymaterias){
			$mensaje = "";
			$usuario = $this->usuarioObj($nickname);
			$idsins = array();
			$this->db->where('idasistente',$usuario->id);
			$this->db->update('asistentemateria',array("estado"=>0));
			$aexiste = array();
			$this->db
			->select("am.*",false)
			->from("materia m")
			->join("asistentemateria am","am.idmateria=m.id","inner")
			->join("usuarios u","u.id=am.idasistente","inner")
			->where(array("u.nickname"=>$nickname,"m.estado"=>1));
			$res = $this->db->get();
			if($res->num_rows()>0){
				foreach($res->result() as $row){
					$aexiste[$row->id] = $row->estado;
				}
			}
			foreach($arraymaterias as $v){
				if(array_key_exists($v,$aexiste)){
					$this->db->where('idasistente',$usuario->id);
					$this->db->update('asistentemateria',array("estado"=>1));
				}
				else{
					$datos = array("idasistente"=>$usuario->id,"idmateria"=>$v);
					$this->db->insert('asistentemateria',$datos);
				}
			}
			if($this->db->affected_rows()>0) $mensaje = "Informaci&oacute;n actualizada";
			else $mensaje = "No se pudo actualizar la informaci&oacute;n";
			return $mensaje;
		}
		
		public function calificarAsesor($idasesor,$puntaje){
			$mensaje = "";
			$datos = array("idasistente"=>$idasesor,"puntaje"=>$puntaje);
			$this->db->insert('rating',$datos);
			if($this->db->affected_rows()>0) $mensaje = "Informaci&oacute;n actualizada";
			else $mensaje = "No se pudo actualizar la informaci&oacute;n";
			return $mensaje;
		}
		
		public function usuarioObj($nickname){
			$u = $this->buscarUsuarios("nickname",$nickname);
			$u = '['.$u.']';
			$usr = json_decode($u);
			$usuario = $usr[0];
			return $usuario;
		}
		
		public function notificacionesTrabajoCreado($nickname){
			$mensaje = '';
			$this->db
			->select("t.id,m.nombre",false)
			->from("notificacionesusuario n")
			->join("trabajo t","t.id=n.idtrabajo","inner")
			->join("usuarios u","u.id=n.idusuario","inner")
			->join("materia m","m.id=t.idmateria","inner")
			->where(array("u.nickname"=>$nickname,"n.leido"=>0));
			$res = $this->db->get();
			if($res->num_rows()>0){
				$cont1 = 0;
				foreach($res->result() as $row){
					if($cont1==0) $cont1 = 1;
					else $mensaje .= ',';
					$mensaje .= '{"idtrabajo":"'.($row->id).'","materia":"'.($row->nombre).'"}';
				}
			}
			return $mensaje;
		}
		
		public function notificacionesNoLeidasCant($nickname){
			$mensaje = 0;
			$this->db
			->select("n.id",false)
			->from("notificacionesusuario n")
			->join("usuarios u","u.id=n.idusuario","inner")
			->where(array("u.nickname"=>$nickname,"n.leido"=>0));
			$res = $this->db->get();
			$mensaje = $res->num_rows();
			return $mensaje;
		}
		
		public function notificacionesNoLeidas($nickname){
			$mensaje = '';
			$this->db
			->select("n.id,n.mensaje,n.fecha,n.idtrabajo,t.titulo",false)
			->from("notificacionesusuario n")
			->join("usuarios u","u.id=n.idusuario","inner")
			->join("trabajo t","t.id=n.idtrabajo","inner")
			->where(array("u.nickname"=>$nickname,"n.leido"=>0));
			$res = $this->db->get();
			if($res->num_rows()>0){
				$cont1 = 0;
				foreach($res->result() as $row){
					if($cont1==0) $cont1 = 1;
					else $mensaje .= ',';
					$mensaje .= '{"id":"'.($row->id).'","mensaje":"'.($row->mensaje).'","fecha":"'.($row->fecha).'","idtrabajo":"'.($row->idtrabajo).'","titulo":"'.($row->titulo).'"}';
				}
			}
			return $mensaje;
		}
		
		public function marcarLeida($id){
			$this->db->where('id',$id);
			$this->db->update('notificacionesusuario',array("leido"=>1));
		}
	}
