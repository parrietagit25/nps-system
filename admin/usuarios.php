<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$db = new Database();
$sql = "SELECT * FROM usuarios ORDER BY fecha_creacion DESC";
$stmt = $db->query($sql);
$usuarios = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios - NPS System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .table { border-radius: 10px; overflow: hidden; }
        .badge { border-radius: 20px; padding: 8px 15px; }
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
                        <a class="nav-link text-white active" href="usuarios.php">
                            <i class="fas fa-user-cog"></i> Usuarios
                        </a>
                        <a class="nav-link text-white-50" href="configuracion.php">
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
                    <h2><i class="fas fa-user-cog"></i> Gestión de Usuarios</h2>
                    <a href="crear-usuario.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Usuario
                    </a>
                </div>

                <?php if (isset($_GET['eliminado']) && $_GET['eliminado'] == '1'): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i>
                        <strong>¡Usuario eliminado exitosamente!</strong> El usuario ha sido eliminado del sistema.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['creado']) && $_GET['creado'] == '1'): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i>
                        <strong>¡Usuario creado exitosamente!</strong> El nuevo usuario ha sido agregado al sistema.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['editado']) && $_GET['editado'] == '1'): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i>
                        <strong>¡Usuario actualizado exitosamente!</strong> Los datos del usuario han sido modificados.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white text-center">
                            <div class="card-body">
                                <i class="fas fa-users fa-2x mb-3"></i>
                                <h4><?php echo count($usuarios); ?></h4>
                                <p class="mb-0">Total Usuarios</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white text-center">
                            <div class="card-body">
                                <i class="fas fa-user-shield fa-2x mb-3"></i>
                                <h4><?php echo count(array_filter($usuarios, function($u) { return $u['rol'] == 'admin'; })); ?></h4>
                                <p class="mb-0">Administradores</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white text-center">
                            <div class="card-body">
                                <i class="fas fa-user fa-2x mb-3"></i>
                                <h4><?php echo count(array_filter($usuarios, function($u) { return $u['rol'] == 'usuario'; })); ?></h4>
                                <p class="mb-0">Usuarios</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-dark text-center">
                            <div class="card-body">
                                <i class="fas fa-user-check fa-2x mb-3"></i>
                                <h4><?php echo count(array_filter($usuarios, function($u) { return $u['activo']; })); ?></h4>
                                <p class="mb-0">Activos</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lista de Usuarios -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list"></i> Lista de Usuarios
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($usuarios)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No hay usuarios registrados.</p>
                                <a href="crear-usuario.php" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Crear Primer Usuario
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Email</th>
                                            <th>Rol</th>
                                            <th>Estado</th>
                                            <th>Fecha Registro</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($usuarios as $usuario): ?>
                                            <tr>
                                                <td>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($usuario['nombre']); ?></strong>
                                                    </div>
                                                </td>
                                                <td>
                                                    <i class="fas fa-envelope text-primary"></i>
                                                    <?php echo htmlspecialchars($usuario['email']); ?>
                                                </td>
                                                <td>
                                                    <?php if ($usuario['rol'] == 'admin'): ?>
                                                        <span class="badge bg-danger">
                                                            <i class="fas fa-user-shield"></i> Administrador
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-info">
                                                            <i class="fas fa-user"></i> Usuario
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($usuario['activo']): ?>
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check"></i> Activo
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">
                                                            <i class="fas fa-times"></i> Inactivo
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <small>
                                                        <?php echo date('d/m/Y H:i', strtotime($usuario['fecha_creacion'])); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="editar-usuario.php?id=<?php echo $usuario['id']; ?>" 
                                                           class="btn btn-sm btn-outline-warning" title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <?php if ($usuario['id'] != $_SESSION['admin_id']): ?>
                                                            <button type="button" 
                                                                    class="btn btn-sm btn-outline-danger" 
                                                                    title="Eliminar usuario"
                                                                    onclick="confirmarEliminacion(<?php echo $usuario['id']; ?>, '<?php echo htmlspecialchars($usuario['nombre']); ?>')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmarEliminacion(usuarioId, nombre) {
            if (confirm('¿Estás seguro de que quieres eliminar al usuario "' + nombre + '"?\n\nEsta acción no se puede deshacer.')) {
                window.location.href = 'eliminar-usuario.php?id=' + usuarioId;
            }
        }
    </script>
</body>
</html> 