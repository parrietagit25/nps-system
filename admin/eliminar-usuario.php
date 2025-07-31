<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$usuarioId = $_GET['id'] ?? null;
if (!$usuarioId) {
    header('Location: usuarios.php');
    exit;
}

$db = new Database();

// Verificar que el usuario existe
$sql = "SELECT * FROM usuarios WHERE id = ?";
$stmt = $db->query($sql, [$usuarioId]);
$usuario = $stmt->fetch();

if (!$usuario) {
    header('Location: usuarios.php');
    exit;
}

// Verificar que no se está eliminando a sí mismo
if ($usuarioId == $_SESSION['admin_id']) {
    header('Location: usuarios.php?error=self_delete');
    exit;
}

// Procesar la eliminación
if ($_POST && isset($_POST['confirmar'])) {
    try {
        // Iniciar transacción
        $db->getConnection()->beginTransaction();
        
        // Verificar si el usuario tiene encuestas creadas
        $sql = "SELECT COUNT(*) as total FROM encuestas WHERE creado_por = ?";
        $stmt = $db->query($sql, [$usuarioId]);
        $encuestas_count = $stmt->fetch()['total'];
        
        if ($encuestas_count > 0) {
            // Si tiene encuestas, solo desactivar el usuario
            $sql = "UPDATE usuarios SET activo = 0 WHERE id = ?";
            $db->query($sql, [$usuarioId]);
            $mensaje = "El usuario tiene encuestas asociadas. Se ha desactivado en lugar de eliminar.";
        } else {
            // Si no tiene encuestas, eliminar completamente
            $sql = "DELETE FROM usuarios WHERE id = ?";
            $db->query($sql, [$usuarioId]);
            $mensaje = "Usuario eliminado completamente.";
        }
        
        // Confirmar transacción
        $db->getConnection()->commit();
        
        // Redirigir con mensaje de éxito
        header('Location: usuarios.php?eliminado=1');
        exit;
        
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $db->getConnection()->rollBack();
        $error = 'Error al eliminar el usuario: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar Usuario - NPS System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .btn-danger { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); border: none; border-radius: 25px; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-user-times text-danger"></i> Eliminar Usuario</h2>
                    <a href="usuarios.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-exclamation-triangle"></i> Confirmar Eliminación
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <h5><i class="fas fa-warning"></i> ¡Atención!</h5>
                            <p class="mb-0">Estás a punto de eliminar al usuario <strong>"<?php echo htmlspecialchars($usuario['nombre']); ?>"</strong>.</p>
                        </div>
                        
                        <h5>Información del Usuario:</h5>
                        <ul class="list-group list-group-flush mb-4">
                            <li class="list-group-item">
                                <i class="fas fa-user text-primary"></i>
                                <strong>Nombre:</strong> <?php echo htmlspecialchars($usuario['nombre']); ?>
                            </li>
                            <li class="list-group-item">
                                <i class="fas fa-envelope text-info"></i>
                                <strong>Email:</strong> <?php echo htmlspecialchars($usuario['email']); ?>
                            </li>
                            <li class="list-group-item">
                                <i class="fas fa-user-shield text-warning"></i>
                                <strong>Rol:</strong> <?php echo ucfirst($usuario['rol']); ?>
                            </li>
                            <li class="list-group-item">
                                <i class="fas fa-calendar text-success"></i>
                                <strong>Fecha Registro:</strong> <?php echo date('d/m/Y H:i', strtotime($usuario['fecha_creacion'])); ?>
                            </li>
                        </ul>
                        
                        <?php
                        // Verificar si tiene encuestas asociadas
                        $sql = "SELECT COUNT(*) as total FROM encuestas WHERE creado_por = ?";
                        $stmt = $db->query($sql, [$usuarioId]);
                        $encuestas_count = $stmt->fetch()['total'];
                        ?>
                        
                        <?php if ($encuestas_count > 0): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Nota:</strong> Este usuario tiene <?php echo $encuestas_count; ?> encuesta(s) asociada(s). 
                                Si procedes, el usuario será desactivado en lugar de eliminado para preservar los datos de las encuestas.
                            </div>
                        <?php else: ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i>
                                <strong>Esta acción no se puede deshacer.</strong> El usuario será eliminado permanentemente del sistema.
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="d-flex justify-content-between">
                                <a href="usuarios.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                                <button type="submit" name="confirmar" class="btn btn-danger">
                                    <i class="fas fa-trash"></i> 
                                    <?php echo $encuestas_count > 0 ? 'Desactivar Usuario' : 'Sí, Eliminar Usuario'; ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 