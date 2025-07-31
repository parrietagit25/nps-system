-- Script para corregir el esquema de la base de datos
USE nps_system;

-- Agregar columna fecha_creacion a la tabla destinatarios
ALTER TABLE destinatarios ADD COLUMN fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Verificar que todas las tablas existan
SHOW TABLES;

-- Verificar estructura de la tabla destinatarios
DESCRIBE destinatarios; 