<?php
// Configurar reporte de errores para que los fallos de conexión lancen excepciones (vital para el salto)
mysqli_report(MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR);

// LISTA DE SERVIDORES (PRIORIDAD: MAIN -> ESCLAVO 1 -> ESCLAVO 2)
$servidores = [
    ['ip' => '192.168.50.10', 'rol' => 'MAESTRO (Principal)'],
    ['ip' => '192.168.50.11', 'rol' => 'ESCLAVO 1 (Respaldo)'],
    ['ip' => '192.168.50.12', 'rol' => 'ESCLAVO 2 (Emergencia)']
];

$user = "admin_web";
$pass = "password123"; 
$db   = "consultoria_erp";
$conn = null;
$servidor_actual = "";

// BUCLE DE FAILOVER (INTENTO DE CONEXIÓN EN CASCADA)
foreach ($servidores as $srv) {
    try {
        // Inicializar MySQLi
        $conn = mysqli_init();
        
        // Configurar un TIMEOUT de 2 segundos (para que salte rápido si está apagado)
        $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 2);
        
        // Intentar conectar
        $conn->real_connect($srv['ip'], $user, $pass, $db);
        
        // Si llegamos aquí, ¡conectó!
        $servidor_actual = $srv['rol'] . " [" . $srv['ip'] . "]";
        $conn->set_charset("utf8");
        break; // Salimos del bucle, ya tenemos conexión
        
    } catch (Exception $e) {
        // Si falla, no hacemos nada, dejamos que el bucle continúe con el siguiente servidor
        continue;
    }
}

// SI TERMINA EL BUCLE Y NO HAY CONEXIÓN
if (!$conn || $conn->connect_errno) {
    die("<div class='alert alert-danger text-center mt-5'>
            <h1>🚨 ERROR CRÍTICO DE SISTEMA 🚨</h1>
            <p>Se ha perdido la conexión con el Cluster de Base de Datos.</p>
            <small>Ninguno de los 3 nodos (Maestro, Esclavo 1, Esclavo 2) responde.</small>
         </div>");
}

// --- VISUALIZACIÓN DEL ESTADO (AQUÍ ESTÁ EL MENSAJE QUE BUSCABAS) ---
// Estilos según a quién estemos conectados
$color_alerta = "alert-success"; // Verde para Maestro
if (strpos($servidor_actual, 'ESCLAVO') !== false) {
    $color_alerta = "alert-warning"; // Amarillo para Esclavos (Alerta)
}

echo "<div class='alert $color_alerta text-center fw-bold m-0 p-2' role='alert'>
        <i class='bi bi-hdd-network'></i> CONECTADO A: $servidor_actual
      </div>";
?>
