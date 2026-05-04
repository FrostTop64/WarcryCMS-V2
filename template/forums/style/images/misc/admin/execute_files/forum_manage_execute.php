<?PHP
if (!defined('init_executes'))
{
    header('HTTP/1.0 404 not found');
    exit;
}

$CORE->loggedInOrReturn();
$CORE->CheckPermissionsExecute(PERMISSION_FORUMS);

$ERRORS->NewInstance('forum_manage');
$ERRORS->onSuccess('Forum content was successfully updated.', '/index.php?page=forums');

define('WC_FORUM_REDIRECT', '/index.php?page=forums');

function wc_redirect($path)
{
    global $config;
    header('Location: '.$config['BaseURL'].'/admin'.$path);
    exit;
}

function wc_recount_forum($forumId)
{
    global $DB;
    $forumId = (int)$forumId;
    if ($forumId <= 0) { return; }

    $topics = (int)$DB->query("SELECT COUNT(*) FROM `wcf_topics` WHERE `forum` = ".$forumId)->fetchColumn();
    $posts = (int)$DB->query("SELECT COUNT(*) FROM `wcf_posts` p INNER JOIN `wcf_topics` t ON t.id = p.topic WHERE t.forum = ".$forumId)->fetchColumn();

    $lastTopicId = 0;
    $stmt = $DB->query("SELECT `id` FROM `wcf_topics` WHERE `forum` = ".$forumId." ORDER BY `lastpost_time` DESC, `added` DESC LIMIT 1");
    if ($stmt && $stmt->rowCount()) { $lastTopicId = (int)$stmt->fetchColumn(); }

    $upd = $DB->prepare("UPDATE `wcf_forums` SET `topics` = :topics, `posts` = :posts, `lasttopic_id` = :lasttopic WHERE `id` = :id LIMIT 1");
    $upd->bindParam(':topics', $topics, PDO::PARAM_INT);
    $upd->bindParam(':posts', $posts, PDO::PARAM_INT);
    $upd->bindParam(':lasttopic', $lastTopicId, PDO::PARAM_INT);
    $upd->bindParam(':id', $forumId, PDO::PARAM_INT);
    $upd->execute();
}

function wc_recount_topic($topicId)
{
    global $DB;
    $topicId = (int)$topicId;
    if ($topicId <= 0) { return 0; }

    $posts = (int)$DB->query("SELECT COUNT(*) FROM `wcf_posts` WHERE `topic` = ".$topicId)->fetchColumn();
    $lastPostId = 0;
    $lastPostTime = '0000-00-00 00:00:00';
    $stmt = $DB->query("SELECT `id`, `added` FROM `wcf_posts` WHERE `topic` = ".$topicId." ORDER BY `added` DESC, `id` DESC LIMIT 1");
    if ($stmt && $stmt->rowCount()) { $row = $stmt->fetch(); $lastPostId = (int)$row['id']; $lastPostTime = $row['added']; }

    $upd = $DB->prepare("UPDATE `wcf_topics` SET `posts` = :posts, `lastpost_id` = :lastpost, `lastpost_time` = :lasttime WHERE `id` = :id LIMIT 1");
    $upd->bindParam(':posts', $posts, PDO::PARAM_INT);
    $upd->bindParam(':lastpost', $lastPostId, PDO::PARAM_INT);
    $upd->bindParam(':lasttime', $lastPostTime, PDO::PARAM_STR);
    $upd->bindParam(':id', $topicId, PDO::PARAM_INT);
    $upd->execute();

    $stmt = $DB->prepare("SELECT `forum` FROM `wcf_topics` WHERE `id` = :id LIMIT 1");
    $stmt->bindParam(':id', $topicId, PDO::PARAM_INT);
    $stmt->execute();
    if ($stmt->rowCount()) { $forum = (int)$stmt->fetchColumn(); wc_recount_forum($forum); return $forum; }
    return 0;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action == 'add_forum' || $action == 'save_forum')
{
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $category = isset($_POST['category']) ? (int)$_POST['category'] : 0;
    $position = isset($_POST['position']) ? (int)$_POST['position'] : 0;
    $class = isset($_POST['class']) ? (int)$_POST['class'] : 0;

    if ($name == '') { $ERRORS->Add('Forum name is required.'); }
    if ($category <= 0) { $ERRORS->Add('Please select a category.'); }
    $ERRORS->Check('/index.php?page=forums&section=forums');

    if ($action == 'add_forum') {
        $stmt = $DB->prepare("INSERT INTO `wcf_forums` (`category`, `name`, `description`, `class`, `position`, `flags`) VALUES (:category, :name, :description, :class, :position, 0)");
    } else {
        if ($id <= 0) { $ERRORS->Add('Forum ID is missing.'); $ERRORS->Check('/index.php?page=forums&section=forums'); }
        $stmt = $DB->prepare("UPDATE `wcf_forums` SET `category` = :category, `name` = :name, `description` = :description, `class` = :class, `position` = :position WHERE `id` = :id LIMIT 1");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    }
    $stmt->bindParam(':category', $category, PDO::PARAM_INT);
    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    $stmt->bindParam(':description', $description, PDO::PARAM_STR);
    $stmt->bindParam(':class', $class, PDO::PARAM_INT);
    $stmt->bindParam(':position', $position, PDO::PARAM_INT);
    $stmt->execute();
    wc_recount_forum($action == 'add_forum' ? (int)$DB->lastInsertId() : $id);
    $ERRORS->triggerSuccess();
    $ERRORS->Check('/index.php?page=forums&section=forums');
}
else if ($action == 'delete_forum')
{
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) { $ERRORS->Add('Forum ID is missing.'); $ERRORS->Check('/index.php?page=forums&section=forums'); }
    $topicIds = array();
    $stmt = $DB->prepare("SELECT `id` FROM `wcf_topics` WHERE `forum` = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT); $stmt->execute();
    while ($row = $stmt->fetch()) { $topicIds[] = (int)$row['id']; }
    foreach ($topicIds as $tid) { $DB->query("DELETE FROM `wcf_posts` WHERE `topic` = ".$tid); }
    $del = $DB->prepare("DELETE FROM `wcf_topics` WHERE `forum` = :id"); $del->bindParam(':id', $id, PDO::PARAM_INT); $del->execute();
    $del = $DB->prepare("DELETE FROM `wcf_forums` WHERE `id` = :id LIMIT 1"); $del->bindParam(':id', $id, PDO::PARAM_INT); $del->execute();
    $ERRORS->triggerSuccess(); $ERRORS->Check('/index.php?page=forums&section=forums');
}
else if ($action == 'save_topic')
{
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $forum = isset($_POST['forum']) ? (int)$_POST['forum'] : 0;
    $views = isset($_POST['views']) ? (int)$_POST['views'] : 0;
    if ($id <= 0 || $name == '' || $forum <= 0) { $ERRORS->Add('Topic data is incomplete.'); }
    $ERRORS->Check('/index.php?page=forums&section=topics');
    $oldForum = 0; $s = $DB->prepare("SELECT `forum` FROM `wcf_topics` WHERE `id` = :id LIMIT 1"); $s->bindParam(':id', $id, PDO::PARAM_INT); $s->execute(); if ($s->rowCount()) { $oldForum = (int)$s->fetchColumn(); }
    $stmt = $DB->prepare("UPDATE `wcf_topics` SET `forum` = :forum, `name` = :name, `views` = :views WHERE `id` = :id LIMIT 1");
    $stmt->bindParam(':forum', $forum, PDO::PARAM_INT); $stmt->bindParam(':name', $name, PDO::PARAM_STR); $stmt->bindParam(':views', $views, PDO::PARAM_INT); $stmt->bindParam(':id', $id, PDO::PARAM_INT); $stmt->execute();
    wc_recount_topic($id); if ($oldForum && $oldForum != $forum) { wc_recount_forum($oldForum); wc_recount_forum($forum); }
    $ERRORS->triggerSuccess(); $ERRORS->Check('/index.php?page=forums&section=topics');
}
else if ($action == 'delete_topic')
{
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) { $ERRORS->Add('Topic ID is missing.'); $ERRORS->Check('/index.php?page=forums&section=topics'); }
    $forum = 0; $s = $DB->prepare("SELECT `forum` FROM `wcf_topics` WHERE `id` = :id LIMIT 1"); $s->bindParam(':id', $id, PDO::PARAM_INT); $s->execute(); if ($s->rowCount()) { $forum = (int)$s->fetchColumn(); }
    $del = $DB->prepare("DELETE FROM `wcf_posts` WHERE `topic` = :id"); $del->bindParam(':id', $id, PDO::PARAM_INT); $del->execute();
    $del = $DB->prepare("DELETE FROM `wcf_topics` WHERE `id` = :id LIMIT 1"); $del->bindParam(':id', $id, PDO::PARAM_INT); $del->execute();
    if ($forum) { wc_recount_forum($forum); }
    $ERRORS->triggerSuccess(); $ERRORS->Check('/index.php?page=forums&section=topics');
}
else if ($action == 'save_post')
{
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $text = isset($_POST['text']) ? trim($_POST['text']) : '';
    if ($id <= 0 || $text == '') { $ERRORS->Add('Post data is incomplete.'); }
    $ERRORS->Check('/index.php?page=forums&section=posts');
    $editor = (int)$CURUSER->get('id');
    $stmt = $DB->prepare("UPDATE `wcf_posts` SET `title` = :title, `text` = :text, `lastedit_by` = :editor, `lastedit_time` = NOW() WHERE `id` = :id LIMIT 1");
    $stmt->bindParam(':title', $title, PDO::PARAM_STR); $stmt->bindParam(':text', $text, PDO::PARAM_STR); $stmt->bindParam(':editor', $editor, PDO::PARAM_INT); $stmt->bindParam(':id', $id, PDO::PARAM_INT); $stmt->execute();
    $s = $DB->prepare("SELECT `topic` FROM `wcf_posts` WHERE `id` = :id LIMIT 1"); $s->bindParam(':id', $id, PDO::PARAM_INT); $s->execute(); if ($s->rowCount()) { wc_recount_topic((int)$s->fetchColumn()); }
    $ERRORS->triggerSuccess(); $ERRORS->Check('/index.php?page=forums&section=posts');
}
else if ($action == 'delete_post')
{
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) { $ERRORS->Add('Post ID is missing.'); $ERRORS->Check('/index.php?page=forums&section=posts'); }
    $topic = 0; $s = $DB->prepare("SELECT `topic` FROM `wcf_posts` WHERE `id` = :id LIMIT 1"); $s->bindParam(':id', $id, PDO::PARAM_INT); $s->execute(); if ($s->rowCount()) { $topic = (int)$s->fetchColumn(); }
    $del = $DB->prepare("DELETE FROM `wcf_posts` WHERE `id` = :id LIMIT 1"); $del->bindParam(':id', $id, PDO::PARAM_INT); $del->execute();
    if ($topic) { wc_recount_topic($topic); }
    $ERRORS->triggerSuccess(); $ERRORS->Check('/index.php?page=forums&section=posts');
}
else
{
    header('HTTP/1.0 404 not found');
    exit;
}
