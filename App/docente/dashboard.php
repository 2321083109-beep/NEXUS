<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('docente');
$pdo = db();
$st = $pdo->prepare('SELECT m.id, m.clave, m.nombre,
    (SELECT COUNT(*) FROM inscripciones i WHERE i.materia_id = m.id) AS alumnos
    FROM materias m JOIN docentes d ON d.id = m.docente_id
    WHERE d.usuario_id = ? ORDER BY m.nombre');
$st->execute([$_SESSION['user']['id']]);
$materias = $st->fetchAll();

$pageTitle = 'Panel del docente';
require __DIR__ . '/../includes/header.php';
?>
<div class="card">
  <h3>Materias asignadas</h3>
  <table>
    <tr><th>Clave</th><th>Materia</th><th>Alumnos</th><th></th></tr>
    <?php foreach ($materias as $m): ?>
      <tr>
        <td><?= e($m['clave']) ?></td><td><?= e($m['nombre']) ?></td><td><?= (int)$m['alumnos'] ?></td>
        <td><a class="btn sm" href="calificaciones.php?materia=<?= (int)$m['id'] ?>">Capturar calificaciones</a></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$materias): ?><tr><td colspan="4">Aún no tienes materias asignadas.</td></tr><?php endif; ?>
  </table>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
