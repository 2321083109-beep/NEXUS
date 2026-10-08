<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('docente');
$pdo = db();
$materiaId = (int)($_GET['materia'] ?? $_POST['materia_id'] ?? 0);

// La materia debe pertenecer al docente en sesión
$st = $pdo->prepare('SELECT m.* FROM materias m JOIN docentes d ON d.id = m.docente_id WHERE m.id = ? AND d.usuario_id = ?');
$st->execute([$materiaId, $_SESSION['user']['id']]);
$materia = $st->fetch();
if (!$materia) {
    flash('err', 'Materia no encontrada.');
    redirect('/docente/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $upd = $pdo->prepare('UPDATE inscripciones SET parcial1 = ?, parcial2 = ?, parcial3 = ? WHERE id = ? AND materia_id = ?');
    foreach (($_POST['cal'] ?? []) as $id => $p) {
        $upd->execute([nota($p['p1'] ?? ''), nota($p['p2'] ?? ''), nota($p['p3'] ?? ''), (int)$id, $materiaId]);
    }
    flash('ok', 'Calificaciones guardadas.');
    redirect('/docente/calificaciones.php?materia=' . $materiaId);
}

$st = $pdo->prepare('SELECT i.id, i.parcial1, i.parcial2, i.parcial3, e.matricula, u.nombre
    FROM inscripciones i
    JOIN estudiantes e ON e.id = i.estudiante_id
    JOIN usuarios u ON u.id = e.usuario_id
    WHERE i.materia_id = ? ORDER BY u.nombre');
$st->execute([$materiaId]);
$alumnos = $st->fetchAll();

$pageTitle = $materia['clave'] . ' - ' . $materia['nombre'];
require __DIR__ . '/../includes/header.php';
?>
<div class="card">
  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="materia_id" value="<?= $materiaId ?>">
    <table>
      <tr><th>Matrícula</th><th>Alumno</th><th>Parcial 1</th><th>Parcial 2</th><th>Parcial 3</th></tr>
      <?php foreach ($alumnos as $a): ?>
        <tr>
          <td><?= e($a['matricula']) ?></td><td><?= e($a['nombre']) ?></td>
          <?php foreach (['parcial1' => 'p1', 'parcial2' => 'p2', 'parcial3' => 'p3'] as $col => $key): ?>
            <td><input class="nota" type="number" step="0.01" min="0" max="10"
                name="cal[<?= (int)$a['id'] ?>][<?= $key ?>]" value="<?= e($a[$col]) ?>"></td>
          <?php endforeach; ?>
        </tr>
      <?php endforeach; ?>
      <?php if (!$alumnos): ?><tr><td colspan="5">No hay alumnos inscritos.</td></tr><?php endif; ?>
    </table>
    <?php if ($alumnos): ?><p><button class="btn">Guardar calificaciones</button> <a class="btn gris" href="dashboard.php">Volver</a></p><?php endif; ?>
  </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
