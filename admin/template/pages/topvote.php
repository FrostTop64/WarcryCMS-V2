<?php
if (!defined('init_pages')) { header('HTTP/1.0 404 not found'); exit; }
function tvh($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$DB->query("CREATE TABLE IF NOT EXISTS `vote_sites` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(120) NOT NULL,
    `url` text NOT NULL,
    `img` text NOT NULL,
    `reward_silver` int(10) NOT NULL DEFAULT 2,
    `cooldown` varchar(32) NOT NULL DEFAULT '',
    `position` int(10) NOT NULL DEFAULT 0,
    `active` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8");

try {
    $count = (int)$DB->query("SELECT COUNT(*) FROM `vote_sites`")->fetchColumn();
    if ($count === 0) {
        $VoteSites = new VoteSitesData();
        unset($VoteSites);
    }
} catch (Exception $e) {}

$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$edit = array('id'=>0,'name'=>'','url'=>'','img'=>'','reward_silver'=>2,'cooldown'=>'','position'=>0,'active'=>1);
if ($editId > 0) {
    $st = $DB->prepare("SELECT * FROM `vote_sites` WHERE `id` = :id LIMIT 1");
    $st->execute(array(':id'=>$editId));
    $found = $st->fetch();
    if ($found) { $edit = $found; }
}

$sites = $DB->query("SELECT vs.*, COUNT(vd.id) AS vote_count
    FROM `vote_sites` vs
    LEFT JOIN `vote_data` vd ON vd.`siteid` = vs.`id`
    GROUP BY vs.`id`
    ORDER BY vs.`position` ASC, vs.`id` ASC")->fetchAll();
?>
<nav id="secondary" class="disable-tabbing"><ul><li class="current"><a href="index.php?page=topvote">Top Vote</a></li></ul></nav>
<section id="content"><div class="tab" id="maintab">
  <div class="admin-page-heading">
    <div>
      <h2>Top Vote Manager</h2>
      <p>Manage every public voting website, voting link, banner image, reward, order and status directly from the admin panel.</p>
    </div>
    <a class="button" href="../index.php?page=vote" target="_blank">View Public Vote Page</a>
  </div>

  <?php if (isset($_GET['saved'])): ?><div class="notice success">Vote site saved successfully.</div><?php endif; ?>
  <?php if (isset($_GET['deleted'])): ?><div class="notice success">Vote site deleted successfully.</div><?php endif; ?>
  <?php if (isset($_GET['error'])): ?><div class="notice">Please verify all required fields.</div><?php endif; ?>

  <div class="admin-grid two-col topvote-layout">
    <div class="admin-card">
      <h3><?php echo ((int)$edit['id'] > 0) ? 'Edit Vote Site' : 'Add Vote Site'; ?></h3>
      <form method="post" action="execute.php?take=topvote&amp;action=save" class="form pro-form">
        <input type="hidden" name="id" value="<?php echo (int)$edit['id']; ?>">
        <section>
          <label>Site Name <small>Example: XtremeTop100, TOPG, Top100Arena.</small></label>
          <div class="field-stack"><input type="text" name="name" value="<?php echo tvh($edit['name']); ?>" placeholder="Vote website name" required></div>
        </section>
        <section>
          <label>Vote Link <small>The external link opened when the player votes.</small></label>
          <div class="field-stack"><input type="url" name="url" value="<?php echo tvh($edit['url']); ?>" placeholder="https://..." required></div>
        </section>
        <section>
          <label>Banner Image URL <small>Small vote banner/image displayed on the vote page.</small></label>
          <div class="field-stack"><input type="url" name="img" value="<?php echo tvh($edit['img']); ?>" placeholder="https://.../vote.png" required></div>
        </section>
        <section>
          <label>Reward Silver <small>Leave 2 to use the classic Warcry vote reward.</small></label>
          <div class="field-stack"><input type="number" name="reward_silver" min="0" max="999999" value="<?php echo (int)$edit['reward_silver']; ?>"></div>
        </section>
        <section>
          <label>Cooldown <small>Optional. Example: 24 or 24 hours. A plain number is treated as hours. Empty uses config.php VOTE cooldown.</small></label>
          <div class="field-stack"><input type="text" name="cooldown" value="<?php echo tvh($edit['cooldown']); ?>" placeholder="24 hours"></div>
        </section>
        <section>
          <label>Position <small>Lower number appears first.</small></label>
          <div class="field-stack"><input type="number" name="position" value="<?php echo (int)$edit['position']; ?>"></div>
        </section>
        <section>
          <label>Status <small>Disabled sites stay saved but are hidden publicly.</small></label>
          <div class="field-stack"><select name="active"><option value="1" <?php echo ((int)$edit['active'] === 1 ? 'selected' : ''); ?>>Active</option><option value="0" <?php echo ((int)$edit['active'] === 0 ? 'selected' : ''); ?>>Disabled</option></select></div>
        </section>
        <section>
          <label></label>
          <div class="form-actions"><button type="submit" class="button primary">Save Vote Site</button><?php if ((int)$edit['id'] > 0): ?><a class="button" href="index.php?page=topvote">Cancel Edit</a><?php endif; ?></div>
        </section>
      </form>
    </div>

    <div class="admin-card topvote-preview-card">
      <h3>Live Preview</h3>
      <div class="vote-preview-tile">
        <div class="vote-preview-image" style="background-image:url('<?php echo tvh($edit['img'] ? $edit['img'] : 'template/img/logo.png'); ?>')"></div>
        <strong><?php echo tvh($edit['name'] ? $edit['name'] : 'New Vote Site'); ?></strong>
        <span><?php echo ((int)$edit['active'] === 1 ? 'Active' : 'Disabled'); ?> • <?php echo (int)$edit['reward_silver']; ?> Silver</span>
      </div>
      <p class="muted">Tip: use a clean rectangular banner from the vote website for the best result.</p>
    </div>
  </div>

  <div class="admin-card">
    <h3>Configured Vote Sites</h3>
    <table class="datatable topvote-table">
      <thead><tr><th>Order</th><th>Preview</th><th>Name</th><th>Reward</th><th>Cooldown</th><th>Total Votes</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($sites as $s): ?>
        <tr>
          <td><?php echo (int)$s['position']; ?></td>
          <td><div class="vote-thumb" style="background-image:url('<?php echo tvh($s['img']); ?>')"></div></td>
          <td><strong><?php echo tvh($s['name']); ?></strong><br><a href="<?php echo tvh($s['url']); ?>" target="_blank" rel="noopener">Open vote link</a></td>
          <td><span class="pill gold"><?php echo (int)$s['reward_silver']; ?> Silver</span></td>
          <td><?php echo $s['cooldown'] ? tvh($s['cooldown']) : '<span class="pill">Config default</span>'; ?></td>
          <td><?php echo (int)$s['vote_count']; ?></td>
          <td><span class="pill <?php echo ((int)$s['active'] === 1 ? 'green' : 'red'); ?>"><?php echo ((int)$s['active'] === 1 ? 'Active' : 'Disabled'); ?></span></td>
          <td class="actions"><a class="button" href="index.php?page=topvote&amp;edit=<?php echo (int)$s['id']; ?>">Edit</a> <a class="button danger" href="execute.php?take=topvote&amp;action=delete&amp;id=<?php echo (int)$s['id']; ?>" onclick="return confirm('Delete this vote site? Existing vote logs stay saved.');">Delete</a></td>
        </tr>
      <?php endforeach; if (!$sites): ?><tr><td colspan="8">No vote sites configured yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div></section>
