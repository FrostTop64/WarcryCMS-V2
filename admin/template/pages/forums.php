<?PHP
if (!defined('init_pages'))
{
    header('HTTP/1.0 404 not found');
    exit;
}

if (!$CURUSER->getPermissions()->isAllowed(PERMISSION_FORUMS))
{
    $CORE->ErrorBox('You do not have the required permissions.');
}

function wc_admin_h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function wc_admin_cut($v, $len = 90) {
    $v = trim(strip_tags((string)$v));
    if (strlen($v) > $len) { $v = substr($v, 0, $len - 3) . '...'; }
    return $v;
}
function wc_forum_count($table) {
    global $DB;
    try { return (int)$DB->query("SELECT COUNT(*) FROM `".$table."`")->fetchColumn(); }
    catch (Exception $e) { return 0; }
}

$section = isset($_GET['section']) ? $_GET['section'] : 'overview';
$allowedSections = array('overview', 'forums', 'topics', 'posts');
if (!in_array($section, $allowedSections)) { $section = 'overview'; }

$editForum = false;
$editTopic = false;
$editPost = false;

if ($section == 'forums' && isset($_GET['edit_forum'])) {
    $id = (int)$_GET['edit_forum'];
    $stmt = $DB->prepare("SELECT * FROM `wcf_forums` WHERE `id` = :id LIMIT 1");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    if ($stmt->rowCount()) { $editForum = $stmt->fetch(); }
}
if ($section == 'topics' && isset($_GET['edit_topic'])) {
    $id = (int)$_GET['edit_topic'];
    $stmt = $DB->prepare("SELECT * FROM `wcf_topics` WHERE `id` = :id LIMIT 1");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    if ($stmt->rowCount()) { $editTopic = $stmt->fetch(); }
}
if ($section == 'posts' && isset($_GET['edit_post'])) {
    $id = (int)$_GET['edit_post'];
    $stmt = $DB->prepare("SELECT * FROM `wcf_posts` WHERE `id` = :id LIMIT 1");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    if ($stmt->rowCount()) { $editPost = $stmt->fetch(); }
}

$categories = array();
$res = $DB->query("SELECT `id`, `name` FROM `wcf_categories` ORDER BY `position` ASC, `id` ASC");
while ($row = $res->fetch()) { $categories[] = $row; }

$forumsList = array();
$res = $DB->query("SELECT `id`, `name`, `category` FROM `wcf_forums` ORDER BY `category` ASC, `position` ASC, `id` ASC");
while ($row = $res->fetch()) { $forumsList[] = $row; }

if ($error = $ERRORS->DoPrint(array('forum_manage'))) { echo $error; unset($error); }
if ($success = $ERRORS->successPrint(array('forum_manage'))) { echo $success; unset($success); }
?>

<nav id="secondary" class="disable-tabbing warcry-admin-tabs">
    <ul>
        <li class="<?php echo ($section == 'overview' ? 'current' : ''); ?>"><a href="index.php?page=forums">Overview</a></li>
        <li class="<?php echo ($section == 'forums' ? 'current' : ''); ?>"><a href="index.php?page=forums&section=forums">Forums</a></li>
        <li class="<?php echo ($section == 'topics' ? 'current' : ''); ?>"><a href="index.php?page=forums&section=topics">Topics</a></li>
        <li class="<?php echo ($section == 'posts' ? 'current' : ''); ?>"><a href="index.php?page=forums&section=posts">Posts</a></li>
        <li><a href="index.php?page=forum-cats">Categories</a></li>
    </ul>
</nav>

<section id="content" class="warcry-forum-admin">
<div class="tab" id="maintab">

<?php if ($section == 'overview'): ?>
    <div class="wc-admin-hero-card">
        <div>
            <span class="wc-kicker">Forum Control Center</span>
            <h2>Complete Forum Management</h2>
            <p>Manage categories, forums, topics and every post directly from the admin panel.</p>
        </div>
        <a class="button primary big" href="index.php?page=forums&section=forums">Create / Edit Forums</a>
    </div>

    <div class="wc-stats-grid">
        <div class="wc-stat"><b><?php echo wc_forum_count('wcf_categories'); ?></b><span>Categories</span></div>
        <div class="wc-stat"><b><?php echo wc_forum_count('wcf_forums'); ?></b><span>Forums</span></div>
        <div class="wc-stat"><b><?php echo wc_forum_count('wcf_topics'); ?></b><span>Topics</span></div>
        <div class="wc-stat"><b><?php echo wc_forum_count('wcf_posts'); ?></b><span>Posts</span></div>
    </div>

    <div class="wc-admin-card">
        <h2>Recent Topics</h2>
        <div class="wc-table-wrap">
            <table class="wc-table">
                <thead><tr><th>ID</th><th>Topic</th><th>Forum</th><th>Author</th><th>Posts</th><th>Last Post</th><th>Actions</th></tr></thead>
                <tbody>
                <?php
                $stmt = $DB->query("SELECT t.*, f.name AS forum_name, a.displayName AS author_name FROM `wcf_topics` t LEFT JOIN `wcf_forums` f ON f.id = t.forum LEFT JOIN `account_data` a ON a.id = t.author ORDER BY t.added DESC LIMIT 20");
                if ($stmt->rowCount()): while ($row = $stmt->fetch()): ?>
                    <tr>
                        <td>#<?php echo (int)$row['id']; ?></td>
                        <td><?php echo wc_admin_h($row['name']); ?></td>
                        <td><?php echo wc_admin_h($row['forum_name']); ?></td>
                        <td><?php echo wc_admin_h($row['author_name'] ? $row['author_name'] : 'Account '.$row['author']); ?></td>
                        <td><?php echo (int)$row['posts']; ?></td>
                        <td><?php echo wc_admin_h($row['lastpost_time']); ?></td>
                        <td><a class="button" href="index.php?page=forums&section=topics&edit_topic=<?php echo (int)$row['id']; ?>">Edit</a></td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="7" class="wc-empty">No topics yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php elseif ($section == 'forums'): ?>
    <div class="wc-grid-2">
        <div class="wc-admin-card">
            <h2><?php echo $editForum ? 'Edit Forum' : 'Create New Forum'; ?></h2>
            <form class="wc-clean-form" action="execute.php?take=forum_manage&action=<?php echo $editForum ? 'save_forum' : 'add_forum'; ?>" method="post">
                <?php if ($editForum): ?><input type="hidden" name="id" value="<?php echo (int)$editForum['id']; ?>"><?php endif; ?>
                <label>Forum Name</label>
                <input type="text" name="name" required maxlength="250" value="<?php echo $editForum ? wc_admin_h($editForum['name']) : ''; ?>" placeholder="Example: General Discussion">
                <label>Description</label>
                <textarea name="description" rows="4" maxlength="500" placeholder="Short description displayed on the forum page."><?php echo $editForum ? wc_admin_h($editForum['description']) : ''; ?></textarea>
                <div class="wc-form-row">
                    <div><label>Category</label><select name="category" required><?php foreach ($categories as $cat): ?><option value="<?php echo (int)$cat['id']; ?>" <?php echo ($editForum && (int)$editForum['category'] == (int)$cat['id']) ? 'selected' : ''; ?>><?php echo wc_admin_h($cat['name']); ?></option><?php endforeach; ?></select></div>
                    <div><label>Position</label><input type="number" name="position" value="<?php echo $editForum ? (int)$editForum['position'] : 0; ?>"></div>
                </div>
                <label>Class Style</label>
                <select name="class"><option value="0" <?php echo ($editForum && (int)$editForum['class'] == 0) ? 'selected' : ''; ?>>Default</option><option value="1" <?php echo ($editForum && (int)$editForum['class'] == 1) ? 'selected' : ''; ?>>Class / Realm Style</option></select>
                <div class="wc-form-actions"><input type="submit" class="button primary big" value="<?php echo $editForum ? 'Save Forum' : 'Create Forum'; ?>"><?php if ($editForum): ?><a class="button" href="index.php?page=forums&section=forums">Cancel</a><?php endif; ?></div>
            </form>
        </div>

        <div class="wc-admin-card">
            <h2>Forums List</h2>
            <div class="wc-table-wrap">
                <table class="wc-table">
                    <thead><tr><th>ID</th><th>Forum</th><th>Category</th><th>Topics</th><th>Posts</th><th>Actions</th></tr></thead><tbody>
                    <?php
                    $stmt = $DB->query("SELECT f.*, c.name AS cat_name FROM `wcf_forums` f LEFT JOIN `wcf_categories` c ON c.id = f.category ORDER BY c.position ASC, f.position ASC, f.id ASC");
                    if ($stmt->rowCount()): while ($row = $stmt->fetch()): ?>
                        <tr><td>#<?php echo (int)$row['id']; ?></td><td><b><?php echo wc_admin_h($row['name']); ?></b><small><?php echo wc_admin_h(wc_admin_cut($row['description'], 70)); ?></small></td><td><?php echo wc_admin_h($row['cat_name']); ?></td><td><?php echo (int)$row['topics']; ?></td><td><?php echo (int)$row['posts']; ?></td><td><span class="button-group"><a class="button" href="index.php?page=forums&section=forums&edit_forum=<?php echo (int)$row['id']; ?>">Edit</a><a class="button danger" onclick="return deletecheck('Delete this forum and all topics/posts inside it?');" href="execute.php?take=forum_manage&action=delete_forum&id=<?php echo (int)$row['id']; ?>">Delete</a></span></td></tr>
                    <?php endwhile; else: ?><tr><td colspan="6" class="wc-empty">No forums created.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php elseif ($section == 'topics'): ?>
    <div class="wc-admin-card">
        <h2><?php echo $editTopic ? 'Edit Topic' : 'Topics Management'; ?></h2>
        <?php if ($editTopic): ?>
            <form class="wc-clean-form" action="execute.php?take=forum_manage&action=save_topic" method="post">
                <input type="hidden" name="id" value="<?php echo (int)$editTopic['id']; ?>">
                <label>Topic Title</label><input type="text" name="name" required maxlength="250" value="<?php echo wc_admin_h($editTopic['name']); ?>">
                <div class="wc-form-row"><div><label>Move to Forum</label><select name="forum" required><?php foreach ($forumsList as $forum): ?><option value="<?php echo (int)$forum['id']; ?>" <?php echo ((int)$editTopic['forum'] == (int)$forum['id']) ? 'selected' : ''; ?>><?php echo wc_admin_h($forum['name']); ?></option><?php endforeach; ?></select></div><div><label>Views</label><input type="number" name="views" value="<?php echo (int)$editTopic['views']; ?>"></div></div>
                <div class="wc-form-actions"><input type="submit" class="button primary big" value="Save Topic"><a class="button" href="index.php?page=forums&section=topics">Cancel</a></div>
            </form>
        <?php endif; ?>
        <div class="wc-table-wrap">
            <table class="wc-table">
                <thead><tr><th>ID</th><th>Topic</th><th>Forum</th><th>Author</th><th>Replies</th><th>Added</th><th>Actions</th></tr></thead><tbody>
                <?php
                $stmt = $DB->query("SELECT t.*, f.name AS forum_name, a.displayName AS author_name FROM `wcf_topics` t LEFT JOIN `wcf_forums` f ON f.id = t.forum LEFT JOIN `account_data` a ON a.id = t.author ORDER BY t.added DESC LIMIT 250");
                if ($stmt->rowCount()): while ($row = $stmt->fetch()): ?>
                    <tr><td>#<?php echo (int)$row['id']; ?></td><td><b><?php echo wc_admin_h($row['name']); ?></b></td><td><?php echo wc_admin_h($row['forum_name']); ?></td><td><?php echo wc_admin_h($row['author_name'] ? $row['author_name'] : 'Account '.$row['author']); ?></td><td><?php echo (int)$row['posts']; ?></td><td><?php echo wc_admin_h($row['added']); ?></td><td><span class="button-group"><a class="button" href="index.php?page=forums&section=topics&edit_topic=<?php echo (int)$row['id']; ?>">Edit</a><a class="button danger" onclick="return deletecheck('Delete this topic and all replies?');" href="execute.php?take=forum_manage&action=delete_topic&id=<?php echo (int)$row['id']; ?>">Delete</a></span></td></tr>
                <?php endwhile; else: ?><tr><td colspan="7" class="wc-empty">No topics yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php elseif ($section == 'posts'): ?>
    <div class="wc-admin-card">
        <h2><?php echo $editPost ? 'Edit Post' : 'Posts Management'; ?></h2>
        <?php if ($editPost): ?>
            <form class="wc-clean-form" action="execute.php?take=forum_manage&action=save_post" method="post">
                <input type="hidden" name="id" value="<?php echo (int)$editPost['id']; ?>">
                <label>Post Title</label><input type="text" name="title" maxlength="250" value="<?php echo wc_admin_h($editPost['title']); ?>">
                <label>Post Content</label><textarea name="text" rows="10" required><?php echo wc_admin_h($editPost['text']); ?></textarea>
                <div class="wc-form-actions"><input type="submit" class="button primary big" value="Save Post"><a class="button" href="index.php?page=forums&section=posts">Cancel</a></div>
            </form>
        <?php endif; ?>
        <div class="wc-table-wrap">
            <table class="wc-table">
                <thead><tr><th>ID</th><th>Topic</th><th>Preview</th><th>Author</th><th>Added</th><th>Actions</th></tr></thead><tbody>
                <?php
                $stmt = $DB->query("SELECT p.*, t.name AS topic_name, a.displayName AS author_name FROM `wcf_posts` p LEFT JOIN `wcf_topics` t ON t.id = p.topic LEFT JOIN `account_data` a ON a.id = p.author ORDER BY p.added DESC LIMIT 350");
                if ($stmt->rowCount()): while ($row = $stmt->fetch()): ?>
                    <tr><td>#<?php echo (int)$row['id']; ?></td><td><?php echo wc_admin_h($row['topic_name']); ?></td><td><b><?php echo wc_admin_h($row['title']); ?></b><small><?php echo wc_admin_h(wc_admin_cut($row['text'], 120)); ?></small></td><td><?php echo wc_admin_h($row['author_name'] ? $row['author_name'] : 'Account '.$row['author']); ?></td><td><?php echo wc_admin_h($row['added']); ?></td><td><span class="button-group"><a class="button" href="index.php?page=forums&section=posts&edit_post=<?php echo (int)$row['id']; ?>">Edit</a><a class="button danger" onclick="return deletecheck('Delete this post?');" href="execute.php?take=forum_manage&action=delete_post&id=<?php echo (int)$row['id']; ?>">Delete</a></span></td></tr>
                <?php endwhile; else: ?><tr><td colspan="6" class="wc-empty">No posts yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

</div>
</section>
