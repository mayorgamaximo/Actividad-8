<?php

function enqueue_styles_child_theme() {

	$parent_style = 'parent-style';
	$child_style  = 'child-style';

	wp_enqueue_style( $parent_style,
				get_template_directory_uri() . '/style.css' );

	wp_enqueue_style( $child_style,
				get_stylesheet_directory_uri() . '/style.css',
				array( $parent_style ),
				wp_get_theme()->get('Version')
				);
}
add_action( 'wp_enqueue_scripts', 'enqueue_styles_child_theme' );


/* Añade aquí tus funciones personalizadas */

function guardar_datos_registro() {
    if (isset($_POST['registrar'])) {
        $nombre = sanitize_text_field($_POST['nombre']);
        $email = sanitize_email($_POST['email']);

        
        $db_host = 'localhost';
        $db_usuario = 'root'; 
        $db_contrasena = ''; 
        $db_nombre = 'usuarios_wordpress';

        $conexion = mysqli_connect($db_host, $db_usuario, $db_contrasena, $db_nombre);

        if (!$conexion) {
            die("Error al conectar a la base de datos: " . mysqli_connect_error());
        }

        $consulta_existente = "SELECT mail FROM usuarios WHERE mail = ?";
        $stmt_existente = mysqli_prepare($conexion, $consulta_existente);

        if ($stmt_existente) {
            mysqli_stmt_bind_param($stmt_existente, "s", $email);
            mysqli_stmt_execute($stmt_existente);
            mysqli_stmt_store_result($stmt_existente);

            if (mysqli_stmt_num_rows($stmt_existente) > 0) {
                echo '<p class="error">Este correo electrónico ya está registrado.</p>';
                mysqli_stmt_close($stmt_existente);
                mysqli_close($conexion);
                return;
            }
            mysqli_stmt_close($stmt_existente);
        } else {
            mysqli_close($conexion);
            return;
        }

        $consulta = "INSERT INTO usuarios (nombre, mail) VALUES (?, ?)";
        $stmt = mysqli_prepare($conexion, $consulta);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ss", $nombre, $email);

            
            if (mysqli_stmt_execute($stmt)) {
                echo '<p class="exito" >Registro exitoso.</p>'; 
            } else {
                echo '<p>Error al registrar los datos: ' . mysqli_stmt_error($stmt) . '</p>';
            }

            mysqli_stmt_close($stmt);
        } else {
            echo '<p>Error al preparar la consulta de inserción: ' . mysqli_error($conexion) . '</p>';
        }

        mysqli_close($conexion);
    }
}
add_action('wp_head', 'guardar_datos_registro');


function listar_usuarios_shortcode() {
    ob_start();

    
    $otro_db = new wpdb( "root", "", 'usuarios_wordpress', "localhost" );

    
    if ( !empty($otro_db->error) ) {
        echo '<div class="error-message">Error de conexión a la base de datos: ' . esc_html($otro_db->error) . '</div>';
    } else {
        $usuarios = $otro_db->get_results( "SELECT nombre, mail FROM usuarios" );

        if ( !empty($usuarios) ) {
            echo '<h3 class="user-list-title">Lista de usuarios registrados:</h3>';
            echo '<ul class="user-list">';
            foreach ( $usuarios as $usuario ) {
                echo '<li class="user-item">Nombre:<strong> ' . esc_html($usuario->nombre) . '</strong> - <span class="user-email">Gmail: ' . esc_html($usuario->mail) . '</span></li>';
            }
            echo '</ul>';
        } else {
            echo '<div class="no-users-message">No hay usuarios registrados aún.</div>';
        }

        
        echo '<a href="http://localhost/wordpress/registro/" class="registro-button">Ir al registro</a>';
    }

    return ob_get_clean();
}
add_shortcode('listar_usuarios', 'listar_usuarios_shortcode');

