<?php 
	if ( ! defined( 'ABSPATH' ) ) exit;

	class migration_drupal_to_wp{
		private $db_drupal;
		private $db_wordpress;
		private $tables;
		private $prefijo_dp;
		private $prefijo_wp;

		private $connection_drupal;
		private $conection_wordpress;

		public $mdtw_mensaje_conection;
		public $mdtw_mensaje_tabla_users;
		public $mdtw_mensaje_tabla_usermeta;
		public $mdtw_mensaje_new_user;
		public $mdtw_mensaje_tabla_posts;
		public $mdtw_mensaje_tabla_postmeta;
		public $mdtw_mensaje_tabla_comments;
		public $mdtw_mensaje_tabla_commentmeta;
		public $mdtw_mensaje_tabla_term;
		public $mdtw_mensaje_tabla_termmeta;
		public $mdtw_mensaje_tabla_term_taxonomy;
		public $mdtw_mensaje_tabla_term_relationships;
		public $mdtw_mensaje_tabla_comprobar;

		//Constructor obligatorio para el funcionamiento: los datos de las BD, mientras que las tablas si estan vacias solo prueban la conexion y los prefijos vienen por defecto si no se pone nada
		function __construct($drupal_host, $drupal_bd, $drupal_user, $drupal_password, $wordpress_host, $wordpress_bd, $wordpress_user, $wordpress_password, $tablas, $prefijo_dp="nr_", $prefijo_wp="wp_"){
			$this->db_drupal=$this->mdtw_conexion_bd_drupal($drupal_host, $drupal_bd, $drupal_user, $drupal_password);
			$this->db_wordpress=$this->mdtw_conexion_bd_wordpress($wordpress_host, $wordpress_bd, $wordpress_user, $wordpress_password);
			$this->tables=$tablas;

			$this->prefijo_dp=$prefijo_dp;
			$this->prefijo_wp=$prefijo_wp;

			$this->mdtw_conexion_bd_resultado();
			$this->mdtw_migracion_controlador();
		}

		//Funcion que devuelve la conexion a drupal, es igual a la de wordpress pero se crea otro objeto para las consultas
		function mdtw_conexion_bd_drupal($host, $bd, $user, $pass){
			$conexion_drupal = new mysqli($host, $user, $pass, $bd);

			if ($conexion_drupal -> connect_errno) {
				$this->connection_drupal=false;
			} else {
				if (!$conexion_drupal->set_charset("utf8")) {
				    $this->connection_drupal=false;
				} else {
				    $this->connection_drupal=true;
				}
			}

			return $conexion_drupal;
		}

		//Funcion que devuelve la conexion a wordpress, es igual a la de drupal pero se crea otro objeto para las consultas
		function mdtw_conexion_bd_wordpress($host, $bd, $user, $pass){
			$conexion_wordpress = new mysqli($host, $user, $pass, $bd);

			if ($conexion_wordpress -> connect_errno) {
				$this->conection_wordpress=false;
			} else {
				if (!$conexion_wordpress->set_charset("utf8")) {
				    $this->conection_wordpress=false;
				} else {
				    $this->conection_wordpress=true;
				}
			}

			return $conexion_wordpress;
		}

		//Funcion que crea la variable con el valor segun el resultado de las conexiones a las BD
		function mdtw_conexion_bd_resultado(){
			if($this->connection_drupal==true && $this->conection_wordpress==true){
				$this->mdtw_mensaje_conection="Conexion establecida...";
			} else {
				if($this->connection_drupal==true){
					$this->mdtw_mensaje_conection="Solo se ha podido establecer la conexion con la BD de drupal";
				} else {
					if($this->conection_wordpress==true){
						$this->mdtw_mensaje_conection="Solo se ha podido establecer la conexion con la BD de wordpress";
					} else {
						$this->mdtw_mensaje_conection="No se ha podido establecer la conexion con las BD";
					}
				}
			}
		}

		//Funcion para controlar que datos se van a migrar
		function mdtw_migracion_controlador(){
			if(isset($this->tables)){
				if(in_array('users', $this->tables)){
					$this->mdtw_migracion_users();
				} else {
					if(in_array('posts', $this->tables)){
						$new_user_id=$this->mdtw_new_user();
						$user_id_dp=null;
						$this->mdtw_migracion_post($user_id_dp, $new_user_id);
					}
				}
				if(in_array('comments', $this->tables)){
					$this->mdtw_migracion_comments();
				}
				if(in_array('terms', $this->tables)){
					$this->mdtw_migracion_term();
				}
				if(in_array('term_relationships', $this->tables)){
					$this->mdtw_migracion_term_relationships();
				}
			}
		}

		//Funcion que hace la migracion de la tabla users cojiendo los datos de drupal y los inserta en wordpress
    function mdtw_migracion_users(){
        $query_drupal_users = "SELECT uid, name, pass, mail, created, status FROM ".$this->prefijo_dp."users WHERE uid!=0";

        if(!$resultado_drupal_users=$this->db_drupal->query($query_drupal_users)){
			$this->mdtw_mensaje_tabla_users="Fallo al extraer los datos de la tabla ".$this->prefijo_dp."users de drupal";
        }

		while ($users = $resultado_drupal_users->fetch_assoc()) {
			$user=$this->mdtw_comprobar("user", $users['uid']);

    		if($user!=false){
    			continue;
    		}

			$query_wp_users = "INSERT INTO ".$this->prefijo_wp."users (ID, user_login, user_pass, user_nicename, user_email, user_url, user_registered, user_activation_key, user_status, display_name) VALUES ";
			
			$name=$this->db_wordpress->real_escape_string($users["name"]);
			$name=sanitize_user($name);
			$pass=sanitize_text_field($users["pass"]);
			$mail=sanitize_email($users["mail"]);
			$status=intval($users["status"]);
			$tiempo=intval($users["created"]);
			$tiempo=$this->mdtw_fecha($tiempo);
			
        	$query_wp_users =$query_wp_users."( null, '$name', '$pass', '$name', '$mail', '', '$tiempo', '', $status , '$name');";

        	if(!$this->db_wordpress->query($query_wp_users)){
				$this->mdtw_mensaje_tabla_users="Fallo al introducir los datos de la tabla ".$this->prefijo_wp."users de wordpress, usuario: ".$users["name"];
        	} else {
        		$user_id_wp=$this->db_wordpress->insert_id;
				$uid=intval($users["uid"]);
				
				$this->mdtw_migracion_usermeta($uid, $user_id_wp, $name);

				if(in_array('posts', $this->tables)){
					$this->mdtw_migracion_post($uid, $user_id_wp);
				}
			}
		}
		$this->mdtw_mensaje_tabla_users="Exito al introducir los datos de la tabla ".$this->prefijo_wp."users de wordpress";
    }

    //Funcion que hace la migracion de la tabla usermeta cojiendo los datos de drupal y los inserta en wordpress
    //Solo se realiza si se selecciona antes la tabla users
    function mdtw_migracion_usermeta($user_id_dp=null, $user_id_wp, $user_name){
    	$query_drupal_usermeta="SELECT pv.fid, pv.value FROM ".$this->prefijo_dp."profile_values pv WHERE pv.uid=$user_id_dp";

    	if(!$resultado_drupal_usermeta=$this->db_drupal->query($query_drupal_usermeta)){
			$this->mdtw_mensaje_tabla_usermeta="Fallo al extraer los datos de la tabla ".$this->prefijo_dp."profile_values de drupal";
        }

        //4:nombre y 5:apellido
        $usermeta_first_name="";
        $usermeta_last_name="";

        while($usermeta_dp_profile_values = $resultado_drupal_usermeta->fetch_assoc()) {
        	switch ($usermeta_dp_profile_values["fid"]) {
        		case '4':
        			$usermeta_first_name = $this->db_wordpress->real_escape_string($usermeta_dp_profile_values['value']);
        			$usermeta_first_name = sanitize_text_field($usermeta_first_name);
					break;
        		
        		case '5':
        			$usermeta_last_name = $this->db_wordpress->real_escape_string($usermeta_dp_profile_values['value']);
        			$usermeta_last_name = sanitize_text_field($usermeta_last_name);
					break;
        	}
        }

        $query_drupal_usermeta="SELECT r.name FROM ".$this->prefijo_dp."users_roles us, ".$this->prefijo_dp."role r WHERE us.rid=r.rid AND uid=$user_id_dp";

    	if(!$resultado_drupal_usermeta=$this->db_drupal->query($query_drupal_usermeta)){
			$this->mdtw_mensaje_tabla_usermeta="Fallo al extraer los datos de las tablas ".$this->prefijo_dp."users_roles us, ".$this->prefijo_dp."role de drupal";
        }

	$name="";
	
        //Asignacion de los roles

        while($usermeta_dp_role = $resultado_drupal_usermeta->fetch_assoc()) {
		$name = $this->db_wordpress->real_escape_string($usermeta_dp_role["name"]);
        	$name=sanitize_text_field($name);
	}

	if(!isset($name)){
		$array_usermeta=array("(null, ".$user_id_wp.", 'nickname', '".$user_name."'),",
	    		"(null, ".$user_id_wp.", 'first_name', '".$usermeta_first_name."'),",
	    		"(null, ".$user_id_wp.", 'last_name', '".$usermeta_last_name."'),",
	    		"(null, ".$user_id_wp.", 'description', null),",
	    		"(null, ".$user_id_wp.", 'rich_editing', 'true'),",
	    		"(null, ".$user_id_wp.", 'comment_shortcuts', 'false'),",
	    		"(null, ".$user_id_wp.", 'admin_color', 'fresh'),",
	    		"(null, ".$user_id_wp.", 'use_ssl', 0),",
	    		"(null, ".$user_id_wp.", 'show_admin_bar_front', 'true'),",
	    		"(null, ".$user_id_wp.", 'wp_capabilities', ''),",
	    		"(null, ".$user_id_wp.", 'wp_user_level', ''),",
	    		"(null, ".$user_id_wp.", 'dismissed_wp_pointers', ''),",
	    		"(null, ".$user_id_wp.", 'id_user_drupal', ".$user_id_dp.")"
	    	);
	} else {
		$rol=get_role($name);
			
        	if(!isset($rol)){
        		add_role($name, __($name),array( ));
        	} 

        	switch ($name) {
        		case 'administrator':
        			$wp_capabilities="a:1:{s:13:\"administrator\";b:1;}";
        			$wp_user_level=10;
        			break;

        		case 'editor':
        			$wp_capabilities="a:1:{s:6:\"editor\";b:1;}";
        			$wp_user_level=7;
        			break;

        		case 'author':
        			$wp_capabilities="a:1:{s:6:\"author\";b:1;}";
        			$wp_user_level=2;
        			break;

        		case 'contributor':
        			$wp_capabilities="a:1:{s:11:\"contributor\";b:1;}";
        			$wp_user_level=1;
        			break;
        		
        		default:
        			$wp_capabilities="a:1:{s:".strlen($name).":\"".$name."\";b:1;}";
        			$wp_user_level=0;
        			break;
        	}

		$wp_capabilities=$this->db_wordpress->real_escape_string($wp_capabilities);
		$wp_capabilities=sanitize_text_field($wp_capabilities);
		$wp_user_level=intval($wp_user_level);

		$array_usermeta=array("(null, ".$user_id_wp.", 'nickname', '".$user_name."'),",
	    		"(null, ".$user_id_wp.", 'first_name', '".$usermeta_first_name."'),",
	    		"(null, ".$user_id_wp.", 'last_name', '".$usermeta_last_name."'),",
	    		"(null, ".$user_id_wp.", 'description', null),",
	    		"(null, ".$user_id_wp.", 'rich_editing', 'true'),",
	    		"(null, ".$user_id_wp.", 'comment_shortcuts', 'false'),",
	    		"(null, ".$user_id_wp.", 'admin_color', 'fresh'),",
	    		"(null, ".$user_id_wp.", 'use_ssl', 0),",
	    		"(null, ".$user_id_wp.", 'show_admin_bar_front', 'true'),",
	    		"(null, ".$user_id_wp.", 'wp_capabilities', '".$wp_capabilities."'),",
	    		"(null, ".$user_id_wp.", 'wp_user_level', '".$wp_user_level."'),",
	    		"(null, ".$user_id_wp.", 'dismissed_wp_pointers', ''),",
	    		"(null, ".$user_id_wp.", 'id_user_drupal', ".$user_id_dp.")"
	    	);
        }

    	$query_wp_usermeta="INSERT INTO ".$this->prefijo_wp."usermeta (umeta_id, user_id, meta_key, meta_value) VALUES ";

	    $query_usermeta="";
	    
	   	for($i=0; $i<count($array_usermeta); $i++){
	   		$query_usermeta=$query_usermeta.$array_usermeta[$i];
	   	}

	   	$query_wp_usermeta=$query_wp_usermeta.$query_usermeta;

	   	if(!$this->db_wordpress->query($query_wp_usermeta)){
			$this->mdtw_mensaje_tabla_usermeta="Fallo al introducir los datos de la tabla ".$this->prefijo_wp."usermeta de wordpress, id_usuario: ".$user_id;
        } else {
        	$this->mdtw_mensaje_tabla_usermeta="Exito al introducir los datos de los usuarios en la tabla ".$this->prefijo_wp."usermeta de wordpress";
		}
    }

    //Funcion que hace la migracion de la tabla post cojiendo los datos de drupal y los inserta en wordpress
    //Se puede realizar de seleccionando o no la tabla users, si no se selecciona se crea uno por defecto
    function mdtw_migracion_post($user_id_dp, $user_id_wp){
    	if(!isset($user_id_dp)){
    		$query_drupal_post="SELECT n.nid, n.created, nr.body, nr.title, n.status, n.changed, n.type, n.comment FROM ".$this->prefijo_dp."node n, ".$this->prefijo_dp."node_revisions nr WHERE n.nid = nr.nid AND n.vid = nr.vid";
    	} else {
    		$query_drupal_post="SELECT n.nid, n.created, nr.body, nr.title, n.status, n.changed, n.type, n.comment FROM ".$this->prefijo_dp."node n, ".$this->prefijo_dp."node_revisions nr WHERE n.nid = nr.nid AND n.vid = nr.vid AND n.uid=".$user_id_dp;
    	}

    	if(!$resultado_drupal_post=$this->db_drupal->query($query_drupal_post)){
			$this->mdtw_mensaje_tabla_posts="Fallo al extraer los datos de las tablas ".$this->prefijo_dp."node y ".$this->prefijo_dp."node_revisions de drupal";
        }

        while ($posts = $resultado_drupal_post->fetch_assoc()) {
			$nid=intval($posts['nid']);
        	$post=$this->mdtw_comprobar("post", $nid);

    		if($post!=false){
    			continue;
    		}

        	$query_wp_posts = "INSERT INTO ".$this->prefijo_wp."posts (id, post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt, post_status, comment_status, ping_status, post_password, post_name, to_ping, pinged, post_modified, post_modified_gmt, post_content_filtered, post_parent, guid, menu_order, post_type, post_mime_type, comment_count) VALUES ";
			
			$body=$this->db_wordpress->real_escape_string($posts["body"]);
			$body=sanitize_text_field($body);
			$title=$this->db_wordpress->real_escape_string($posts["title"]);
			$title=sanitize_title($title);
			
			$post_name=$this->mdtw_normalize($posts["title"]);
			$post_name=$this->db_wordpress->real_escape_string($post_name);
			$post_name=sanitize_text_field($post_name);
			
			$tiempo_creacion=intval($posts["created"]);
			$tiempo_modificacion=intval($posts["changed"]);
			
			$tiempo_modificacion_unix=$tiempo_modificacion;
			
			$tiempo_creacion=$this->mdtw_fecha($tiempo_creacion);
			$tiempo_modificacion=$this->mdtw_fecha($tiempo_modificacion);

			if($posts["status"]==0){
				$post_status="draft";
				$post_comment_status="closed";
				$post_ping_status="closed";
			} else {
				$post_status="publish";
				$post_comment_status="open";
				$post_ping_status="open";
			}

			$type=sanitize_text_field($posts["type"]);
			switch($type) {
				case 'post':
					$post_type="post";
					break;
				
				case 'page':
					$post_type="page";
					break;

				case 'attachement':
					$post_type="attachement";
					break;
				
				case 'revision':
					$post_type="revision";
					break;
				
				case 'nav_menu_item':
					$post_type="nav_menu_item";
					break;
				
				default:
					$post_type="post";
					break;
			}
			
			$post_type=sanitize_text_field($post_type);
			
			$comment=intval($posts["comment"]);
			
        	$query_wp_posts =$query_wp_posts."( null, '$user_id_wp', '$tiempo_creacion', '$tiempo_creacion', '$body', '$title', '', '$post_status', '$post_comment_status', '$post_ping_status', '', '$post_name', '', '', '$tiempo_modificacion', '$tiempo_modificacion', '', 0, '', 0, '$post_type', '', '$comment');";

        	if(!$this->db_wordpress->query($query_wp_posts)){
				$this->mdtw_mensaje_tabla_posts="Fallo al introducir los datos de la tabla ".$this->prefijo_wp."posts de wordpress";
        	} else {
        		$post_id_wp=$this->db_wordpress->insert_id;
				
				$query_wp_posts_siteurl="SELECT option_value FROM ".$this->prefijo_wp."options WHERE option_name='siteurl';";
        		
				if(!$siteurl=$this->db_wordpress->query($query_wp_posts_siteurl)){
					$this->mdtw_mensaje_tabla_posts="Fallo al extraer el siteurl de la tabla ".$this->prefijo_wp."options de wordpress";
	        	}

				while ($guid = $siteurl->fetch_assoc()){
					$post_guid=$guid["option_value"]."/?p=".$post_id_wp;
				}

        		$query_wp_posts_guid="UPDATE ".$this->prefijo_wp."posts SET guid='".$post_guid."' WHERE ID=$post_id_wp";

        		if(!$siteurl=$this->db_wordpress->query($query_wp_posts_guid)){
					$this->mdtw_mensaje_tabla_posts="Fallo al actualizar la guid del post: ".$posts["title"];
	        	}

        		$this->mdtw_mensaje_tabla_posts="Exito al introducir el post ".$title;

				$this->mdtw_migracion_postmeta($post_id_wp, $tiempo_modificacion_unix, $nid);
			}
        }
        $this->mdtw_mensaje_tabla_posts="Exito al introducir los datos de la tabla ".$this->prefijo_wp."posts de wordpress";
    }

    //Funcion que hace la migracion de la tabla postmeta cojiendo los datos heredados de la funcion migracion_post y insertandolos en wp
    function mdtw_migracion_postmeta($post_id_wp, $tiempo_modificacion_unix, $id_post_drupal){
    	$query_wp_postmeta="INSERT INTO ".$this->prefijo_wp."postmeta (meta_id, post_id, meta_key, meta_value) VALUES ";

    	$array_postmeta=array("(null, ".$post_id_wp.", '_edit_last', 1),",
    		"(null, ".$post_id_wp.", '_edit_lock', '".$tiempo_modificacion_unix.":1'),",
    		"(null, ".$post_id_wp.", 'id_post_drupal', ".$id_post_drupal."),"
    		);

    	$query_postmeta="";
	    
	   	for($i=0; $i<count($array_postmeta); $i++){
	   		$query_postmeta=$query_postmeta.$array_postmeta[$i];
	   	}

	   	$query_wp_postmeta=$query_wp_postmeta.trim($query_postmeta, ",").";";

	   	if(!$this->db_wordpress->query($query_wp_postmeta)){
			$this->mdtw_mensaje_tabla_postmeta="Fallo al introducir los datos de la tabla ".$this->prefijo_wp."postmeta de wordpress, id_post: ".$post_id_wp;
        } else {
        	$this->mdtw_mensaje_tabla_postmeta="Exito al introducir los datos de los posts en la tabla ".$this->prefijo_wp."postmeta de wordpress";
		}
    }

    //Funcion que hace la migracion de la tabla comments usando datos de nr_comments y insertandolos en wp
    //Se comprueba si el usuario y el post existen
    function mdtw_migracion_comments(){
    	$query_drupal_comments="SELECT c.hostname, c.timestamp, c.comment, c.status, c.name, c.mail, c.uid, c.nid, c.cid FROM ".$this->prefijo_dp."comments c WHERE c.uid!=0";

    	if(!$resultado_drupal_comments=$this->db_drupal->query($query_drupal_comments)){
    		$this->mdtw_mensaje_tabla_comments="Fallo al extraer los datos de la tabla ".$this->prefijo_dp."comments de drupal";
    	}

    	while ($comments = $resultado_drupal_comments->fetch_assoc()){
			$nid=intval($comments['nid']);
    		$post=$this->mdtw_comprobar("post", $nid);
			$cid=intval($comments['cid']);
    		$comment=$this->mdtw_comprobar("comment", $cid);

    		if($post==false){
    			continue;
    		}

    		if($comment!=false){
    			continue;
    		}

			$uid=intval($comments['uid']);
    		$user=$this->mdtw_comprobar("user", $uid);

    		$query_wp_comments="INSERT INTO ".$this->prefijo_wp."comments (comment_ID, comment_post_ID, comment_author, comment_author_email, comment_author_url, comment_author_IP, comment_date, comment_date_gmt, comment_content, comment_karma, comment_approved, comment_agent, comment_type, comment_parent, user_id) VALUES ";

			$tiempo=intval($comments['timestamp']);
    		$tiempo=$this->mdtw_fecha($tiempo);
			
			$name=$this->db_wordpress->real_escape_string($comments['name']);
			$name=sanitize_text_field($name);
			$commen=$this->db_wordpress->real_escape_string($comments['comment']);
			$commen=sanitize_text_field($commen);
			$mail=sanitize_email($comments['mail']);
			$hostname=sanitize_text_field($comments['hostname']);
			$status=intval($comments['status']);

    		$query_wp_comments=$query_wp_comments."(null, $post, '$name', '$mail', '', 'hostname', '$tiempo', '$tiempo', '$commen', 0, 'status', '', '', 0, $user);";

    		if(!$resultado_wp_comments=$this->db_wordpress->query($query_wp_comments)){
	    		$this->mdtw_mensaje_tabla_comments="Fallo al introducir los datos de la tabla ".$this->prefijo_wp."comments de wordpress";
	    	}

	    	$comment_id_wp=$this->db_wordpress->insert_id;

	    	$this->mdtw_migracion_commentmeta($comment_id_wp, $cid);
    	}
    	$this->mdtw_mensaje_tabla_comments="Exito al introducir los datos de los comentarios en la tabla ".$this->prefijo_wp."comments de wordpress";
    }

    //Funcion que hace la migracion de la tabla commentmeta cojiendo los datos heredados de la funcion migracion_comments y insertandolos en wp
    function mdtw_migracion_commentmeta($comment_id_wp, $comment_tid){
    	$query_wp_commentmeta="INSERT INTO ".$this->prefijo_wp."commentmeta (meta_id, comment_id, meta_key, meta_value) VALUES ";

	    $array_commentmeta=array(
	    	"(null, ".$comment_id_wp.", 'id_comment_drupal', ".$comment_tid."),"
    	);

    	$query_commentmeta="";
	    
	   	for($i=0; $i<count($array_commentmeta); $i++){
	   		$query_commentmeta=$query_commentmeta.$array_commentmeta[$i];
	   	}

	   	$query_wp_commentmeta=$query_wp_commentmeta.trim($query_commentmeta, ",").";";

	   	if(!$this->db_wordpress->query($query_wp_commentmeta)){
			$this->mdtw_mensaje_tabla_commentmeta="Fallo al introducir los datos a la tabla ".$this->prefijo_wp."commentmeta de wordpress";
        }

        $this->mdtw_mensaje_tabla_commentmeta="Exito al introducir los datos a la tabla ".$this->prefijo_wp."commentmeta de wordpress";
    }

    //Funcion que hace la migracion de la tabla wp_terms usando datos de nr_term_data y insertandolos en wp
    function mdtw_migracion_term(){
    	$query_drupal_terms="SELECT tid, name FROM ".$this->prefijo_dp."term_data";

    	if(!$resultado_drupal_terms=$this->db_drupal->query($query_drupal_terms)){
    		$this->mdtw_mensaje_tabla_term="Fallo al extraer los datos de la tabla ".$this->prefijo_dp."term_data de drupal";
    	}

    	while ($terms = $resultado_drupal_terms->fetch_assoc()){
			$tid=intval($terms['tid']);
    		$term=$this->mdtw_comprobar("term", $tid);

    		if($term!=false){
    			continue;
    		}

    		$query_drupal_term_condition="SELECT v.name FROM ".$this->prefijo_dp."vocabulary v, ".$this->prefijo_dp."term_data td WHERE v.vid = td.vid AND td.tid = $tid";

    		if(!$resultado_drupal_condition=$this->db_drupal->query($query_drupal_term_condition)){
	    		$this->mdtw_mensaje_tabla_term="Fallo al extraer los datos de la tabla ".$this->prefijo_dp."vocabulary de drupal";
	    	}

	    	while ($term_condition = $resultado_drupal_condition->fetch_assoc()){
				$name_condition=sanitize_text_field($term_condition['name']);
		    	switch ($name_condition) {
	    			case 'Topic':
	    				$name_condition="nav_menu";
	    				break;
	    			
	    			case 'Categoría':
	    				$name_condition="category";
	    				break;

	    			case 'Tags':
	    				$name_condition="post_tag";
	    				break;
	    		}
	    	}

	    	if(in_array($name_condition, $this->tables)){
	    		$query_wp_terms="INSERT INTO ".$this->prefijo_wp."terms (term_id, name, slug, term_group) VALUES ";

			$name_terms=sanitize_text_field($terms['name']);
			$name_terms=$this->db_wordpress->real_escape_string($name_terms);
	    		$slug=str_replace(" ", "-", $name_terms);

	    		$query_wp_terms=$query_wp_terms."(null, '$name_terms', '$slug', 0);";

	    		if(!$resultado_wp_terms=$this->db_wordpress->query($query_wp_terms)){
		    		$this->mdtw_mensaje_tabla_term="Fallo al introducir los datos a la tabla ".$this->prefijo_wp."terms de wordpress";
		    	}

		    	$term_id_wp=$this->db_wordpress->insert_id;

		    	$this->mdtw_migracion_termmeta($term_id_wp, $tid);

		    	$this->mdtw_migracion_term_taxonomy($term_id_wp, $tid, $name_condition);
	    	}
    	}
    	$this->mdtw_mensaje_tabla_term="Exito al introducir los datos a la tabla ".$this->prefijo_wp."terms de wordpress";
    }

    //Funcion que hace la migracion de la tabla termmeta cojiendo los datos heredados de la funcion migracion_term y insertandolos en wp
    function mdtw_migracion_termmeta($term_id_wp, $term_tid){
    	$query_wp_termmeta="INSERT INTO ".$this->prefijo_wp."termmeta (meta_id, term_id, meta_key, meta_value) VALUES ";

	    $array_termmeta=array(
	    	"(null, ".$term_id_wp.", 'id_term_drupal', ".$term_tid."),"
    	);

    	$query_termmeta="";
	    
	   	for($i=0; $i<count($array_termmeta); $i++){
	   		$query_termmeta=$query_termmeta.$array_termmeta[$i];
	   	}

	   	$query_wp_termmeta=$query_wp_termmeta.trim($query_termmeta, ",").";";

	   	if(!$this->db_wordpress->query($query_wp_termmeta)){
			$this->mdtw_mensaje_tabla_termmeta="Fallo al introducir los datos a la tabla ".$this->prefijo_wp."termmeta de wordpress";
        }

        $this->mdtw_mensaje_tabla_termmeta="Exito al introducir los datos a la tabla ".$this->prefijo_wp."termmeta de wordpress";
    }

    //Funcion que hace la migracion de la tabla wp_terms usando datos de nr_term_data y insertandolos en wp
    function mdtw_migracion_term_taxonomy($term_id_wp, $term_tid, $name){
    	$query_drupal_term_taxonomy="SELECT td.description, th.parent FROM ".$this->prefijo_dp."term_data td, ".$this->prefijo_dp."term_hierarchy th WHERE td.tid = th.tid AND td.tid = $term_tid";

    	if(!$resultado_drupal_term_taxonomy=$this->db_drupal->query($query_drupal_term_taxonomy)){
    		$this->mdtw_mensaje_tabla_term_taxonomy="Fallo al extraer los datos de las tablas ".$this->prefijo_dp."vocabulary, ".$this->prefijo_dp."term_data, ".$this->prefijo_dp."term_hierarchy de drupal";
    	}

    	while ($term_taxonomy = $resultado_drupal_term_taxonomy->fetch_assoc()){
    		$query_wp_term_taxonomy="INSERT INTO ".$this->prefijo_wp."term_taxonomy (term_taxonomy_id, term_id, taxonomy, description, parent, count) VALUES ";

			if(isset($term_taxonomy['description'])){
				$description=$this->db_wordpress->real_escape_string($term_taxonomy['description']);
				$description=sanitize_text_field($description);
			} else {
				$description=sanitize_text_field($term_taxonomy['description']);
			}
			
    		$query_wp_term_taxonomy=$query_wp_term_taxonomy."(null, $term_id_wp, '$name', '$description', 0, 0);";

    		if(!$resultado_wp_term_taxonomy=$this->db_wordpress->query($query_wp_term_taxonomy)){
	    		$this->mdtw_mensaje_tabla_term_taxonomy="Fallo al introducir los datos a la tabla ".$this->prefijo_wp."term_taxonomy de wordpress";
	    	}
    	}
		
    	$this->mdtw_mensaje_tabla_term_taxonomy="Exito al introducir los datos a la tabla ".$this->prefijo_wp."term_taxonomy de wordpress";
    }

    //Funcion que hace la migracion de la tabla wp_terms usando datos de ############## y insertandolos en wp
    function mdtw_migracion_term_relationships(){
    	$query_drupal_term_relationships="SELECT tid, vid FROM ".$this->prefijo_dp."term_node";

    	if(!$resultado_drupal_term_relationships=$this->db_drupal->query($query_drupal_term_relationships)){
    		$this->mdtw_mensaje_tabla_term_relationships="Fallo al extraer los datos de la tabla ".$this->prefijo_dp."term_node de drupal";
    	}

    	while ($term_relationships = $resultado_drupal_term_relationships->fetch_assoc()){
    		$query_wp_term_relationships="INSERT INTO ".$this->prefijo_wp."term_relationships (object_id, term_taxonomy_id, term_order) VALUES ";

			$term=intval($term_relationships['tid']);
			$post=intval($term_relationships['vid']);
			
    		$term=$this->mdtw_comprobar("term", $term);
    		$post=$this->mdtw_comprobar("post", $post);

    		if($term==false){
    			continue;
    		}
    		if($post==false){
    			continue;
    		}

    		$query_wp_term_relationships=$query_wp_term_relationships."($post, $term, 0);";

    		if(!$resultado_wp_term_relationships=$this->db_wordpress->query($query_wp_term_relationships)){
	    		$this->mdtw_mensaje_tabla_term_relationships="Fallo al introducir los datos de la tabla ".$this->prefijo_wp."term_relationships de wordpress";
	    	}

	    	$query_wp_count="SELECT count FROM ".$this->prefijo_wp."term_taxonomy WHERE term_taxonomy_id=$term";

	    	if(!$resultado_count=$this->db_wordpress->query($query_wp_count)){
	    		$this->mdtw_mensaje_tabla_term_relationships="Fallo al extraer los datos de la tabla ".$this->prefijo_wp."term_taxonomy de wordpress";
	    	}

	    	while ($count = $resultado_count->fetch_assoc()){
	    		$con=$count['count']+1;
	    	}

	    	$query_wp_count="UPDATE ".$this->prefijo_wp."term_taxonomy SET count=$con WHERE term_taxonomy_id=$term";

	    	if(!$resultado_count=$this->db_wordpress->query($query_wp_count)){
	    		$this->mdtw_mensaje_tabla_term_relationships="Fallo al actualizar los datos de la tabla ".$this->prefijo_wp."term_taxonomy de wordpress";
	    	}
    	}
    	$this->mdtw_mensaje_tabla_term_relationships="Exito al introducir los datos a la tabla ".$this->prefijo_wp."term_relationships de wordpress";
    }

    //Funcion que pasandole 2 parametros, lo que se quiere comprobar: user, post o term y el id de drupal, devuelve el id en wordpress
    function mdtw_comprobar($comprobar, $comprobar_dato){
    	if ($comprobar=='user') {
    		$comprobar_tabla=$this->prefijo_wp."usermeta";
    		$comprobar_meta_key="id_user_drupal";
    	}
    	if ($comprobar=='post') {
    		$comprobar_tabla=$this->prefijo_wp."postmeta";
    		$comprobar_meta_key="id_post_drupal";
    	}
    	if ($comprobar=='term') {
    		$comprobar_tabla=$this->prefijo_wp."termmeta";
    		$comprobar_meta_key="id_term_drupal";
    	}
    	if ($comprobar=='comment') {
    		$comprobar_tabla=$this->prefijo_wp."commentmeta";
    		$comprobar_meta_key="id_comment_drupal";
    	}

    	$query_wordpress_comprobar="SELECT ".$comprobar."_id FROM $comprobar_tabla WHERE meta_key='$comprobar_meta_key' AND meta_value=$comprobar_dato";

    	if(!$resultado_comprobar=$this->db_wordpress->query($query_wordpress_comprobar)){
    		$this->mdtw_mensaje_tabla_comprobar="Fallo al extraer los datos de la tabla $comprobar_tabla de wordpress";
    	}

    	$num_rows=mysqli_num_rows($resultado_comprobar);

    	if(empty($num_rows)){
    		if ($comprobar=='post') {
    			return false;
    		} 
    		if ($comprobar=='user') {
    			$query_wordpress_comprobar_defect_user="SELECT ".$comprobar."_id FROM $comprobar_tabla WHERE meta_key='$comprobar_meta_key' AND meta_value=''";

	    		if(!$resultado_defect_user=$this->db_wordpress->query($query_wordpress_comprobar_defect_user)){
	    			$this->mdtw_mensaje_tabla_comprobar="Fallo al extraer los datos de la tabla $comprobar_tabla de wordpress";
	    		}

	    		while($id=$resultado_defect_user->fetch_assoc()){
					$id_user=intval($id['id']);
	    			return $id_user;
	    		}
    		}
    		if ($comprobar=='term') {
    			return false;
    		} 
    		if ($comprobar=='comment') {
    			return false;
    		}
    	} else {
    		while($comprobar_result = $resultado_comprobar->fetch_assoc()) {
    			if ($comprobar=='user') {
    				return intval($comprobar_result['user_id']);
    			}
    			if ($comprobar=='post') {
    				return intval($comprobar_result['post_id']);
    			}
    			if ($comprobar=='term') {
	    			return intval($comprobar_result['term_id']);
	    		} 
	    		if ($comprobar=='comment') {
	    			return intval($comprobar_result['comment_id']);
	    		} 
    		}
    	}
    } 

    //Funcion que crea un nuevo usuario
    function mdtw_new_user(){
    	$query_wp_user_comprobar_count="SELECT ID FROM ".$this->prefijo_wp."users WHERE user_login='user_migration'";

    	if(!$resultado_wp_comprobar_user_count=$this->db_wordpress->query($query_wp_user_comprobar_count)){
			$this->mdtw_mensaje_new_user="Fallo al comprobar si existia el usuario 'user_migration' en la tabla ".$this->prefijo_wp."users";
        }

        $num_rows=mysqli_num_rows($resultado_wp_comprobar_user_count);
        
        if($num_rows!=0){
        	$query_wp_user_comprobar="SELECT ID FROM ".$this->prefijo_wp."users WHERE user_login='user_migration'";

	    	if(!$resultado_wp_comprobar_user=$this->db_wordpress->query($query_wp_user_comprobar)){
				$this->mdtw_mensaje_new_user="Fallo al comprobar si existia el usuario 'user_migration' en la tabla ".$this->prefijo_wp."users";
	        }

        	while($comprobar_user = $resultado_wp_comprobar_user->fetch_assoc()) {
        		unset($this->mdtw_mensaje_new_user);
	        	return intval($comprobar_user['ID']);
	        }
        } else {
        	$query_wp_user = "INSERT INTO ".$this->prefijo_wp."users (ID, user_login, user_pass, user_nicename, user_email, user_url, user_registered, user_activation_key, user_status, display_name) VALUES ";
			
	        $query_wp_user =$query_wp_user."( null, 'user_migration', '".md5("user_migration")."', 'user_migration', 'migration@migration.test', '', '2016-01-19 14:45:54', '', 0, 'user_migration');";

	        if(!$this->db_wordpress->query($query_wp_user)){
				$this->mdtw_mensaje_new_user="Fallo al crear un nuevo usuario: 'user_migration' en la tabla ".$this->prefijo_wp."users de wordpress";
	        } else {
	        	$this->mdtw_mensaje_new_user="Se ha creado el usuario 'user_migration', al cual se le asignaran los post";
				return $new_user_id=$this->db_wordpress->insert_id;
			}
        }
    }

    //Formateo de la fecha de unix
	function mdtw_fecha($fecha){
		$date = DateTime::createFromFormat('U', $fecha);
		$tiempo=$date->format('Y-m-d H:i:s');
		return $tiempo;
	}

	//Cambia el string sustitullendo los caracteres especiales por normales y sustituyendo los espacios en blanco por guion bajo
	function mdtw_normalize($string) {
	    $table = array(
	        'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
	        'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
	        'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
	        'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
	        'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
	        'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
	        'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
	        'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r',
	    );

	    $string=strtr($string, $table);
	    $string=str_replace(' ', '_', $string);
		$string=str_replace("'", "_", $string);
		return str_replace("\"", "_", $string);
	}
	}
?>
