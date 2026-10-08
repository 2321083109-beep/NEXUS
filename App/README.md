# Control Escolar (PHP + MySQL) — XAMPP 8.0.30

XAMPP 8.0.30 incluye PHP 8.0.30 y MariaDB 10.4. El código es compatible con esas versiones.

## Instalación
1. Copia la carpeta `control_escolar` a `C:\xampp\htdocs\`.
2. Abre el Panel de XAMPP e inicia **Apache** y **MySQL**.
3. Entra a http://localhost/phpmyadmin → pestaña **Importar** → elige `database.sql` → Continuar.
4. Abre http://localhost/control_escolar

## Acceso inicial
- Administrador: `admin@escuela.com` / `password` (cámbiala cuando entres).

## Flujo de uso
1. Admin: registra **Docentes**, **Estudiantes** y **Materias** (asignando docente).
2. Admin: en **Inscripciones** inscribe estudiantes en materias.
3. Docente: entra, ve sus materias y captura calificaciones (3 parciales).
4. Estudiante: entra y consulta sus calificaciones y promedio (aprobatorio ≥ 6).

## Configuración
Si cambiaste usuario/contraseña de MySQL o el nombre de la carpeta, edita `includes/bootstrap.php`
(`DB_USER`, `DB_PASS`, `BASE_URL`).
