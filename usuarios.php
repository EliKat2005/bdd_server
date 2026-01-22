<?php 
include 'includes/db.php'; 

$mensaje = "";

// LÓGICA DE PROCESAMIENTO DEL FORMULARIO
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Recibir datos del formulario
        $nombre = $_POST['nombre'];
        $email  = $_POST['email'];
        $rol    = $_POST['rol_id'];
        $pass   = "123456"; // Contraseña default

        // Validar que no lleguen vacíos
        if (empty($nombre) || empty($email)) {
            throw new Exception("El nombre y el correo son obligatorios.");
        }

        // Preparar la sentencia (Esto protege contra inyección SQL)
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, rol_id, password_hash) VALUES (?, ?, ?, ?)");
        
        // Si falla la preparación (ej. tabla no existe), lanzará excepción aquí
        if (!$stmt) {
            throw new Exception("Error preparando la consulta: " . $conn->error);
        }

        $stmt->bind_param("ssis", $nombre, $email, $rol, $pass);
        
        // Intentar ejecutar la inserción
        $stmt->execute();

        // Si llega aquí, es ÉXITO
        $mensaje = "<div class='alert alert-success mt-3'>
                        <i class='bi bi-check-circle'></i> ¡Usuario <strong>$nombre</strong> registrado correctamente!
                    </div>";
        
    } catch (mysqli_sql_exception $e) {
        // ERROR DE BASE DE DATOS (Ej: Email duplicado, fallo de conexión)
        $error_txt = $e->getMessage();
        
        // Mensaje amigable si es email duplicado
        if (strpos($error_txt, 'Duplicate entry') !== false) {
            $mensaje = "<div class='alert alert-warning mt-3'>
                            <i class='bi bi-exclamation-triangle'></i> El correo <strong>$email</strong> ya está registrado.
                        </div>";
        } else {
            $mensaje = "<div class='alert alert-danger mt-3'>
                            <i class='bi bi-x-circle'></i> Error de Base de Datos: $error_txt
                        </div>";
        }
    } catch (Exception $e) {
        // OTROS ERRORES GENÉRICOS
        $mensaje = "<div class='alert alert-danger mt-3'>
                        <i class='bi bi-bug'></i> Error: " . $e->getMessage() . "
                    </div>";
    }
}

include 'includes/header.php'; 
?>
