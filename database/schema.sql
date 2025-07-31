-- Esquema de base de datos para el sistema NPS
-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS nps_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nps_system;

-- Tabla de usuarios administradores
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'usuario') DEFAULT 'usuario',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE
);

-- Tabla de encuestas NPS
CREATE TABLE encuestas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    pregunta VARCHAR(500) NOT NULL,
    fecha_inicio DATE,
    fecha_fin DATE,
    estado ENUM('activa', 'inactiva', 'borrador') DEFAULT 'borrador',
    creado_por INT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (creado_por) REFERENCES usuarios(id)
);

-- Tabla de destinatarios de encuestas
CREATE TABLE destinatarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    encuesta_id INT NOT NULL,
    nombre VARCHAR(100),
    email VARCHAR(255) NOT NULL,
    token_unico VARCHAR(255) UNIQUE NOT NULL,
    enviado BOOLEAN DEFAULT FALSE,
    respondido BOOLEAN DEFAULT FALSE,
    fecha_envio TIMESTAMP NULL,
    fecha_respuesta TIMESTAMP NULL,
    FOREIGN KEY (encuesta_id) REFERENCES encuestas(id) ON DELETE CASCADE
);

-- Tabla de respuestas NPS
CREATE TABLE respuestas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    encuesta_id INT NOT NULL,
    destinatario_id INT NOT NULL,
    puntuacion INT NOT NULL CHECK (puntuacion >= 0 AND puntuacion <= 10),
    comentario TEXT,
    categoria ENUM('detractor', 'pasivo', 'promotor') GENERATED ALWAYS AS (
        CASE 
            WHEN puntuacion <= 6 THEN 'detractor'
            WHEN puntuacion <= 8 THEN 'pasivo'
            ELSE 'promotor'
        END
    ) STORED,
    fecha_respuesta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    FOREIGN KEY (encuesta_id) REFERENCES encuestas(id) ON DELETE CASCADE,
    FOREIGN KEY (destinatario_id) REFERENCES destinatarios(id) ON DELETE CASCADE
);

-- Tabla de logs de envío de emails
CREATE TABLE logs_email (
    id INT AUTO_INCREMENT PRIMARY KEY,
    destinatario_id INT NOT NULL,
    sendgrid_message_id VARCHAR(255),
    estado ENUM('enviado', 'entregado', 'rebotado', 'error') DEFAULT 'enviado',
    fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    detalles TEXT,
    FOREIGN KEY (destinatario_id) REFERENCES destinatarios(id) ON DELETE CASCADE
);

-- Insertar usuario administrador por defecto
INSERT INTO usuarios (nombre, email, password, rol) VALUES 
('Administrador', 'admin@nps.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Índices para mejorar el rendimiento
CREATE INDEX idx_encuestas_estado ON encuestas(estado);
CREATE INDEX idx_destinatarios_encuesta ON destinatarios(encuesta_id);
CREATE INDEX idx_destinatarios_token ON destinatarios(token_unico);
CREATE INDEX idx_respuestas_encuesta ON respuestas(encuesta_id);
CREATE INDEX idx_respuestas_categoria ON respuestas(categoria); 