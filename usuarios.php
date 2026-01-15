<?php 
include 'includes/db.php'; 

// --- LÓGICA DE INTERACCIÓN (PROCESAR FORMULARIO) ---
$mensaje = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $email  = $_POST['email'];
    $rol    = $_POST['rol_id'];
    $pass   = "123456"; // Contraseña por defecto para el lab

    // Consulta de Inserción (Interactividad pura)
    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, rol_id, password_hash) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssis", $nombre, $email, $rol, $pass);
    
    if ($stmt->execute()) {
        $mensaje = "<div class='alert alert-success'>¡Usuario registrado correctamente!</div>";
    } else {
        $mensaje = "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }
}

include 'includes/header.php'; 
?>

<div class="row mb-3">
    <div class="col-md-8">
        <h2>Gestión de Personal</h2>
        <p class="text-muted">Administra los accesos al sistema ERP.</p>
    </div>
    <div class="col-md-4 text-end">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrear">
            <i class="bi bi-person-plus"></i> Nuevo Usuario
        </button>
    </div>
</div>

<?php echo $mensaje; ?>

<div class="card">
    <div class="card-body">
        <table class="table table-striped align-middle">
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
                // JOIN para traer el nombre del Rol en lugar del ID número
                $sql = "SELECT u.*, r.nombre as nombre_rol 
                        FROM usuarios u 
                        JOIN roles r ON u.rol_id = r.id 
                        WHERE u.deleted_at IS NULL 
                        ORDER BY u.id DESC";
                $resultado = $conn->query($sql);

                while($fila = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $fila['id']; ?></td>
                    <td>
                        <div class="fw-bold"><?php echo $fila['nombre']; ?></div>
                        <small class="text-muted">Registrado: <?php echo date('d/m/Y', strtotime($fila['created_at'])); ?></small>
                    </td>
                    <td><?php echo $fila['email']; ?></td>
                    <td><span class="badge bg-info text-dark"><?php echo $fila['nombre_rol']; ?></span></td>
                    <td><span class="badge bg-success">Activo</span></td>
                    <td>
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalCrear" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="usuarios.php">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Nuevo Empleado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Nombre Completo</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Correo Electrónico</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Rol en la Empresa</label>
                        <select name="rol_id" class="form-select" required>
                            <?php
                            // Llenamos el select dinámicamente desde la BD
                            $roles = $conn->query("SELECT * FROM roles");
                            while($r = $roles->fetch_assoc()) {
                                echo "<option value='".$r['id']."'>".$r['nombre']."</option>";
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
