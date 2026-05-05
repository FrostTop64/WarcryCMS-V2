<?php
if (!defined('init_pages')) { header('HTTP/1.0 404 not found'); exit; }
$CORE->load_CoreModule('forums.parser');
$TPL->SetTitle('Video Preview');
$TPL->AddCSS('template/style/page-media.css');
$TPL->LoadHeader();
function wc_youtube_id_public($url) { if (preg_match('~(?:v=|youtu\.be/|embed/|shorts/|live/)([A-Za-z0-9_-]{6,})~', (string)$url, $m)) return $m[1]; return preg_match('~^[A-Za-z0-9_-]{6,}$~',(string)$url)?$url:''; }
$movieId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
?>
<div class="content_holder"><div class="sub-page-title"><div id="title"><h1>Media<p></p><span></span></h1></div></div><div class="container_2" align="center" style="padding:30px 75px; width:846px;"><div class="media-header"><h2>VIDEOS</h2><div class="clear"></div><div class="bline"></div></div>
<?php
$IdError = true;
if ($movieId > 0) {
    $res = $DB->prepare('SELECT * FROM `movies` WHERE `id` = :id AND `status` = 1 LIMIT 1;');
    $res->bindParam(':id', $movieId, PDO::PARAM_INT); $res->execute();
    if ($res->rowCount() > 0) {
        $row = $res->fetch(); $youtubeId = wc_youtube_id_public($row['youtube']);
        if ($youtubeId !== '') {
            echo '<div class="wc-youtube-frame"><iframe src="https://www.youtube.com/embed/', htmlspecialchars($youtubeId, ENT_QUOTES, 'UTF-8'), '?rel=0&modestbranding=1" title="', htmlspecialchars(stripslashes($row['name']), ENT_QUOTES, 'UTF-8'), '" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe></div>';
        } else { echo '<div class="there-is-nothing">This video does not have a valid YouTube link.</div>'; }
        if (($text = $CACHE->get('media/movies/movie_' . $row['id'])) === false) {
            $parser = new SBBCodeParser_Document(true, false);
            $text = stripslashes($row['descr']);
            $text = $parser->parse($text)->detect_links()->detect_emails()->detect_emoticons()->get_html(true);
            $text = preg_replace('/<br\s*\/?\>\s<br\s*\/?\>\s+/', '<br/>', $text);
            unset($parser);
            $CACHE->store('media/movies/movie_' . $row['id'], $text, '2592000');
        }
        echo '<div class="open-video-info"><h3>', htmlspecialchars(stripslashes($row['name']), ENT_QUOTES, 'UTF-8'), '</h3><p>', $text, '</p></div>';
        $IdError = false;
    }
}
if ($IdError) { $ERRORS->iPrint('Unable to proceed to the requested page. Invalid movie id.', true, true); echo '<div align="left" style="margin:30px 0;"><input type="button" value="Go back" onclick="history.go(-1); return false;"></div>'; }
?>
</div></div><style>.wc-youtube-frame{width:846px;height:476px;background:#000;box-shadow:0 0 18px rgba(0,0,0,.65);margin:0 auto 18px;overflow:hidden}.wc-youtube-frame iframe{display:block;width:846px;height:476px;border:0}.open-video-info{text-align:left;margin-top:18px}.open-video-info h3{color:#d6a94b;margin:0 0 8px}.open-video-info p{color:#aaa;line-height:1.55}</style><?php $TPL->LoadFooter(); ?>
