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
$sql = "SELECT * FROM encuestas WHERE id = ?";
$stmt = $db->query($sql, [$encuestaId]);
$encuesta = $stmt->fetch();

if (!$encuesta) {
    header('Location: encuestas.php');
    exit;
}

$success = '';
$error = '';

if ($_POST) {
    $datos = [
        'titulo' => trim($_POST['titulo'] ?? ''),
        'descripcion' => trim($_POST['descripcion'] ?? ''),
        'pregunta' => trim($_POST['pregunta'] ?? ''),
        'fecha_inicio' => $_POST['fecha_inicio'] ?? null,
        'fecha_fin' => $_POST['fecha_fin'] ?? null,
        'estado' => $_POST['estado'] ?? 'borrador'
    ];
    
    if (empty($datos['titulo']) || empty($datos['pregunta'])) {
        $error = 'El título y la pregunta son obligatorios.';
    } else {
        $sql = "UPDATE encuestas SET titulo = ?, descripcion = ?, pregunta = ?, fecha_inicio = ?, fecha_fin = ?, estado = ? WHERE id = ?";
        $stmt = $db->query($sql, [
            $datos['titulo'],
            $datos['descripcion'],
            $datos['pregunta'],
            $datos['fecha_inicio'],
            $datos['fecha_fin'],
            $datos['estado'],
            $encuestaId
        ]);
        
        $success = 'Encuesta actualizada exitosamente.';
        
        // Recargar datos de la encuesta
        $stmt = $db->query("SELECT * FROM encuestas WHERE id = ?", [$encuestaId]);
        $encuesta = $stmt->fetch();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Encuesta - NPS System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .btn-primary { background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); border: none; border-radius: 25px; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-edit"></i> Editar Encuesta</h2>
                    <a href="encuestas.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h4 class="mb-0">Editar: <?php echo htmlspecialchars($encuesta['titulo']); ?></h4>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="titulo" class="form-label">Título de la Encuesta *</label>
                                    <input type="text" class="form-control" id="titulo" name="titulo" 
                                           value="<?php echo htmlspecialchars($encuesta['titulo']); ?>" required>
                                </div>
                                
                                <div class="col-md-12 mb-3">
                                    <label for="descripcion" class="form-label">Descripción</label>
                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?php echo htmlspecialchars($encuesta['descripcion']); ?></textarea>
                                </div>
                                
                                <div class="col-md-12 mb-3">
                                    <label for="pregunta" class="form-label">Pregunta NPS *</label>
                                    <textarea class="form-control" id="pregunta" name="pregunta" rows="2" required><?php echo htmlspecialchars($encuesta['pregunta']); ?></textarea>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                                           value="<?php echo $encuesta['fecha_inicio']; ?>">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="fecha_fin" class="form-label">Fecha de Finalización</label>
                                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" 
                                           value="<?php echo $encuesta['fecha_fin']; ?>">
                                </div>
                                
                                <div class="col-md-12 mb-4">
                                    <label for="estado" class="form-label">Estado</label>
                                    <select class="form-select" id="estado" name="estado">
                                        <option value="borrador" <?php echo $encuesta['estado'] == 'borrador' ? 'selected' : ''; ?>>Borrador</option>
                                        <option value="activa" <?php echo $encuesta['estado'] == 'activa' ? 'selected' : ''; ?>>Activa</option>
                                        <option value="inactiva" <?php echo $encuesta['estado'] == 'inactiva' ? 'selected' : ''; ?>>Inactiva</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="encuestas.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Guardar Cambios
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