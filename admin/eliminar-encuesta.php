<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/NPSService.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$encuestaId = $_GET['id'] ?? null;
if (!$encuestaId) {
    header('Location: encuestas.php');
    exit;
}

$db = new Database();

// Verificar que la encuesta existe
$sql = "SELECT * FROM encuestas WHERE id = ?";
$stmt = $db->query($sql, [$encuestaId]);
$encuesta = $stmt->fetch();

if (!$encuesta) {
    header('Location: encuestas.php');
    exit;
}

// Procesar la eliminación
if ($_POST && isset($_POST['confirmar'])) {
    try {
        // Iniciar transacción
        $db->getConnection()->beginTransaction();
        
        // Eliminar logs de email asociados primero (porque depende de destinatarios)
        $sql = "DELETE FROM logs_email WHERE destinatario_id IN (SELECT id FROM destinatarios WHERE encuesta_id = ?)";
        $db->query($sql, [$encuestaId]);
        
        // Eliminar respuestas asociadas
        $sql = "DELETE FROM respuestas WHERE encuesta_id = ?";
        $db->query($sql, [$encuestaId]);
        
        // Eliminar destinatarios asociados
        $sql = "DELETE FROM destinatarios WHERE encuesta_id = ?";
        $db->query($sql, [$encuestaId]);
        
        // Finalmente eliminar la encuesta
        $sql = "DELETE FROM encuestas WHERE id = ?";
        $db->query($sql, [$encuestaId]);
        
        // Confirmar transacción
        $db->getConnection()->commit();
        
        // Redirigir con mensaje de éxito
        header('Location: encuestas.php?eliminado=1');
        exit;
        
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $db->getConnection()->rollBack();
        $error = 'Error al eliminar la encuesta: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar Encuesta - NPS System</title>
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
                    <h2><i class="fas fa-trash text-danger"></i> Eliminar Encuesta</h2>
                    <a href="encuestas.php" class="btn btn-secondary">
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
                            <p class="mb-0">Estás a punto de eliminar la encuesta <strong>"<?php echo htmlspecialchars($encuesta['titulo']); ?>"</strong>.</p>
                        </div>
                        
                        <h5>Esta acción eliminará permanentemente:</h5>
                        <ul class="list-group list-group-flush mb-4">
                            <li class="list-group-item">
                                <i class="fas fa-clipboard-list text-primary"></i>
                                <strong>La encuesta:</strong> <?php echo htmlspecialchars($encuesta['titulo']); ?>
                            </li>
                            <li class="list-group-item">
                                <i class="fas fa-users text-info"></i>
                                <strong>Todos los destinatarios asociados</strong>
                            </li>
                            <li class="list-group-item">
                                <i class="fas fa-reply text-success"></i>
                                <strong>Todas las respuestas recibidas</strong>
                            </li>
                            <li class="list-group-item">
                                <i class="fas fa-envelope text-warning"></i>
                                <strong>Todos los logs de email</strong>
                            </li>
                        </ul>
                        
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            <strong>Esta acción no se puede deshacer.</strong> Una vez eliminada, todos los datos se perderán permanentemente.
                        </div>
                        
                        <form method="POST">
                            <div class="d-flex justify-content-between">
                                <a href="encuestas.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                                <button type="submit" name="confirmar" class="btn btn-danger">
                                    <i class="fas fa-trash"></i> Sí, Eliminar Encuesta
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