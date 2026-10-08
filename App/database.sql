-- Control Escolar - XAMPP 8.0.30 (PHP 8.0.30 / MariaDB 10.4)
CREATE DATABASE IF NOT EXISTS control_escolar CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE control_escolar;

CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  rol ENUM('admin','docente','estudiante') NOT NULL,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE estudiantes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL UNIQUE,
  matricula VARCHAR(30) NOT NULL UNIQUE,
  grado VARCHAR(20) DEFAULT NULL,
  grupo VARCHAR(10) DEFAULT NULL,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE docentes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL UNIQUE,
  especialidad VARCHAR(100) DEFAULT NULL,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE materias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  clave VARCHAR(20) NOT NULL UNIQUE,
  nombre VARCHAR(120) NOT NULL,
  docente_id INT DEFAULT NULL,
  FOREIGN KEY (docente_id) REFERENCES docentes(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE inscripciones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  estudiante_id INT NOT NULL,
  materia_id INT NOT NULL,
  parcial1 DECIMAL(4,2) DEFAULT NULL,
  parcial2 DECIMAL(4,2) DEFAULT NULL,
  parcial3 DECIMAL(4,2) DEFAULT NULL,
  UNIQUE KEY uq_insc (estudiante_id, materia_id),
  FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE,
  FOREIGN KEY (materia_id) REFERENCES materias(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Administrador inicial: admin@escuela.com / password  (cámbiala después de entrar)
INSERT INTO usuarios (nombre, email, password, rol) VALUES
('Administrador', 'admin@escuela.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
