<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('admin');
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'delete') {
            $pdo->prepare('DELETE FROM materias WHERE id = ?')->execute([(int)$_POST['id']]);
            flash('ok', 'Materia eliminada.');
        } else {
            $clave = trim($_POST['clave'] ?? '');
            $nombre = trim($_POST['nombre'] ?? '');
            $docente = (int)($_POST['docente_id'] ?? 0) ?: null;
            if ($clave === '' || $nombre === '') {
                throw new RuntimeException('Clave y nombre son obligatorios.');
            }
            if ($action === 'create') {
                $pdo->prepare('INSERT INTO materias (clave, nombre, docente_id) VALUES (?,?,?)')->execute([$clave, $nombre, $docente]);
                flash('ok', 'Materia creada.');
            } elseif ($action === 'update') {
                $pdo->prepare('UPDATE materias SET clave = ?, nombre = ?, docente_id = ? WHERE id = ?')
                    ->execute([$clave, $nombre, $docente, (int)$_POST['id']]);
                flash('ok', 'Materia actualizada.');
            }
        }
    } catch (PDOException $ex) {
        flash('err', $ex->getCode() === '23000' ? 'La clave ya existe.' : 'Error de base de datos.');
    } catch (RuntimeException $ex) {
        flash('err', $ex->getMessage());
    }
    redirect('/admin/materias.php');
}

$edit = null;
if (isset($_GET['edit'])) {
    $st = $pdo->prepare('SELECT * FROM materias WHERE id = ?');
    $st->execute([(int)$_GET['edit']]);
    $edit = $st->fetch() ?: null;
}
$docentes = $pdo->query('SELECT d.id, u.nombre FROM docentes d JOIN usuarios u ON u.id = d.usuario_id ORDER BY u.nombre')->fetchAll();
$lista = $pdo->query('SELECT m.*, u.nombre AS docente FROM materias m LEFT JOIN docentes d ON d.id = m.docente_id LEFT JOIN usuarios u ON u.id = d.usuario_id ORDER BY m.nombre')->fetchAll();

$pageTitle = 'Materias';
require __DIR__ . '/../includes/header.php';
?>
<div class="card">
  <h3><?= $edit ? 'Editar materia' : 'Nueva materia' ?></h3>
  <form method="post" class="grid">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="<?= $edit ? 'update' : 'create' ?>">
    <?php if ($edit): ?><input type="hidden" name="id" value="<?= (int)$edit['id'] ?>"><?php endif; ?>
    <div><label>Clave</label><input name="clave" required value="<?= e($edit['clave'] ?? '') ?>"></div>
    <div><label>Nombre</label><input name="nombre" required value="<?= e($edit['nombre'] ?? '') ?>"></div>
    <div>
      <label>Docente</label>
      <select name="docente_id">
        <option value="">— Sin asignar —</option>
        <?php foreach ($docentes as $d): ?>
          <option value="<?= (int)$d['id'] ?>" <?= ($edit['docente_id'] ?? null) == $d['id'] ? 'selected' : '' ?>><?= e($d['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <button class="btn"><?= $edit ? 'Guardar cambios' : 'Crear' ?></button>
      <?php if ($edit): ?><a class="btn gris" href="materias.php">Cancelar</a><?php endif; ?>
    </div>
  </form>
</div>
<div class="card">
  <table>
    <tr><th>Clave</th><th>Materia</th><th>Docente</th><th>Acciones</th></tr>
    <?php foreach ($lista as $r): ?>
      <tr>
        <td><?= e($r['clave']) ?></td><td><?= e($r['nombre']) ?></td><td><?= e($r['docente'] ?? 'Sin asignar') ?></td>
        <td>
          <a class="btn sm" href="?edit=<?= (int)$r['id'] ?>">Editar</a>
          <form method="post" class="inline" onsubmit="return confirm('¿Eliminar esta materia?')">
            <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button class="btn rojo sm">Eliminar</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$lista): ?><tr><td colspan="4">Sin registros.</td></tr><?php endif; ?>
  </table>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
