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
$sql = "SELECT * FROM usuarios WHERE id = ?";
$stmt = $db->query($sql, [$usuarioId]);
$usuario = $stmt->fetch();

if (!$usuario) {
    header('Location: usuarios.php');
    exit;
}

$success = '';
$error = '';

if ($_POST) {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $rol = $_POST['rol'] ?? 'usuario';
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    // Validaciones
    if (empty($nombre) || empty($email)) {
        $error = 'Los campos nombre y email son obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El formato del email no es válido.';
    } elseif (!empty($password) && strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif (!empty($password) && $password !== $confirm_password) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        // Verificar si el email ya existe (excluyendo el usuario actual)
        $sql = "SELECT id FROM usuarios WHERE email = ? AND id != ?";
        $stmt = $db->query($sql, [$email, $usuarioId]);
        if ($stmt->fetch()) {
            $error = 'El email ya está registrado por otro usuario.';
        } else {
            // Actualizar el usuario
            if (!empty($password)) {
                // Actualizar con nueva contraseña
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE usuarios SET nombre = ?, email = ?, password = ?, rol = ?, activo = ? WHERE id = ?";
                $stmt = $db->query($sql, [$nombre, $email, $password_hash, $rol, $activo, $usuarioId]);
            } else {
                // Actualizar sin cambiar contraseña
                $sql = "UPDATE usuarios SET nombre = ?, email = ?, rol = ?, activo = ? WHERE id = ?";
                $stmt = $db->query($sql, [$nombre, $email, $rol, $activo, $usuarioId]);
            }
            
            if ($stmt->rowCount() > 0) {
                header('Location: usuarios.php?editado=1');
                exit;
            } else {
                $error = 'No se realizaron cambios o error al actualizar.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario - NPS System</title>
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
                    <h2><i class="fas fa-user-edit"></i> Editar Usuario</h2>
                    <a href="usuarios.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h4 class="mb-0">
                            <i class="fas fa-user-edit"></i> Editar: <?php echo htmlspecialchars($usuario['nombre']); ?>
                        </h4>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nombre" class="form-label">Nombre Completo *</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" 
                                           value="<?php echo htmlspecialchars($_POST['nombre'] ?? $usuario['nombre']); ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($_POST['email'] ?? $usuario['email']); ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Nueva Contraseña</label>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           minlength="6">
                                    <small class="text-muted">Dejar en blanco para mantener la contraseña actual</small>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">Confirmar Nueva Contraseña</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                           minlength="6">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="rol" class="form-label">Rol *</label>
                                    <select class="form-select" id="rol" name="rol" required>
                                        <option value="usuario" <?php echo ($_POST['rol'] ?? $usuario['rol']) == 'usuario' ? 'selected' : ''; ?>>Usuario</option>
                                        <option value="admin" <?php echo ($_POST['rol'] ?? $usuario['rol']) == 'admin' ? 'selected' : ''; ?>>Administrador</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Estado</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="activo" name="activo" 
                                               <?php echo ($_POST['activo'] ?? $usuario['activo']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="activo">
                                            Usuario Activo
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="usuarios.php" class="btn btn-secondary">
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
    <script>
        // Validación de contraseñas en tiempo real
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password && confirmPassword && password !== confirmPassword) {
                this.setCustomValidity('Las contraseñas no coinciden');
            } else {
                this.setCustomValidity('');
            }
        });
        
        document.getElementById('password').addEventListener('input', function() {
            const confirmPassword = document.getElementById('confirm_password');
            if (confirmPassword.value) {
                if (this.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('Las contraseñas no coinciden');
                } else {
                    confirmPassword.setCustomValidity('');
                }
            }
        });
    </script>
</body>
</html> 