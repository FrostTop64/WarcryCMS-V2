<?php
if (!defined('init_pages')) { header('HTTP/1.0 404 not found'); exit; }
function wr_h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function wr_val($arr, $key, $default='') { return isset($arr[$key]) ? $arr[$key] : $default; }
function wr_dbval($arr, $key, $default='') { return isset($arr['Database'][$key]) ? $arr['Database'][$key] : $default; }

$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editing = ($editId > 0 && isset($realms_config[$editId])) ? $realms_config[$editId] : array();
$nextId = 1;
if (!empty($realms_config)) { $nextId = max(array_keys($realms_config)) + 1; }
$formId = $editId > 0 ? $editId : $nextId;

if (isset($_GET['saved'])) echo '<div class="notice success">Realm configuration saved successfully.</div>';
if (isset($_GET['deleted'])) echo '<div class="notice success">Realm removed successfully.</div>';
if (isset($_GET['error'])) echo '<div class="notice">Please check the fields. Realm ID, name, host/database and world port are required.</div>';
?>
<nav id="secondary" class="disable-tabbing"><ul><li class="current"><a href="index.php?page=realms">Realms Manager</a></li></ul></nav>
<section id="content">
  <div class="tab" id="maintab">
    <div class="admin-page-heading">
      <div>
        <h2>Realms Manager</h2>
        <p>Manage the realms shown by the website, including online status, uptime, type, rates and connection settings.</p>
      </div>
      <a class="button secondary" href="index.php?page=realms">Add New Realm</a>
    </div>

    <div class="admin-card">
      <h3>Configured Realms</h3>
      <table class="topvote-table">
        <thead><tr><th>ID</th><th>Name</th><th>Type</th><th>Rates</th><th>World Address</th><th>Characters DB</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($realms_config as $rid => $realm): ?>
          <tr>
            <td><span class="badge">#<?php echo (int)$rid; ?></span></td>
            <td><strong><?php echo wr_h(wr_val($realm, 'name', 'Unnamed')); ?></strong><br><span class="muted"><?php echo wr_h(wr_val($realm, 'expansion', '')); ?></span></td>
            <td><?php echo wr_h(wr_val($realm, 'realm_type', wr_val($realm, 'descr', 'Custom'))); ?></td>
            <td><?php echo wr_h(wr_val($realm, 'rates', '-')); ?></td>
            <td><?php echo wr_h(wr_val($realm, 'address', '127.0.0.1') . ':' . wr_val($realm, 'port', '8085')); ?></td>
            <td><?php echo wr_h(wr_dbval($realm, 'host', '127.0.0.1') . ' / ' . wr_dbval($realm, 'name', 'characters')); ?></td>
            <td><?php echo ((int)wr_val($realm, 'active', 1) === 1) ? '<span class="badge">Active</span>' : '<span class="muted">Hidden</span>'; ?></td>
            <td class="actions"><div class="button-group"><a class="button secondary" href="index.php?page=realms&edit=<?php echo (int)$rid; ?>">Edit</a><?php if (count($realms_config) > 1): ?><a class="button danger" onclick="return confirm('Delete this realm from the website configuration?');" href="execute.php?take=realms&action=delete&id=<?php echo (int)$rid; ?>">Delete</a><?php endif; ?></div></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="admin-card">
      <h3><?php echo $editId > 0 ? 'Edit Realm' : 'Add New Realm'; ?></h3>
      <form method="post" action="execute.php?take=realms&action=save" class="form pro-form">
        <section><label>Realm ID <small>Must match the auth.realmlist ID / uptime realmid.</small></label><div class="field-stack"><input type="number" name="id" min="1" value="<?php echo (int)$formId; ?>" required></div></section>
        <section><label>Realm Name <small>Public name displayed on the website.</small></label><div class="field-stack"><input type="text" name="name" value="<?php echo wr_h(wr_val($editing, 'name', '')); ?>" required></div></section>
        <section><label>Description / Type <small>Example: Blizzlike, Fun Realm, Instant 80, Custom PvE.</small></label><div class="field-inline"><input type="text" name="descr" value="<?php echo wr_h(wr_val($editing, 'descr', 'Blizzlike')); ?>"><select name="realm_type"><option>Blizzlike</option><option<?php echo wr_val($editing,'realm_type')==='Fun Realm'?' selected':''; ?>>Fun Realm</option><option<?php echo wr_val($editing,'realm_type')==='Custom'?' selected':''; ?>>Custom</option><option<?php echo wr_val($editing,'realm_type')==='PvP'?' selected':''; ?>>PvP</option><option<?php echo wr_val($editing,'realm_type')==='PvE'?' selected':''; ?>>PvE</option></select></div></section>
        <section><label>Expansion & Rates <small>Fully customizable website labels.</small></label><div class="field-inline"><input type="text" name="expansion" value="<?php echo wr_h(wr_val($editing, 'expansion', 'Wrath of the Lich King')); ?>"><input type="text" name="rates" value="<?php echo wr_h(wr_val($editing, 'rates', '1x')); ?>" style="max-width:180px"></div></section>
        <section><label>Website Note <small>Optional public note for this realm.</small></label><div class="field-stack"><textarea name="website_note" rows="4"><?php echo wr_h(wr_val($editing, 'website_note', '')); ?></textarea></div></section>
        <section><label>Worldserver Status Check <small>Used to detect if the realm is online.</small></label><div class="field-inline"><input type="text" name="address" value="<?php echo wr_h(wr_val($editing, 'address', '127.0.0.1')); ?>" required><input type="number" name="port" value="<?php echo wr_h(wr_val($editing, 'port', '8085')); ?>" required style="max-width:160px"></div></section>
        <section><label>Characters Database <small>Used for online players and realm details.</small></label><div class="field-inline"><input type="text" name="db_host" value="<?php echo wr_h(wr_dbval($editing, 'host', '127.0.0.1')); ?>" required><input type="text" name="db_name" value="<?php echo wr_h(wr_dbval($editing, 'name', 'characters')); ?>" required></div></section>
        <section><label>DB Login <small>Characters database user and password.</small></label><div class="field-inline"><input type="text" name="db_user" value="<?php echo wr_h(wr_dbval($editing, 'user', 'Ghost')); ?>" required><input type="text" name="db_pass" value="<?php echo wr_h(wr_dbval($editing, 'pass', 'ascent')); ?>"></div></section>
        <section><label>DB Encoding <small>Usually utf8.</small></label><div class="field-stack"><input type="text" name="db_encoding" value="<?php echo wr_h(wr_dbval($editing, 'encoding', 'utf8')); ?>" required style="max-width:180px"></div></section>
        <section><label>SOAP Console <small>Optional GM command access for realm actions.</small></label><div class="field-inline"><select name="soap_protocol"><option value="http">http</option><option value="https"<?php echo wr_val($editing,'soap_protocol')==='https'?' selected':''; ?>>https</option></select><input type="text" name="soap_address" value="<?php echo wr_h(wr_val($editing, 'soap_address', '127.0.0.1')); ?>"><input type="number" name="soap_port" value="<?php echo wr_h(wr_val($editing, 'soap_port', '7878')); ?>" style="max-width:150px"></div></section>
        <section><label>SOAP Login <small>Optional SOAP username/password.</small></label><div class="field-inline"><input type="text" name="soap_user" value="<?php echo wr_h(wr_val($editing, 'soap_user', '')); ?>"><input type="text" name="soap_pass" value="<?php echo wr_h(wr_val($editing, 'soap_pass', '')); ?>"></div></section>
        <section><label>Uptime Refresh Window <small>Used as fallback with auth.uptime. Example: 10 minutes.</small></label><div class="field-stack"><input type="text" name="update_time" value="<?php echo wr_h(wr_val($editing, 'UPDATE_TIME', '10 minutes')); ?>" style="max-width:220px"></div></section>
        <section><label>Visibility <small>Hidden realms stay configured but are not meant to be promoted publicly.</small></label><div class="field-inline"><label><input type="checkbox" name="active" value="1" <?php echo ((int)wr_val($editing, 'active', 1) === 1) ? 'checked' : ''; ?>> Active on website</label></div></section>
        <section><label></label><div class="form-actions"><button type="submit" class="button primary">Save Realm</button><a class="button secondary" href="index.php?page=realms">Cancel</a></div></section>
      </form>
    </div>
  </div>
</section>
