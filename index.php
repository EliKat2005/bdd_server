<?php 
include 'includes/db.php'; 
include 'includes/header.php'; 

// Consultas rápidas para el dashboard
$total_clientes = $conn->query("SELECT COUNT(*) as c FROM clientes WHERE deleted_at IS NULL")->fetch_assoc()['c'];
$total_usuarios = $conn->query("SELECT COUNT(*) as c FROM usuarios WHERE deleted_at IS NULL")->fetch_assoc()['c'];
$total_roles    = $conn->query("SELECT COUNT(*) as c FROM roles")->fetch_assoc()['c'];
?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4">Panel de Control</h2>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-white bg-success h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Clientes Activos</h6>
                        <h2 class="display-4"><?php echo $total_clientes; ?></h2>
                    </div>
                    <i class="bi bi-briefcase fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-info h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Personal</h6>
                        <h2 class="display-4"><?php echo $total_usuarios; ?></h2>
                    </div>
                    <i class="bi bi-people fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-warning h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Roles Definidos</h6>
                        <h2 class="display-4"><?php echo $total_roles; ?></h2>
                    </div>
                    <i class="bi bi-shield-lock fs-1"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Clientes Recientes</h5>
            </div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Empresa</th>
                            <th>Contacto</th>
                            <th>Email</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM clientes WHERE deleted_at IS NULL ORDER BY created_at DESC LIMIT 5";
                        $result = $conn->query($sql);
                        while($row = $result->fetch_assoc()):
                        ?>
                        <tr>
                            <td>#<?php echo $row['id']; ?></td>
                            <td><strong><?php echo $row['empresa']; ?></strong></td>
                            <td><?php echo $row['contacto_nombre']; ?></td>
                            <td><?php echo $row['email']; ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary">Ver</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>