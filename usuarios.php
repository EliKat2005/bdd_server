<?php 
include 'includes/db.php'; 

$mensaje = "";

// --- 1. LÓGICA DE GUARDADO (Backend) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Recibir datos del formulario
        $nombre = $_POST['nombre'];
        $email  = $_POST['email'];
        $rol    = $_POST['rol_id'];
        $pass   = "123456"; // Contraseña por defecto

        // Validaciones básicas
        if (empty($nombre) || empty($email)) {
            throw new Exception("El nombre y el correo son obligatorios.");
        }

        // Preparar la consulta (INSERT)
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, rol_id, password_hash) VALUES (?, ?, ?, ?)");
        
        // Verificar si la preparación falló (ej. si la tabla no existe)
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta: " . $conn->error);
        }

        $stmt->bind_param("ssis", $nombre, $email, $rol, $pass);
        
        // Ejecutar
        $stmt->execute();

        // Si llega aquí, todo salió bien
        $mensaje = "<div class='alert alert-success mt-3 alert-dismissible fade show' role='alert'>
                        <i class='bi bi-check-circle-fill'></i> Usuario <strong>$nombre</strong> registrado y replicado correctamente.
                        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                    </div>";
        
    } catch (mysqli_sql_exception $e) {
        // Capturar errores específicos de la Base de Datos
        $error_txt = $e->getMessage();
        
        if (strpos($error_txt, 'Duplicate entry') !== false) {
            $mensaje = "<div class='alert alert-warning mt-3'>
                            <i class='bi bi-exclamation-triangle-fill'></i> El correo <strong>$email</strong> ya está registrado en el sistema.
                        </div>";
        } else {
            $mensaje = "<div class='alert alert-danger mt-3'>
                            <i class='bi bi-server'></i> Error de Base de Datos: $error_txt
                        </div>";
        }
    } catch (Exception $e) {
        // Capturar otros errores generales
        $mensaje = "<div class='alert alert-danger mt-3'>
                        <i class='bi bi-bug-fill'></i> Error del Sistema: " . $e->getMessage() . "
                    </div>";
    }
}

include 'includes/header.php'; 
?>

<div class="row mb-3">
    <div class="col-md-8">
        <h2>Gestión de Personal</h2>
        <p class="text-muted">Administra los accesos al sistema ERP (Alta Disponibilidad).</p>
    </div>
    <div class="col-md-4 text-end">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrear">
            <i class="bi bi-person-plus-fill"></i> Nuevo Usuario
        </button>
    </div>
</div>

<?php echo $mensaje; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Intentamos listar. Si falla la conexión, mostramos error en la tabla.
                    try {
                        $sql = "SELECT u.*, r.nombre as nombre_rol 
                                FROM usuarios u 
                                LEFT JOIN roles r ON u.rol_id = r.id 
                                WHERE u.deleted_at IS NULL 
                                ORDER BY u.id DESC";
                        
                        $resultado = $conn->query($sql);

                        if ($resultado && $resultado->num_rows > 0) {
                            while($fila = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $fila['id']; ?></td>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($fila['nombre']); ?></div>
                                    <small class="text-muted">Alta: <?php echo date('d/m/Y', strtotime($fila['created_at'])); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($fila['email']); ?></td>
                                <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($fila['nombre_rol']); ?></span></td>
                                <td><span class="badge bg-success">Activo</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                            <?php endwhile; 
                        } else {
                            echo "<tr><td colspan='6' class='text-center py-4'>No hay usuarios registrados o no se pudo leer la base de datos.</td></tr>";
                        }
                    } catch (Exception $e) {
                        echo "<tr><td colspan='6' class='text-center text-danger py-4'>Error al cargar lista: " . $e->getMessage() . "</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCrear" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="usuarios.php">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Registrar Nuevo Empleado</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre Completo</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej: Juan Pérez" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Correo Electrónico</label>
                        <input type="email" name="email" class="form-control" placeholder="juan@empresa.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rol en la Empresa</label>
                        <select name="rol_id" class="form-select" required>
                            <option value="" selected disabled>Seleccione un rol...</option>
                            <?php
                            // Llenar el select dinámicamente
                            try {
                                $roles = $conn->query("SELECT * FROM roles");
                                if($roles) {
                                    while($r = $roles->fetch_assoc()) {
                                        echo "<option value='".$r['id']."'>".$r['nombre']."</option>";
                                    }
                                }
                            } catch (Exception $e) {
                                echo "<option disabled>Error cargando roles</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
