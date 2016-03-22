<?php
	class SolicitudesModel extends CI_Model{

		public function __construct(){
			$this->load->database();
			$this->load->library('s3');
			//$this->db->get_compiled_select();
		}

		public function crearSolicitud($datos,$datos2){
			$mensaje = '';
			//$this->db->insert('trabajo',$datos);
			$ins = $this->db->simple_query("INSERT INTO trabajo(idusuario,idmateria,titulo,descripcion) "
			." VALUES({$datos['idusuario']},{$datos['idmateria']},".($this->db->escape($datos['titulo'])).",".($this->db->escape($datos['descripcion'])).")");
			if($ins) $mensaje = "Se ha creado la solicitud";
			//if($this->db->affected_rows()>0) $mensaje = "Se ha creado la solicitud";
			else $mensaje = "No se pudo ingresar la informaci&oacute;n";
			$idtrabajo = $this->db->insert_id();
			$this->notificarAsistentesTrabajoCreado($idtrabajo,"Se ha creado una solicitud");
			if($datos2!=null) $this->ingresarArchivos($idtrabajo,$datos['idusuario'],$datos2);
			return $mensaje;
		}

		public function ingresarArchivos($idtrabajo,$idusuario,$datos){
			$mensaje = '';
			$buckName = 'waoofiles';
			$bucket = $this->s3->getBucket($buckName);
			if($bucket !==false) ;
			else{
				$this->s3->putBucket($buckName,'public-read-write');
			}

			foreach($datos as $i=>$v){
				/*$dats = array('idtrabajo'=>$idtrabajo,'idusuario'=>$idusuario,
				'archivo'=>$v['archivo'],'tipoarchivo'=>$v['tipoarchivo'],'extension'=>$v['extension']);*/
				//$this->db->insert('trabajoarchivos',$dats);
				$nombrearch = random_str(32);
				$putf = $this->s3->putObject($v['archivo'],$buckName,$nombrearch.$v['extension'],'public-read');
				if($putf) $mensaje = "Se ha guardado el archivo";
				else $mensaje = "No se pudo ingresar la informaci&oacute;n";
				/*if($this->db->affected_rows()>0) $mensaje = "Se ha guardado el archivo";
				else $mensaje = "No se pudo ingresar la informaci&oacute;n";*/
			}
			return $mensaje;
		}

		public function solicitudesUsuario($nickname){
			$mensaje = '';
			$this->db
			->select("t.id,t.titulo,t.descripcion,t.fecharegistro,t.fecharesuelto,e.nombre AS nomestado,COALESCE(u2.nickname,'Sin asignar') AS asistente,m.nombre AS materia",false)
			->from("trabajo t")
			->join("usuarios u","u.id=t.idusuario","inner")
			->join("estado e","e.id=t.estado","inner")
			->join("materia m","m.id=t.idmateria","inner")
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
					$mensaje .= '{"id":"'.($row->id).'","titulo":"'.($row->titulo).'","materia":"'.($row->materia).'",'
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
			->join("estado e","e.id=t.estado","inner")
			->join("materia m","m.id=t.idmateria","inner")
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
			->join("estado e","e.id=t.estado","inner")
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
			->join("materia m","m.id=t.idmateria","inner")
			->join("usuarios u","u.id=t.idusuario","inner")
			->where("t.idasistente",0);
			$res = $this->db->get();
			if($res->num_rows()>0){
				$cont1 = 0;
				$mensaje = '[';
				foreach($res->result() as $row){
					if($cont1==0) $cont1 = 1;
					else $mensaje .= ',';
					$mensaje .= '{"id":"'.($row->id).'","titulo":"'.($row->titulo).'","materia":"'.($row->materia).'",'
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
			->join("materia m","m.id=t.idmateria","inner")
			->join("usuarios u","u.id=t.idusuario","inner")
			->where(array("t.idasistente"=>0,"t.idmateria"=>$idmateria));
			$res = $this->db->get();
			if($res->num_rows()>0){
				$cont1 = 0;
				//$mensaje = '[';
				foreach($res->result() as $row){
					if($cont1==0) $cont1 = 1;
					else $mensaje .= ',';
					$mensaje .= '{"id":"'.($row->id).'","titulo":"'.($row->titulo).'","materia":"'.($row->materia).'",'
					.'"descripcion":"'.($row->descripcion).'","fecharegistro":"'.($row->fecharegistro).'","solicitante":"'.($row->nickname).'"}';
				}
				//$mensaje .= ']';
			}
			else{
				$mensaje = 'No hay solicitudes';
			}
			return $mensaje;
		}

		public function verificaSiAsistenteOferto($idtrabajo,$idasistente){
			$resp = array('hizo'=>false,'valor'=>0);
			$this->db
			->select("id,valor")
			->from("ofertatrabajo")
			->where(array('idtrabajo'=>$idtrabajo,'idasistente'=>$idasistente,'estado'=>1));
			$res = $this->db->get();
			if($res->num_rows()>0){
				foreach($res->result() as $row){
					$resp['hizo']=true;
					$resp['valor']=$row->valor;
				}
			}
			return $resp;
		}

		public function enviarPrecioTrabajo($idtrabajo,$idasistente,$valor){
			$mensaje = '';
			$verif = $this->verificaSiAsistenteOferto($idtrabajo,$idasistente);
			if($verif['hizo']) $mensaje = "Ya has hecho una oferta por ".number_format($verif['valor'],0,".",",")." para esta solicitud";
			else{
				$this->db->insert('ofertatrabajo',array("idtrabajo"=>$idtrabajo,"idasistente"=>$idasistente,"valor"=>$valor,'estado'=>1));
				if($this->db->affected_rows()>0) $mensaje = "Informaci&oacute;n ingresada";
				else $mensaje = "No se pudo ingresar la informaci&oacute;n";
				$msg = "Ha recibido una oferta para realizar su trabajo por ".number_format($valor,0,".",",").". Verifique en Mis solicitudes las ofertas recibidas.";
				$this->notificarUsuario($msg,"(SELECT idusuario FROM trabajo WHERE id={$idtrabajo})",$idtrabajo);
			}
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

		public function notificarUsuario($msg,$idusuario,$idtrabajo){
			$mensaje = '';
			$res = $this->db->query("INSERT INTO notificacionesusuario(idusuario,mensaje,idtrabajo) VALUES ({$idusuario},'{$msg}',{$idtrabajo});");
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
			->select("t.id,t.titulo,t.descripcion,t.fecharegistro,t.fecharesuelto,e.nombre AS nestado,m.nombre AS nmateria,u.nickname,COALESCE(u1.nickname,'Ninguno') AS nickasistente,t.estado",false)
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
					.'"fecharegistro":"'.($row->fecharegistro).'","fecharesuelto":"'.($row->fecharesuelto).'","estado":"'.($row->nestado).'","idestado":"'.($row->estado).'",'
					.'"materia":"'.($row->nmateria).'","usuario":"'.($row->nickname).'","asistente":"'.($row->nickasistente).'"}';
				}
			}
			return $mensaje;
		}

		public function notificarAsistentesTrabajoCreado($idtrabajo,$msg){
			$mensaje = '';
			$this->db
			->query("INSERT INTO notificacionesusuario (idusuario,idtrabajo,mensaje)
			SELECT amt.idasistente,t.id,'{$msg}'
			FROM trabajo t
			INNER JOIN materia m ON t.idmateria = m.id
			INNER JOIN asistentemateria amt ON amt.idmateria=m.id
			WHERE t.id={$idtrabajo}",false);
			if($this->db->affected_rows()>0) $mensaje = "Informaci&oacute;n ingresada";
			else $mensaje = "No se pudo ingresar la informaci&oacute;n";
			return $mensaje;
		}

		public function ofertasParaTrabajo($idtrabajo){
			$mensaje = '';
			$this->load->model('UsuariosModel');
			$this->db
			->select("otr.idtrabajo,otr.valor,u.nickname",false)
			->from("ofertatrabajo otr")
			->join("usuarios u","u.id=otr.idasistente","inner")
			->where("idtrabajo",$idtrabajo);
			$res = $this->db->get();
			if($res->num_rows()>0){
				$cont1 = 0;
				foreach($res->result() as $row){
					$calif = $this->UsuariosModel->calificacionAsesor($row->nickname);
					if($cont1==0) $cont1 = 1;
					else $mensaje .= ',';
					$mensaje .= '{"id":"'.($row->idtrabajo).'","valor":"'.($row->valor).'","asistente":"'.($row->nickname).'","calificacion":"'.($calif).'"}';
				}
			}
			return $mensaje;
		}

		public function listaArchivosTrabajo($idtrabajo){
			$mensaje = '';
			$this->db
			->select("tra.id,tra.tipoarchivo,u.nickname",false)
			->from("trabajoarchivos tra")
			->join("usuarios u","u.id=tra.idusuario","inner")
			->where("tra.idtrabajo",$idtrabajo);
			$res = $this->db->get();
			if($res->num_rows()>0){
				$cont1 = 0;
				foreach($res->result() as $row){
					if($cont1==0) $cont1 = 1;
					else $mensaje .= ',';
					$mensaje .= '{"id":"'.($row->id).'","tipoarchivo":"'.($row->tipoarchivo).'","usuario":"'.($row->nickname).'"}';
				}
			}
			return $mensaje;
		}

		public function getBlobArchivoSolicitud($idarchivo){
			$mensaje = array('archivo'=>'No hay archivo','tipo'=>'text/plain','extension'=>'.txt');
			$this->db
			->select("archivo,tipoarchivo,extension",false)
			->from("trabajoarchivos")
			->where("id",$idarchivo);
			$res = $this->db->get();
			if($res->num_rows()>0){
				foreach($res->result() as $row){
					$mensaje['archivo'] = $row->archivo;
					$mensaje['tipo'] = $row->tipoarchivo;
					$mensaje['extension'] = $row->extension;
				}
			}
			return $mensaje;
		}

		public function enviarSolucion($datos){
			$mensaje = '';
			$this->db->insert('trabajoarchivos',$datos);
			if($this->db->affected_rows()>0) $mensaje = "Se ha actualizado la solicitud";
			else $mensaje = "No se pudo ingresar la informaci&oacute;n";
			$this->logTrabajo($datos['idusuario'],$datos['idtrabajo'],4,"Archivo de solucion para idtrabajo ".$datos['idtrabajo']." enviado");
			$this->notificarUsuario("Archivo de solucion para solicitud recibido, click <a href='#' onclick='verSolucion(".$datos['idtrabajo'].");'>ac&aacute;</a> para verlo ".$datos['idtrabajo']." enviado",$datos['idusuario'],$datos['idtrabajo']);
			$this->db->where('id',$datos['idtrabajo']);
			$this->db->update("trabajo",array("estado"=>3));
			return $mensaje;
		}

		public function aceptarSolucion($idtrabajo,$idusuario){
			$mensaje = '';
			$this->db->where('id',$idtrabajo);
			$this->db->update("trabajo",array("estado"=>4));
			if($this->db->affected_rows()>0) $mensaje = "Se ha marcado la solicitud como resuelta, gracias por usar nuestros servicios";
			else $mensaje = "No se pudo actualizar la informaci&oacute;n";
			$this->logTrabajo($idusuario,$idtrabajo,5,"El usuario ha marcado el trabajo como solucionado");
			return $mensaje;
		}

		function random_str($length){
			$keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$str = '';
			$max = mb_strlen($keyspace, '8bit') - 1;
			for ($i = 0; $i < $length; ++$i) {
				$str .= $keyspace[random_int(0, $max)];
			}
			return $str;
		}

	}
