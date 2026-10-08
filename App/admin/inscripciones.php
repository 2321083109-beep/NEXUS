<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('admin');
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'delete') {
            $pdo->prepare('DELETE FROM inscripciones WHERE id = ?')->execute([(int)$_POST['id']]);
            flash('ok', 'Inscripción eliminada.');
        } elseif ($action === 'create') {
            $est = (int)($_POST['estudiante_id'] ?? 0);
            $mat = (int)($_POST['materia_id'] ?? 0);
            if (!$est || !$mat) {
                throw new RuntimeException('Selecciona estudiante y materia.');
            }
            $pdo->prepare('INSERT INTO inscripciones (estudiante_id, materia_id) VALUES (?,?)')->execute([$est, $mat]);
            flash('ok', 'Estudiante inscrito.');
        }
    } catch (PDOException $ex) {
        flash('err', $ex->getCode() === '23000' ? 'Ese estudiante ya está inscrito en la materia.' : 'Error de base de datos.');
    } catch (RuntimeException $ex) {
        flash('err', $ex->getMessage());
    }
    redirect('/admin/inscripciones.php');
}

$estudiantes = $pdo->query('SELECT e.id, e.matricula, u.nombre FROM estudiantes e JOIN usuarios u ON u.id = e.usuario_id ORDER BY u.nombre')->fetchAll();
$materias = $pdo->query('SELECT id, clave, nombre FROM materias ORDER BY nombre')->fetchAll();
$lista = $pdo->query('SELECT i.id, u.nombre AS estudiante, e.matricula, m.clave, m.nombre AS materia
    FROM inscripciones i
    JOIN estudiantes e ON e.id = i.estudiante_id
    JOIN usuarios u ON u.id = e.usuario_id
    JOIN materias m ON m.id = i.materia_id
    ORDER BY m.nombre, u.nombre')->fetchAll();

$pageTitle = 'Inscripciones';
require __DIR__ . '/../includes/header.php';
?>
<div class="card">
  <h3>Inscribir estudiante en materia</h3>
  <form method="post" class="grid">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="create">
    <div>
      <label>Estudiante</label>
      <select name="estudiante_id" required>
        <option value="">— Selecciona —</option>
        <?php foreach ($estudiantes as $s): ?>
          <option value="<?= (int)$s['id'] ?>"><?= e($s['matricula'] . ' - ' . $s['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label>Materia</label>
      <select name="materia_id" required>
        <option value="">— Selecciona —</option>
        <?php foreach ($materias as $m): ?>
          <option value="<?= (int)$m['id'] ?>"><?= e($m['clave'] . ' - ' . $m['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div><button class="btn">Inscribir</button></div>
  </form>
</div>
<div class="card">
  <table>
    <tr><th>Materia</th><th>Matrícula</th><th>Estudiante</th><th></th></tr>
    <?php foreach ($lista as $r): ?>
      <tr>
        <td><?= e($r['clave'] . ' - ' . $r['materia']) ?></td><td><?= e($r['matricula']) ?></td><td><?= e($r['estudiante']) ?></td>
        <td>
          <form method="post" class="inline" onsubmit="return confirm('¿Quitar esta inscripción?')">
            <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button class="btn rojo sm">Quitar</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$lista): ?><tr><td colspan="4">Sin inscripciones.</td></tr><?php endif; ?>
  </table>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
