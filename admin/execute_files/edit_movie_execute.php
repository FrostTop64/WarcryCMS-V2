<?PHP
if (!defined('init_executes')) { header('HTTP/1.0 404 not found'); exit; }

$CORE->loggedInOrReturn();
$CORE->CheckPermissionsExecute(PERMISSION_MEDIA_MOVIES);

function wc_admin_url_edit($config, $page, $extra = '') {
    return rtrim($config['BaseURL'], '/') . '/admin/index.php?page=' . $page . $extra;
}
function wc_youtube_id_edit($url) {
    $url = trim((string)$url);
    if ($url === '') return '';
    if (preg_match('~^[A-Za-z0-9_-]{6,}$~', $url)) return $url;
    if (preg_match('~youtu\.be/([A-Za-z0-9_-]{6,})~i', $url, $m)) return $m[1];
    if (preg_match('~youtube\.com/(?:embed|shorts|live)/([A-Za-z0-9_-]{6,})~i', $url, $m)) return $m[1];
    $parts = @parse_url($url);
    if (is_array($parts) && isset($parts['query'])) {
        parse_str($parts['query'], $q);
        if (isset($q['v']) && preg_match('~^[A-Za-z0-9_-]{6,}$~', $q['v'])) return $q['v'];
    }
    if (preg_match('~[?&]v=([A-Za-z0-9_-]{6,})~i', $url, $m)) return $m[1];
    return '';
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$title = isset($_POST['name']) ? trim($_POST['name']) : '';
$youtubeRaw = isset($_POST['youtube']) ? trim($_POST['youtube']) : '';
$short_text = isset($_POST['short_text']) ? trim($_POST['short_text']) : '';
$text = isset($_POST['text']) ? trim($_POST['text']) : '';
$status = isset($_POST['status']) ? (int)$_POST['status'] : 1;
$youtubeId = wc_youtube_id_edit($youtubeRaw);

$back = wc_admin_url_edit($config, 'movie-edit', '&id=' . $id);
if ($id <= 0) { header('Location: ' . wc_admin_url_edit($config, 'media', '&error=movie_id')); exit; }
if ($title === '' || $youtubeId === '' || $short_text === '' || $text === '') { header('Location: ' . $back . '&error=validation'); exit; }

$res = $DB->prepare('SELECT `id` FROM `movies` WHERE `id`=:id LIMIT 1;');
$res->bindParam(':id', $id, PDO::PARAM_INT);
$res->execute();
if ($res->rowCount() == 0) { header('Location: ' . wc_admin_url_edit($config, 'media', '&error=notfound')); exit; }

$youtube = 'https://www.youtube.com/watch?v=' . $youtubeId;
$image = $youtubeId;
$upd = $DB->prepare('UPDATE `movies` SET `name`=:title, `descr`=:descr, `short_text`=:short_text, `image`=:image, `mp4`="", `webm`="", `ogg`="", `youtube`=:youtube, `status`=:status WHERE `id`=:id LIMIT 1;');
$upd->bindParam(':title', $title, PDO::PARAM_STR);
$upd->bindParam(':descr', $text, PDO::PARAM_STR);
$upd->bindParam(':short_text', $short_text, PDO::PARAM_STR);
$upd->bindParam(':image', $image, PDO::PARAM_STR);
$upd->bindParam(':youtube', $youtube, PDO::PARAM_STR);
$upd->bindParam(':status', $status, PDO::PARAM_INT);
$upd->bindParam(':id', $id, PDO::PARAM_INT);
$upd->execute();

@unlink($config['RootPath'] . '/cache/media/movies/movie_' . $id . '_cache');
header('Location: ' . wc_admin_url_edit($config, 'media', '&success=movie_updated'));
exit;
