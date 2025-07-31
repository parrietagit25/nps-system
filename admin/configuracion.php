<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$success = '';
$error = '';

if ($_POST) {
    // Aquí puedes agregar lógica para actualizar configuraciones
    $success = 'Configuración actualizada correctamente.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - NPS System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .btn-primary { background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); border: none; border-radius: 25px; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 bg-dark text-white p-0" style="min-height: 100vh;">
                <div class="p-4">
                    <h4 class="mb-4">
                        <i class="fas fa-chart-line"></i> NPS System
                    </h4>
                    <nav class="nav flex-column">
                        <a class="nav-link text-white-50" href="index.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a class="nav-link text-white-50" href="encuestas.php">
                            <i class="fas fa-clipboard-list"></i> Encuestas
                        </a>
                        <a class="nav-link text-white-50" href="resultados.php">
                            <i class="fas fa-chart-bar"></i> Resultados
                        </a>
                        <a class="nav-link text-white-50" href="destinatarios.php">
                            <i class="fas fa-users"></i> Destinatarios
                        </a>
                        <a class="nav-link text-white-50" href="usuarios.php">
                            <i class="fas fa-user-cog"></i> Usuarios
                        </a>
                        <a class="nav-link text-white active" href="configuracion.php">
                            <i class="fas fa-cog"></i> Configuración
                        </a>
                        <a class="nav-link text-white-50" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-cog"></i> Configuración del Sistema</h2>
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

                <!-- Configuración de SendGrid -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-envelope"></i> Configuración de SendGrid
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>API Key:</strong> <?php echo substr(SENDGRID_API_KEY, 0, 10) . '...'; ?></p>
                                <p><strong>Email Remitente:</strong> <?php echo SENDGRID_FROM_EMAIL; ?></p>
                                <p><strong>Nombre Remitente:</strong> <?php echo SENDGRID_FROM_NAME; ?></p>
                            </div>
                            <div class="col-md-6">
                                <a href="test-sendgrid.php" class="btn btn-info">
                                    <i class="fas fa-test-tube"></i> Probar SendGrid
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Configuración de Base de Datos -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-database"></i> Configuración de Base de Datos
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Host:</strong> <?php echo DB_HOST; ?></p>
                                <p><strong>Base de Datos:</strong> <?php echo DB_NAME; ?></p>
                                <p><strong>Usuario:</strong> <?php echo DB_USER; ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>URL de la Aplicación:</strong> <?php echo APP_URL; ?></p>
                                <p><strong>Nombre de la App:</strong> <?php echo APP_NAME; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información del Sistema -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle"></i> Información del Sistema
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Versión de PHP:</strong> <?php echo PHP_VERSION; ?></p>
                                <p><strong>Servidor:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido'; ?></p>
                                <p><strong>Directorio:</strong> <?php echo __DIR__; ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Fecha Actual:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
                                <p><strong>Zona Horaria:</strong> <?php echo date_default_timezone_get(); ?></p>
                                <p><strong>Memoria Usada:</strong> <?php echo round(memory_get_usage() / 1024 / 1024, 2); ?> MB</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Acciones del Sistema -->
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="fas fa-tools"></i> Acciones del Sistema
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <a href="backup-database.php" class="btn btn-outline-primary w-100 mb-2">
                                    <i class="fas fa-download"></i> Backup Base de Datos
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="clear-logs.php" class="btn btn-outline-warning w-100 mb-2">
                                    <i class="fas fa-trash"></i> Limpiar Logs
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="export-data.php" class="btn btn-outline-success w-100 mb-2">
                                    <i class="fas fa-file-export"></i> Exportar Datos
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 