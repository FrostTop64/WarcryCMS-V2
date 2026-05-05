<?PHP
if (!defined('init_executes')) { header('HTTP/1.0 404 not found'); exit; }

$CORE->loggedInOrReturn();
$CORE->CheckPermissionsExecute(PERMISSION_MEDIA_MOVIES);

function wc_admin_url($config, $page, $extra = '') {
    return rtrim($config['BaseURL'], '/') . '/admin/index.php?page=' . $page . $extra;
}
function wc_youtube_id($url) {
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
function wc_safe_dir($title) {
    $dir = preg_replace('/[^A-Za-z0-9-_]/', '', str_replace(' ', '_', trim($title)));
    return ($dir !== '' ? $dir : 'youtube_video') . '_' . substr(md5(uniqid(rand(), true)), 0, 8);
}

$title = isset($_POST['name']) ? trim($_POST['name']) : '';
$youtubeRaw = isset($_POST['youtube']) ? trim($_POST['youtube']) : '';
$short_text = isset($_POST['short_text']) ? trim($_POST['short_text']) : '';
$text = isset($_POST['text']) ? trim($_POST['text']) : '';
$youtubeId = wc_youtube_id($youtubeRaw);

$errors = array();
if ($title === '') $errors[] = 'missing_title';
if ($youtubeId === '') $errors[] = 'bad_youtube';
if ($short_text === '') $errors[] = 'missing_short';
if ($text === '') $errors[] = 'missing_desc';
if (!empty($errors)) { header('Location: ' . wc_admin_url($config, 'movie-add', '&error=' . implode(',', $errors))); exit; }

$dirname = wc_safe_dir($title);
$folder = $config['RootPath'] . '/uploads/media/movies/' . $dirname;
if (!is_dir($folder)) { @mkdir($folder, 0755, true); }
if (!is_dir($folder)) { header('Location: ' . wc_admin_url($config, 'movie-add', '&error=folder')); exit; }

$youtube = 'https://www.youtube.com/watch?v=' . $youtubeId;
$image = $youtubeId; // For YouTube videos we store the ID as image fallback. No local thumbnail required.

try {
    $insert = $DB->prepare("INSERT INTO `movies` (`name`, `descr`, `short_text`, `added`, `account`, `dirname`, `image`, `mp4`, `webm`, `ogg`, `youtube`, `status`) VALUES (:title, :descr, :short_text, :added, :acc, :dirname, :image, '', '', '', :youtube, '1');");
    $insert->bindParam(':title', $title, PDO::PARAM_STR);
    $insert->bindParam(':descr', $text, PDO::PARAM_STR);
    $insert->bindParam(':short_text', $short_text, PDO::PARAM_STR);
    $insert->bindParam(':added', $CORE->getTime(), PDO::PARAM_STR);
    $insert->bindParam(':acc', $CURUSER->get('id'), PDO::PARAM_INT);
    $insert->bindParam(':dirname', $dirname, PDO::PARAM_STR);
    $insert->bindParam(':image', $image, PDO::PARAM_STR);
    $insert->bindParam(':youtube', $youtube, PDO::PARAM_STR);
    $insert->execute();
} catch (Exception $e) {
    header('Location: ' . wc_admin_url($config, 'movie-add', '&error=db'));
    exit;
}

header('Location: ' . wc_admin_url($config, 'media', '&success=movie_added'));
exit;
