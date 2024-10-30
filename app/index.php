	<?php  
		if ( ! defined( 'ABSPATH' ) ) exit;
		current_user_can( "manage_options" );
	?>
	<?php require "lib.php"; ?>
	<?php require "migration_drupal_to_wp.php" ?>
	<?php 
	$get_mdtw_salir=filter_input(INPUT_GET, "mdtw_salir");
	if (isset($get_mdtw_salir)) {
		session_unset();
	}

	$post_mdtw_run=filter_input(INPUT_POST, "mdtw_run");
	if(isset($post_mdtw_run)){
		$post_mdtw_drupal_host=filter_input(INPUT_POST, "mdtw_drupal_host");
		if (!filter_var($post_mdtw_drupal_host, FILTER_VALIDATE_IP) === false) {
    			$_SESSION["mdtw_drupal_host"]=$post_mdtw_drupal_host;
		} else {
    			$_SESSION["mdtw_mensaje_datos_introducidos"]="La ip introducida no es valida";
		}

		$post_mdtw_wordpress_host=filter_input(INPUT_POST, "mdtw_wordpress_host");
		if (!filter_var($post_mdtw_wordpress_host, FILTER_VALIDATE_IP) === false) {
    			$_SESSION["mdtw_wordpress_host"]=$post_mdtw_wordpress_host;
		} else {
    			$_SESSION["mdtw_mensaje_datos_introducidos"]="La ip introducida no es valida";
		}
	
		$post_mdtw_drupal_bd=filter_input(INPUT_POST, "mdtw_drupal_bd");
		$post_mdtw_drupal_user=filter_input(INPUT_POST, "mdtw_drupal_user");
		$post_mdtw_drupal_password=filter_input(INPUT_POST, "mdtw_drupal_password");
		$post_mdtw_wordpress_bd=filter_input(INPUT_POST, "mdtw_wordpress_bd");
		$post_mdtw_wordpress_user=filter_input(INPUT_POST, "mdtw_wordpress_user");
		$post_mdtw_wordpress_password=filter_input(INPUT_POST, "mdtw_wordpress_password");
		$post_mdtw_drupal_prefijo=filter_input(INPUT_POST, "mdtw_drupal_prefijo");
		$post_mdtw_wordpress_prefijo=filter_input(INPUT_POST, "mdtw_wordpress_prefijo");

		$_SESSION["mdtw_drupal_bd"]=filter_var($post_mdtw_drupal_bd, FILTER_SANITIZE_STRING);
		$_SESSION["mdtw_drupal_user"]=filter_var($post_mdtw_drupal_user, FILTER_SANITIZE_STRING);
		$_SESSION["mdtw_drupal_password"]=filter_var($post_mdtw_drupal_password, FILTER_SANITIZE_STRING);

		$_SESSION["mdtw_wordpress_bd"]=filter_var($post_mdtw_wordpress_bd, FILTER_SANITIZE_STRING);
		$_SESSION["mdtw_wordpress_user"]=filter_var($post_mdtw_wordpress_user, FILTER_SANITIZE_STRING);
		$_SESSION["mdtw_wordpress_password"]=filter_var($post_mdtw_wordpress_password, FILTER_SANITIZE_STRING);

		$_SESSION["mdtw_drupal_prefijo"]=filter_var($post_mdtw_drupal_prefijo, FILTER_SANITIZE_STRING);
		$_SESSION["mdtw_wordpress_prefijo"]=filter_var($post_mdtw_wordpress_prefijo, FILTER_SANITIZE_STRING);

		$post_mdtw_tablas=filter_input(INPUT_POST, "mdtw_tablas", FILTER_DEFAULT , FILTER_REQUIRE_ARRAY);
		$mdtw_tablas = filter_var_array($_POST["mdtw_tablas"], FILTER_SANITIZE_STRING);

		$migration_drupal_to_wp=new migration_drupal_to_wp($_SESSION["mdtw_drupal_host"], $_SESSION["mdtw_drupal_bd"], $_SESSION["mdtw_drupal_user"], $_SESSION["mdtw_drupal_password"], $_SESSION["mdtw_wordpress_host"], $_SESSION["mdtw_wordpress_bd"], $_SESSION["mdtw_wordpress_user"], $_SESSION["mdtw_wordpress_password"], $mdtw_tablas, $_SESSION["mdtw_drupal_prefijo"], $_SESSION["mdtw_wordpress_prefijo"]);

		if(isset($migration_drupal_to_wp->mdtw_mensaje_conection)){
			$_SESSION["mdtw_mensaje_conection"]=$migration_drupal_to_wp->mdtw_mensaje_conection;
		}
		if(isset($migration_drupal_to_wp->mdtw_mensaje_tabla_users)){
			$_SESSION["mdtw_mensaje_tabla_users"]=$migration_drupal_to_wp->mdtw_mensaje_tabla_users;
		}
		if(isset($migration_drupal_to_wp->mdtw_mensaje_tabla_usermeta)){
			$_SESSION["mdtw_mensaje_tabla_usermeta"]=$migration_drupal_to_wp->mdtw_mensaje_tabla_usermeta;
		}if(isset($migration_drupal_to_wp->mdtw_mensaje_new_user)){
			$_SESSION["mdtw_mensaje_new_user"]=$migration_drupal_to_wp->mdtw_mensaje_new_user;
		}
		if(isset($migration_drupal_to_wp->mdtw_mensaje_tabla_posts)){
			$_SESSION["mdtw_mensaje_tabla_posts"]=$migration_drupal_to_wp->mdtw_mensaje_tabla_posts;
		}
		if(isset($migration_drupal_to_wp->mdtw_mensaje_tabla_postmeta)){
			$_SESSION["mdtw_mensaje_tabla_postmeta"]=$migration_drupal_to_wp->mdtw_mensaje_tabla_postmeta;
		}
		if(isset($migration_drupal_to_wp->mdtw_mensaje_tabla_comments)){
			$_SESSION["mdtw_mensaje_tabla_comments"]=$migration_drupal_to_wp->mdtw_mensaje_tabla_comments;
		}
		if(isset($migration_drupal_to_wp->mdtw_mensaje_tabla_commentmeta)){
			$_SESSION["mdtw_mensaje_tabla_commentmeta"]=$migration_drupal_to_wp->mdtw_mensaje_tabla_commentmeta;
		}
		if(isset($migration_drupal_to_wp->mdtw_mensaje_tabla_term)){
			$_SESSION["mdtw_mensaje_tabla_term"]=$migration_drupal_to_wp->mdtw_mensaje_tabla_term;
		}
		if(isset($migration_drupal_to_wp->mdtw_mensaje_tabla_termmeta)){
			$_SESSION["mdtw_mensaje_tabla_termmeta"]=$migration_drupal_to_wp->mdtw_mensaje_tabla_termmeta;
		}
		if(isset($migration_drupal_to_wp->mdtw_mensaje_tabla_term_taxonomy)){
			$_SESSION["mdtw_mensaje_tabla_term_taxonomy"]=$migration_drupal_to_wp->mdtw_mensaje_tabla_term_taxonomy;
		}
		if(isset($migration_drupal_to_wp->mdtw_mensaje_tabla_term_relationships)){
			$_SESSION["mdtw_mensaje_tabla_term_relationships"]=$migration_drupal_to_wp->mdtw_mensaje_tabla_term_relationships;
		}
		if(isset($migration_drupal_to_wp->mdtw_mensaje_tabla_comprobar)){
			$_SESSION["mdtw_mensaje_tabla_comprobar"]=$migration_drupal_to_wp->mdtw_mensaje_tabla_comprobar;
		}
	}
	
	if(isset($_SESSION["mdtw_drupal_host"])){
		$mdtw_drupal_host=$_SESSION["mdtw_drupal_host"];
	} else {
		$mdtw_drupal_host="";
	}

	if(isset($_SESSION["mdtw_drupal_bd"])){
		$mdtw_drupal_bd=$_SESSION["mdtw_drupal_bd"];
	} else {
		$mdtw_drupal_bd="";
	}

	if(isset($_SESSION["mdtw_drupal_user"])){
		$mdtw_drupal_user=$_SESSION["mdtw_drupal_user"];
	} else {
		$mdtw_drupal_user="";
	}

	if(isset($_SESSION["mdtw_drupal_password"])){
		$mdtw_drupal_password=$_SESSION["mdtw_drupal_password"];
	} else {
		$mdtw_drupal_password="";
	}

	if(isset($_SESSION["mdtw_wordpress_host"])){
		$mdtw_wordpress_host=$_SESSION["mdtw_wordpress_host"];
	} else {
		$mdtw_wordpress_host="";
	}

	if(isset($_SESSION["mdtw_wordpress_bd"])){
		$mdtw_wordpress_bd=$_SESSION["mdtw_wordpress_bd"];
	} else {
		$mdtw_wordpress_bd="";
	}

	if(isset($_SESSION["mdtw_wordpress_user"])){
		$mdtw_wordpress_user=$_SESSION["mdtw_wordpress_user"];
	} else {
		$mdtw_wordpress_user="";
	}
	
	if(isset($_SESSION["mdtw_wordpress_password"])){
		$mdtw_wordpress_password=$_SESSION["mdtw_wordpress_password"];
	} else {
		$mdtw_wordpress_password="";
	}

	if(isset($_SESSION["mdtw_drupal_prefijo"])){
		$mdtw_drupal_prefijo=$_SESSION["mdtw_drupal_prefijo"];
	} else {
		$mdtw_drupal_prefijo="";
	}

	if(isset($_SESSION["mdtw_wordpress_prefijo"])){
		$mdtw_wordpress_prefijo=$_SESSION["mdtw_wordpress_prefijo"];
	} else {
		$mdtw_wordpress_prefijo="";
	}

	?>
	<div class="container">
		<form action= "<?php echo ADMIN_URL().'admin.php?page=migration' ?>" method="post">
			<div class="row">
				<div class="col-md-6">
					<h2 class="form-signin-heading">Drupal 6</h2>
					<div class="form-group">
						<input name="mdtw_drupal_host" class="form-control" type="text" autofocus="" required placeholder="Host (IP)" value="<?php echo $mdtw_drupal_host; ?>" maxlength="15" pattern="[0-9]{1,3}[.]{1}[0-9]{1,3}[.]{1}[0-9]{1,3}[.]{1}[0-9]{1,3}">
					</div>
					<div class="form-group">
						<input name="mdtw_drupal_bd" class="form-control" type="text" autofocus="" required placeholder="BD" value="<?php echo $mdtw_drupal_bd; ?>">
					</div>
					<div class="form-group">
						<input name="mdtw_drupal_user" class="form-control" type="text" autofocus="" required placeholder="User" value="<?php echo $mdtw_drupal_user; ?>">
					</div>
					<div class="form-group">
						<input name="mdtw_drupal_password" class="form-control" type="password" required placeholder="Password" value="<?php echo $mdtw_drupal_password; ?>">
					</div>
					<div class="form-group">
						<input name="mdtw_drupal_prefijo" class="form-control" type="text" required placeholder="Prefijo" value="<?php echo $mdtw_drupal_prefijo; ?>">
					</div>
				</div>
				<div class="col-md-6">
					<h2 class="form-signin-heading">Wordpress 4</h2>
					<div class="form-group">
						<input name="mdtw_wordpress_host" class="form-control" type="text" autofocus="" required placeholder="Host (IP)" value="<?php echo $mdtw_wordpress_host; ?>" maxlength="15" pattern="[0-9]{1,3}[.]{1}[0-9]{1,3}[.]{1}[0-9]{1,3}[.]{1}[0-9]{1,3}">
					</div>
					<div class="form-group">
						<input name="mdtw_wordpress_bd" class="form-control" type="text" autofocus="" required placeholder="BD" value="<?php echo $mdtw_wordpress_bd; ?>">
					</div>
					<div class="form-group">
						<input name="mdtw_wordpress_user" class="form-control" type="text" autofocus="" required placeholder="User" value="<?php echo $mdtw_wordpress_user; ?>">
					</div>
					<div class="form-group">
						<input name="mdtw_wordpress_password" class="form-control" type="password" required placeholder="Password" value="<?php echo $mdtw_wordpress_password; ?>">
					</div>
					<div class="form-group">
						<input name="mdtw_wordpress_prefijo" class="form-control" type="text" required placeholder="Prefijo" value="<?php echo $mdtw_wordpress_prefijo; ?>">
					</div>
				</div>
				<div class="col-md-12">
					<center>
						<h2 class="form-signin-heading">Tablas de wordpress</h2>
						<label class="checkbox-inline">
							<input type="checkbox" name="mdtw_tablas[]" value="users" > users
						</label>
						<label class="checkbox-inline">
							<input type="checkbox" name="mdtw_tablas[]" value="posts" > posts
						</label>
						<label class="checkbox-inline">
							<input type="checkbox" name="mdtw_tablas[]" value="comments" > comments
						</label>
						<label class="checkbox-inline">
							<input type="checkbox" name="mdtw_tablas[]" value="terms" id="term"> terms
						</label>
						<label class="checkbox-inline">
							<input type="checkbox" name="mdtw_tablas[]" value="term_relationships" > term_relationships
						</label>
					</center>
				</div>
				<div class="col-md-12" id="ocult" style="display:none;">
					<center>
						<label class="checkbox-inline">
							<input type="checkbox" name="mdtw_tablas[]" value="nav_menu" checked> nav_menu
						</label>
						<label class="checkbox-inline">
							<input type="checkbox" name="mdtw_tablas[]" value="category" checked> category
						</label>
						<label class="checkbox-inline">
							<input type="checkbox" name="mdtw_tablas[]" value="post_tag" checked> post_tag
						</label>
					</center>
				</div>
				<div class="col-md-12" style="display:none;">
					<input type="checkbox" name="mdtw_run" checked>
				</div>
				<br>
				<div class="col-md-12">
					<button class="btn btn-primary btn-lg btn-block" type="submit">Test Conection</button>
				</div>
			</div>
		</form>
	</div>
	<br>
	<?php
	if(isset($_SESSION["mdtw_mensaje_datos_introducidos"])){
		migration_drupal_to_wp_print_container_12($_SESSION["mdtw_mensaje_datos_introducidos"]);
	}
	if(isset($_SESSION["mdtw_mensaje_conection"])){
		migration_drupal_to_wp_print_container_12($_SESSION["mdtw_mensaje_conection"]);
	}
	if(isset($_SESSION["mdtw_mensaje_tabla_users"])){
		migration_drupal_to_wp_print_container_12($_SESSION["mdtw_mensaje_tabla_users"]);
	}
	if(isset($_SESSION["mdtw_mensaje_tabla_usermeta"])){
		migration_drupal_to_wp_print_container_12($_SESSION["mdtw_mensaje_tabla_usermeta"]);
	}
	if(isset($_SESSION["mdtw_mensaje_new_user"])){
		migration_drupal_to_wp_print_container_12($_SESSION["mdtw_mensaje_new_user"]);
	}
	if(isset($_SESSION["mdtw_mensaje_tabla_posts"])){
		migration_drupal_to_wp_print_container_12($_SESSION["mdtw_mensaje_tabla_posts"]);
	}
	if(isset($_SESSION["mdtw_mensaje_tabla_postmeta"])){
		migration_drupal_to_wp_print_container_12($_SESSION["mdtw_mensaje_tabla_postmeta"]);
	}
	if(isset($_SESSION["mdtw_mensaje_tabla_comments"])){
		migration_drupal_to_wp_print_container_12($_SESSION["mdtw_mensaje_tabla_comments"]);
	}
	if(isset($_SESSION["mdtw_mensaje_tabla_commentmeta"])){
		migration_drupal_to_wp_print_container_12($_SESSION["mdtw_mensaje_tabla_commentmeta"]);
	}
	if(isset($_SESSION["mdtw_mensaje_tabla_term"])){
		migration_drupal_to_wp_print_container_12($_SESSION["mdtw_mensaje_tabla_term"]);
	}
	if(isset($_SESSION["mdtw_mensaje_tabla_termmeta"])){
		migration_drupal_to_wp_print_container_12($_SESSION["mdtw_mensaje_tabla_termmeta"]);
	}
	if(isset($_SESSION["mdtw_mensaje_tabla_term_taxonomy"])){
		migration_drupal_to_wp_print_container_12($_SESSION["mdtw_mensaje_tabla_term_taxonomy"]);
	}
	if(isset($_SESSION["mdtw_mensaje_tabla_term_relationships"])){
		migration_drupal_to_wp_print_container_12($_SESSION["mdtw_mensaje_tabla_term_relationships"]);
	}
	if(isset($_SESSION["mdtw_mensaje_tabla_comprobar"])){
		migration_drupal_to_wp_print_container_12($_SESSION["mdtw_mensaje_tabla_comprobar"]);
	}

	$salir="<a href=\"".ADMIN_URL()."admin.php?page=migration&mdtw_salir=OK\">Salir</a>";
	migration_drupal_to_wp_print_container_12($salir);

	?>
	
