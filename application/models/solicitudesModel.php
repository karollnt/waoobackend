<?php
	class SolicitudesModel extends CI_Model{
		
		public function __construct(){
			$this->load->database();
		}
		
		public function crearSolicitud($datos){
			$this->db->insert('trabajo',$datos);
			if($this->db->affected_rows()>0) $mensaje = "Se ha creado la solicitud";
			else $mensaje = "No se pudo ingresar la informaci&oacute;n";
			$idtrabajo = $this->db->insert_id();
			$this->notificarAsistentesTrabajoCreado($idtrabajo,"Se ha creado una solicitud");
			return $mensaje;
		}
		
		public function solicitudesUsuario($nickname){
			$mensaje = '';
			$this->db
			->select("t.id,t.titulo,t.descripcion,t.fecharegistro,t.fecharesuelto,e.nombre AS nomestado,COALESCE(u2.nickname,'Sin asignar') AS asistente,m.nombre AS materia",false)
			->from("trabajo t")
			->join("usuarios u","u.id=t.idusuario","inner")
			->join("estados e","e.id=t.estado","inner")
			->join("materias m","m.id=t.idmateria","inner")
			->join("usuarios u2","u2.id=t.idasistente","left")
			->where("u.nickname",$nickname)
			->order_by("t.estado","desc")
			->order_by("t.fecharegistro","desc");
			$res = $this->db->get();
			if($res->num_rows()>0){
				$cont1 = 0;
				foreach($res->result() as $row){
					if($cont1==0) $cont1 = 1;
					else $mensaje .= ',';
					$mensaje .= '{"id":"'.($row->id).'","titulo":"'.($row->titulo).'",'
					.'"descripcion":"'.($row->descripcion).'","fecharegistro":"'.($row->fecharegistro).'",'
					.'"fecharesuelto":"'.($row->fecharesuelto).'","asistente":"'.($row->asistente).'",'
					.'"estado":"'.($row->nomestado).'"}';
				}
			}
			return $mensaje;
		}
		
		public function solicitudesAsistente($nickname){
			$mensaje = '';
			$this->db
			->select("t.id,t.titulo,t.descripcion,t.fecharegistro,t.fecharesuelto,e.nombre AS nomestado,COALESCE(u2.nickname,'Sin asignar') AS asistente,m.nombre AS materia",false)
			->from("trabajo t")
			->join("usuarios u","u.id=t.idasistente","inner")
			->join("estados e","e.id=t.estado","inner")
			->join("materias m","m.id=t.idmateria","inner")
			->join("usuarios u2","u2.id=t.idusuario","inner")
			->where("u.nickname",$nickname)
			->order_by("m.nombre","asc")
			->order_by("t.estado","desc")
			->order_by("t.fecharegistro","desc");
			$res = $this->db->get();
			if($res->num_rows()>0){
				$cont1 = 0;
				foreach($res->result() as $row){
					if($cont1==0) $cont1 = 1;
					else $mensaje .= ',';
					$mensaje .= '{"id":"'.($row->id).'","titulo":"'.($row->titulo).'",'
					.'"descripcion":"'.($row->descripcion).'","fecharegistro":"'.($row->fecharegistro).'",'
					.'"fecharesuelto":"'.($row->fecharesuelto).'","solicitante":"'.($row->solicitante).'",'
					.'"estado":"'.($row->nomestado).'"}';
				}
			}
			return $mensaje;
		}
		
		public function solicitudesAsistenteMateria($nickname,$idmateria){
			$mensaje = '';
			$this->db->select("t.id,t.titulo,t.descripcion,t.fecharegistro,t.fecharesuelto,e.nombre AS nomestado,u.nickname AS solicitante",false)
			->from("trabajo t")
			->join("usuarios u","u.id=t.idasistente","inner")
			->join("estados e","e.id=t.estado","inner")
			->join("usuarios u2","u2.id=t.idusuario","inner")
			->where(array("u.nickname"=>$nickname,"t.idmateria"=>$idmateria))
			->order_by("t.estado","desc")
			->order_by("t.fecharegistro","desc");
			$res = $this->db->get();
			if($res->num_rows()>0){
				$cont1 = 0;
				foreach($res->result() as $row){
					if($cont1==0) $cont1 = 1;
					else $mensaje .= ',';
					$mensaje .= '{"id":"'.($row->id).'","titulo":"'.($row->titulo).'",'
					.'"descripcion":"'.($row->descripcion).'","fecharegistro":"'.($row->fecharegistro).'",'
					.'"fecharesuelto":"'.($row->fecharesuelto).'","solicitante":"'.($row->solicitante).'",'
					.'"estado":"'.($row->nomestado).'"}';
				}
			}
			else{
				$mensaje = 'No hay solicitudes';
			}
			return $mensaje;
		}
		
		public function solicitudesSinAsignar(){
			$mensaje = '';
			$this->db
			->select("t.id,t.titulo,t.descripcion,t.fecharegistro,m.nombre AS materia,u.nickname",false)
			->from("trabajo t")
			->join("materias m","m.id=t.idmateria","inner")
			->join("usuarios u","u.id=t.idusuario","inner")
			->where("t.idasistente",0);
			$res = $this->db->get();
			if($res->num_rows()>0){
				$cont1 = 0;
				$mensaje = '[';
				foreach($res->result() as $row){
					if($cont1==0) $cont1 = 1;
					else $mensaje .= ',';
					$mensaje .= '{"id":"'.($row->id).'","titulo":"'.($row->titulo).'","materia":"'.($row->materia).'"'
					.'"descripcion":"'.($row->descripcion).'","fecharegistro":"'.($row->fecharegistro).'","solicitante":"'.($row->nickname).'"}';
					$mensaje .= ']';
				}
			}
			else{
				$mensaje = 'No hay solicitudes';
			}
			return $mensaje;
		}
		
		public function solicitudesSinAsignarPorMateria($idmateria){
			$mensaje = '';
			$this->db
			->select("t.id,t.titulo,t.descripcion,t.fecharegistro,m.nombre AS materia,u.nickname",false)
			->from("trabajo t")
			->join("materias m","m.id=t.idmateria","inner")
			->join("usuarios u","u.id=t.idusuario","inner")
			->where(array("t.idasistente"=>0,"t.idmateria"=>$idmateria));
			$res = $this->db->get();
			if($res->num_rows()>0){
				$cont1 = 0;
				$mensaje = '[';
				foreach($res->result() as $row){
					if($cont1==0) $cont1 = 1;
					else $mensaje .= ',';
					$mensaje .= '{"id":"'.($row->id).'","titulo":"'.($row->titulo).'","materia":"'.($row->materia).'"'
					.'"descripcion":"'.($row->descripcion).'","fecharegistro":"'.($row->fecharegistro).'","solicitante":"'.($row->nickname).'"}';
				}
				$mensaje .= ']';
			}
			else{
				$mensaje = 'No hay solicitudes';
			}
			return $mensaje;
		}
		
		public function enviarPrecioTrabajo($idtrabajo,$idasistente,$valor){
			$mensaje = '';
			$this->db->insert('ofertatrabajo',array("idtrabajo"=>$idtrabajo,"idasistente"=>$idasistente));
			if($this->db->affected_rows()>0) $mensaje = "Informaci&oacute;n ingresada";
			else $mensaje = "No se pudo ingresar la informaci&oacute;n";
			$msg = "Ha recibido una oferta para realizar su trabajo {$idtrabajo} por $".number_format($valor,0,".",",");
			$this->notificarUsuario($msg,"(SELECT idusuario FROM trabajo WHERE id={$idtrabajo})",$idtrabajo);
			return $mensaje;
		}
		
		public function aceptarPrecio($idpreciotrabajo,$numcomprobante){
			$mensaje = "";
			$idtrabajo = "(SELECT idtrabajo FROM ofertatrabajo WHERE id={$idpreciotrabajo})";
			$idasistente = "(SELECT idasistente FROM ofertatrabajo WHERE id={$idpreciotrabajo})";
			$idusuario = "(SELECT idusuario FROM trabajo WHERE id={$idtrabajo})";
			$this->db->where('id',$idpreciotrabajo);
			$this->db->update('ofertatrabajo',array("estado"=>1));
			if($this->db->affected_rows()>0){
				$mensaje = $this->logTrabajo($idtrabajo,$idusuario,2,"Usuario escoge asistente para hacer el trabajo");
				if(strcasecmp($mensaje,"Informaci&oacute;n actualizada")==0){
					$mensaje = $this->asignarAsistenteTrabajo($idtrabajo,$idasistente,$numcomprobante);
					$this->notificarUsuario("Su oferta para el trabajo {$idtrabajo} ha sido aceptada",$idasistente,$idtrabajo);
				}
				else $mensaje = "No se pudo actualizar la informaci&oacute;n";
			}
			else $mensaje = "No se pudo actualizar la informaci&oacute;n";
			return $mensaje;
		}
		
		public function asignarAsistenteTrabajo($idasistente,$idtrabajo,$numcomprobante){
			$mensaje = '';
			$this->db->where('id',$idtrabajo);
			$this->db->update('trabajo',array("idasistente"=>$idasistente,"estado"=>2,"numcomprobante"=>$numcomprobante));
			if($this->db->affected_rows()>0) $mensaje = "Informaci&oacute;n actualizada";
			else $mensaje = "No se pudo actualizar la informaci&oacute;n";
			return $mensaje;
		}
		
		public function logTrabajo($idtrabajo,$idusuario,$tipo,$desc){
			$mensaje = '';
			$this->db->insert('trabajolog',array("idtrabajo"=>$idtrabajo,"idusuario"=>$idusuario,"tipolog"=>$tipo,"descripcion"=>$desc));
			if($this->db->affected_rows()>0) $mensaje = "Informaci&oacute;n ingresada";
			else $mensaje = "No se pudo ingresar la informaci&oacute;n";
			return $mensaje;
		}
		
		public function notificarUsuario($mensaje,$idusuario,$idtrabajo){
			$mensaje = '';
			$this->db->insert('notificacionesusuario',array("mensaje"=>$mensaje,"idusuario"=>$idusuario,"idtrabajo"=>$idtrabajo));
			if($this->db->affected_rows()>0) $mensaje = "Informaci&oacute;n ingresada";
			else $mensaje = "No se pudo ingresar la informaci&oacute;n";
			return $mensaje;
		}
		
		public function valorOfertaTrabajo($idoferta){
			$valor = 0;
			$this->db
			->select("valor",false)
			->from("ofertatrabajo")
			->where("id",$idoferta);
			$res = $this->db->get();
			if($res->num_rows()>0){
				foreach($res->result() as $row){
					$valor = $row->valor;
				}
			}
			return $valor;
		}
		
		public function idTrabajoDesdeOfertaTrabajo($idoferta){
			$id = 0;
			$this->db
			->select("idtrabajo",false)
			->from("ofertatrabajo")
			->where("id",$idoferta);
			$res = $this->db->get();
			if($res->num_rows()>0){
				foreach($res->result() as $row){
					$id = $row->idtrabajo;
				}
			}
			return $id;
		}
		
		public function detallesSolicitud($id){
			$mensaje = '';
			$this->db
			->select("t.id,t.titulo,t.descripcion,t.fecharegistro,t.fecharesuelto,e.nombre AS nestado,m.nombre AS nmateria,u.nickname,COALESCE(u1.nickname,'Ninguno') AS nickasistente",false)
			->from("trabajo t")
			->join("usuarios u","u.id=t.idusuario","inner")
			->join("usuarios u1","u1.id=t.idasistente","left")
			->join("materia m","m.id=t.idmateria","inner")
			->join("estado e","e.id=t.estado","inner")
			->where("t.id",$id);
			$res = $this->db->get();
			if($res->num_rows()>0){
				foreach($res->result() as $row){
					$mensaje = '{"id":"'.($row->id).'","titulo":"'.($row->titulo).'","descripcion":"'.($row->descripcion).'",'
					.'"fecharegistro":"'.($row->fecharegistro).'","fecharesuelto":"'.($row->fecharesuelto).'","estado":"'.($row->nestado).'",'
					.'"materia":"'.($row->nmateria).'","usuario":"'.($row->nickname).'","asistente":"'.($row->nickasistente).'"}';
				}
			}
			return $mensaje;
		}
		
		public function notificarAsistentesTrabajoCreado($idtrabajo,$mensaje){
			$mensaje = '';
			$this->db
			->query("INSERT INTO notificacionesusuario (idusuario,idtrabajo,mensaje)
			SELECT amt.idasistente,t.id,'{$mensaje}'
			FROM trabajo t
			INNER JOIN materia m ON t.idmateria = m.id
			INNER JOIN asistentemateria amt ON amt.idmateria=m.id
			WHERE t.id={$idtrabajo}");
			if($this->db->affected_rows()>0) $mensaje = "Informaci&oacute;n ingresada";
			else $mensaje = "No se pudo ingresar la informaci&oacute;n";
			return $mensaje;
		}
		
	}