<?php
if (!defined('init_pages')) { header('HTTP/1.0 404 not found'); exit; }

/* Warcry CMS - Professional Armory Profile for AzerothCore/WotLK 3.3.5a */
$TPL->SetTitle('Armory Profile');
$TPL->LoadHeader();

function wa_h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function wa_mask($name){ $name=(string)$name; $l=strlen($name); if($l<=0) return '****'; if($l<=2) return str_repeat('*',$l); return substr($name,0,2).str_repeat('*', max(3,$l-2)); }
function wa_money($copper){ $c=max(0,(int)$copper); return array(floor($c/10000), floor(($c%10000)/100), $c%100); }
function wa_playtime($sec){ $sec=(int)$sec; $d=floor($sec/86400); $h=floor(($sec%86400)/3600); $m=floor(($sec%3600)/60); return $d.'d '.$h.'h '.$m.'m'; }
function wa_race($id){ $a=array(1=>'Human',2=>'Orc',3=>'Dwarf',4=>'Night Elf',5=>'Undead',6=>'Tauren',7=>'Gnome',8=>'Troll',10=>'Blood Elf',11=>'Draenei'); return isset($a[(int)$id])?$a[(int)$id]:'Unknown'; }
function wa_class($id){ $a=array(1=>'Warrior',2=>'Paladin',3=>'Hunter',4=>'Rogue',5=>'Priest',6=>'Death Knight',7=>'Shaman',8=>'Mage',9=>'Warlock',11=>'Druid'); return isset($a[(int)$id])?$a[(int)$id]:'Unknown'; }
function wa_faction($race){ return in_array((int)$race,array(1,3,4,7,11),true)?'Alliance':'Horde'; }
function wa_class_slug($id){ $a=array(1=>'warrior',2=>'paladin',3=>'hunter',4=>'rogue',5=>'priest',6=>'deathknight',7=>'shaman',8=>'mage',9=>'warlock',11=>'druid'); return isset($a[(int)$id])?$a[(int)$id]:'warrior'; }
function wa_gender_slug($id){ return ((int)$id===1)?'female':'male'; }
function wa_quality($q){ $a=array(0=>'poor',1=>'common',2=>'uncommon',3=>'rare',4=>'epic',5=>'legendary',6=>'artifact',7=>'heirloom'); return isset($a[(int)$q])?$a[(int)$q]:'common'; }
function wa_sql_ident($name){ $name=(string)$name; if(!preg_match('/^[A-Za-z0-9_]+$/',$name)) return ''; return '`'.str_replace('`','``',$name).'`'; }
function wa_sql_quote($db,$value){ try { return $db->quote((string)$value); } catch(Exception $e) { return "'".str_replace("'","\\'",(string)$value)."'"; } }
function wa_table_exists($db,$table){ $table=(string)$table; if(!preg_match('/^[A-Za-z0-9_]+$/',$table)) return false; try{ $s=$db->query('SHOW TABLES LIKE '.wa_sql_quote($db,$table)); return $s ? (bool)$s->fetchColumn() : false; }catch(Exception $e){ return false; } }
function wa_world_db_name($db){
    static $cached=null; if($cached!==null) return $cached;
    try{
        $rows=$db->query('SHOW DATABASES')->fetchAll(PDO::FETCH_COLUMN);
        foreach($rows as $n){
            if(!preg_match('/^[A-Za-z0-9_]+$/',$n)) continue;
            if(preg_match('/(world|acore_world|azerothcore_world)$/i',$n)){
                try{ if($db->query('SHOW TABLES FROM '.wa_sql_ident($n).' LIKE '.wa_sql_quote($db,'item_template'))->fetchColumn()){ $cached=$n; return $cached; } }catch(Exception $e){}
            }
        }
    }catch(Exception $e){}
    $cached='world'; return $cached;
}
function wa_icon_cache_dir(){ $dir=dirname(__FILE__).'/../cache/wowhead_icons'; if(!is_dir($dir)) @mkdir($dir,0755,true); return $dir; }
function wa_fetch_wowhead_icon($type,$entry){
    $type=($type==='achievement')?'achievement':'item'; $entry=(int)$entry; if($entry<=0) return '';
    $dir=wa_icon_cache_dir(); $file=$dir.'/'.$type.'_'.$entry.'.txt';
    if(is_file($file)) { $cached=trim((string)@file_get_contents($file)); if($cached!=='' && preg_match('/^[a-z0-9_]+$/',$cached)) return $cached; }
    $url='https://www.wowhead.com/wotlk/'.$type.'='.$entry.'&xml'; $xml='';
    if(function_exists('curl_init')){
        $ch=curl_init($url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true); curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,5); curl_setopt($ch,CURLOPT_TIMEOUT,10);
        // Secure TLS: verify Wowhead SSL certificate and hostname (fixes SAST finding).
        // Do NOT disable CURLOPT_SSL_VERIFYPEER / CURLOPT_SSL_VERIFYHOST.
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 WarcryCMS Armory Icon Cache');
        $xml=(string)curl_exec($ch); curl_close($ch);
    } else {
        $ctx=stream_context_create(array(
            'http'=>array('timeout'=>10,'header'=>"User-Agent: Mozilla/5.0 WarcryCMS Armory Icon Cache\r\n"),
            'ssl'=>array('verify_peer'=>true,'verify_peer_name'=>true)
        ));
        $xml=(string)@file_get_contents($url,false,$ctx);
    }
    $icon=''; if($xml && preg_match('/<icon[^>]*>([^<]+)<\/icon>/i',$xml,$m)) $icon=strtolower(trim($m[1]));
    if($icon!=='' && preg_match('/^[a-z0-9_]+$/',$icon)){ @file_put_contents($file,$icon); return $icon; }
    return '';
}
function wa_icon_url($icon){ $icon=strtolower((string)$icon); if(!preg_match('/^[a-z0-9_]+$/',$icon)) $icon='inv_misc_questionmark'; return 'https://wow.zamimg.com/images/wow/icons/large/'.$icon.'.jpg'; }
function wa_item_icon_guess($entry,$name,$class=0,$inv=0){
    $entry=(int)$entry; $name=strtolower((string)$name); $remote=wa_fetch_wowhead_icon('item',$entry); if($remote!=='') return $remote;
    $known=array(25=>'inv_sword_04',35=>'inv_staff_08',36=>'inv_axe_04',37=>'inv_axe_04',38=>'inv_shirt_05',39=>'inv_pants_02',40=>'inv_boots_05',43=>'inv_boots_05',44=>'inv_pants_02',45=>'inv_shirt_05',47=>'inv_boots_05',48=>'inv_pants_02',49=>'inv_shirt_05',2092=>'inv_throwingknife_01',19019=>'inv_sword_39',32837=>'inv_weapon_glave_01',32838=>'inv_weapon_glave_01',17182=>'inv_hammer_unique_sulfuras',49623=>'inv_sword_155',50730=>'inv_axe_113',34648=>'inv_boots_plate_05',34649=>'inv_gauntlets_28',34650=>'inv_chest_plate06',34651=>'inv_belt_12',34652=>'inv_helmet_125',34653=>'inv_bracer_13',34655=>'inv_shoulder_92',34656=>'inv_pants_cloth_27',34657=>'inv_jewelry_necklace_37',34658=>'inv_jewelry_ring_34',34659=>'inv_misc_cape_19');
    if(isset($known[$entry])) return $known[$entry];
    if(strpos($name,'warglaive')!==false) return 'inv_weapon_glave_01'; if(strpos($name,'shirt')!==false || (int)$inv===4) return 'inv_shirt_01'; if(strpos($name,'pants')!==false || strpos($name,'leggings')!==false || (int)$inv===7) return 'inv_pants_02'; if(strpos($name,'boot')!==false || (int)$inv===8) return 'inv_boots_05'; if(strpos($name,'sword')!==false || (int)$class===2) return 'inv_sword_04'; if(strpos($name,'shield')!==false) return 'inv_shield_04'; if(strpos($name,'helm')!==false || (int)$inv===1) return 'inv_helmet_03'; if(strpos($name,'shoulder')!==false || (int)$inv===3) return 'inv_shoulder_02'; if(strpos($name,'chest')!==false || (int)$inv===5 || (int)$inv===20) return 'inv_chest_plate05'; if(strpos($name,'glove')!==false || (int)$inv===10) return 'inv_gauntlets_04'; if(strpos($name,'ring')!==false || (int)$inv===11) return 'inv_jewelry_ring_03'; if(strpos($name,'trinket')!==false || (int)$inv===12) return 'inv_misc_gem_pearl_05'; return 'inv_misc_questionmark';
}
function wa_achievement_icon($id){ $remote=wa_fetch_wowhead_icon('achievement',(int)$id); return $remote!=='' ? $remote : 'achievement_general'; }
function wa_armory_scene_asset($race,$gender){ return './template/style/images/armory/'.wa_gender_slug($gender).'_'.strtolower(wa_faction($race)).'.png'; }
function wa_get_auth($CORE,$id){ try{ $a=$CORE->AuthDatabaseConnection(); $s=$a->prepare('SELECT id, username, joindate, last_login, online, expansion FROM account WHERE id=:id LIMIT 1'); $s->execute(array(':id'=>(int)$id)); return $s->fetch(PDO::FETCH_ASSOC); }catch(Exception $e){ return false; } }
function wa_get_cms($DB,$id){ try{ $s=$DB->prepare('SELECT `id`, `displayName`, `silver`, `gold`, `country`, `avatar`, `avatarType`, `rank`, `status`, `selected_realm` FROM `account_data` WHERE `id`=:id LIMIT 1'); $s->execute(array(':id'=>(int)$id)); return $s->fetch(PDO::FETCH_ASSOC); }catch(Exception $e){ return false; } }
function wa_get_avatar($user){ if(!$user) return './resources/avatars/rookie_avatar_1.jpg'; if(defined('AVATAR_TYPE_UPLOAD') && (int)$user['avatarType']===AVATAR_TYPE_UPLOAD && !empty($user['avatar'])) return $user['avatar']; try{ $g=new AvatarGallery(); $a=$g->get((int)$user['avatar']); if($a) return './resources/avatars/'.$a->string(); }catch(Exception $e){} return './resources/avatars/rookie_avatar_1.jpg'; }
function wa_stat_value($row,$keys,$decimals=0){ foreach((array)$keys as $k){ if(isset($row[$k]) && $row[$k] !== null && $row[$k] !== '') return $decimals>0 ? number_format((float)$row[$k],$decimals) : number_format((float)$row[$k]); } return '0'; }

function wa_get_world_name($db,$world,$tables,$idCol,$nameCols,$id){
    $id=(int)$id; if($id<=0) return '';
    foreach((array)$tables as $t){
        if(!preg_match('/^[A-Za-z0-9_]+$/',$t)) continue;
        try{
            $exists=$db->query('SHOW TABLES FROM '.wa_sql_ident($world).' LIKE '.wa_sql_quote($db,$t));
            if(!$exists || !$exists->fetchColumn()) continue;
            $cols=$db->query('SHOW COLUMNS FROM '.wa_sql_ident($world).'.'.wa_sql_ident($t))->fetchAll(PDO::FETCH_COLUMN);
            if(!in_array($idCol,$cols,true)) continue;
            $pick=''; foreach((array)$nameCols as $c){ if(in_array($c,$cols,true)){ $pick=$c; break; } }
            if($pick==='') continue;
            $q=$db->prepare('SELECT '.wa_sql_ident($pick).' FROM '.wa_sql_ident($world).'.'.wa_sql_ident($t).' WHERE '.wa_sql_ident($idCol).'=:id LIMIT 1');
            $q->execute(array(':id'=>$id)); $v=$q->fetchColumn();
            if($v!==false && trim((string)$v)!=='') return (string)$v;
        }catch(Exception $e){}
    }
    return '';
}
function wa_achievement_info($db,$world,$id){
    static $known=array(
        6=>array('Level 10','achievement_level_10'),7=>array('Level 20','achievement_level_20'),8=>array('Level 30','achievement_level_30'),9=>array('Level 40','achievement_level_40'),10=>array('Level 50','achievement_level_50'),11=>array('Level 60','achievement_level_60'),12=>array('Level 70','achievement_level_70'),13=>array('Level 80','achievement_level_80'),
        131=>array('Journeyman in First Aid','spell_holy_sealofsacrifice'),132=>array('Expert in First Aid','spell_holy_sealofsacrifice'),133=>array('Artisan in First Aid','spell_holy_sealofsacrifice'),
        134=>array('Master in First Aid','spell_holy_sealofsacrifice'),135=>array('Grand Master in First Aid','spell_holy_sealofsacrifice')
    );
    $id=(int)$id; $name=''; $icon='';
    if(isset($known[$id])){ $name=$known[$id][0]; $icon=$known[$id][1]; }
    $dbName=wa_get_world_name($db,$world,array('achievement_dbc','achievement'), 'ID', array('Name_Lang_enUS','name','Name'), $id);
    if($dbName!=='') $name=$dbName;
    $remote=wa_fetch_wowhead_icon('achievement',$id); if($remote!=='') $icon=$remote;
    if($name==='') $name='Achievement #'.$id; if($icon==='') $icon='achievement_general';
    return array('name'=>$name,'icon'=>$icon);
}
function wa_skill_static($id){
    $m=array(
        6=>array('Frost','spell_frost_frostbolt02'),8=>array('Fire','spell_fire_firebolt02'),26=>array('Arms','ability_warrior_savageblow'),38=>array('Combat','ability_backstab'),39=>array('Subtlety','ability_stealth'),43=>array('Swords','inv_sword_04'),44=>array('Axes','inv_axe_01'),45=>array('Bows','inv_weapon_bow_05'),46=>array('Guns','inv_weapon_rifle_01'),50=>array('Beast Mastery','ability_hunter_bestialdiscipline'),51=>array('Survival','ability_hunter_swiftstrike'),54=>array('Maces','inv_mace_01'),55=>array('Two-Handed Swords','inv_sword_04'),56=>array('Holy','spell_holy_holybolt'),78=>array('Shadow Magic','spell_shadow_shadowwordpain'),95=>array('Defense','ability_warrior_defensivestance'),98=>array('Language: Common','inv_misc_book_09'),101=>array('Dwarven','inv_misc_book_09'),109=>array('Language: Orcish','inv_misc_book_09'),113=>array('Language: Darnassian','inv_misc_book_09'),118=>array('Dual Wield','ability_dualwield'),124=>array('Taur-ahe','inv_misc_book_09'),125=>array('Orcish','inv_misc_book_09'),126=>array('Language: Dwarven','inv_misc_book_09'),129=>array('First Aid','spell_holy_sealofsacrifice'),136=>array('Staves','inv_staff_08'),137=>array('Thalassian','inv_misc_book_09'),138=>array('Draconic','inv_misc_book_09'),139=>array('Demon Tongue','inv_misc_book_09'),140=>array('Titan','inv_misc_book_09'),141=>array('Old Tongue','inv_misc_book_09'),142=>array('Survival','ability_hunter_swiftstrike'),148=>array('Horse Riding','ability_mount_ridinghorse'),149=>array('Wolf Riding','ability_mount_blackdirewolf'),150=>array('Tiger Riding','ability_mount_jungletiger'),152=>array('Ram Riding','ability_mount_mountainram'),155=>array('Swimming','ability_druid_aquaticform'),160=>array('Two-Handed Maces','inv_mace_04'),162=>array('Unarmed','ability_golemthunderclap'),164=>array('Blacksmithing','trade_blacksmithing'),165=>array('Leatherworking','inv_misc_armorkit_17'),171=>array('Alchemy','trade_alchemy'),172=>array('Two-Handed Axes','inv_axe_09'),173=>array('Daggers','ability_steelmelee'),176=>array('Thrown','inv_throwingknife_01'),182=>array('Herbalism','spell_nature_naturetouchgrow'),183=>array('Generic','inv_misc_questionmark'),185=>array('Cooking','inv_misc_food_15'),186=>array('Mining','trade_mining'),188=>array('Pet - Imp','spell_shadow_summonimp'),197=>array('Tailoring','trade_tailoring'),202=>array('Engineering','trade_engineering'),226=>array('Crossbows','inv_weapon_crossbow_01'),228=>array('Wands','inv_wand_01'),229=>array('Polearms','inv_spear_06'),333=>array('Enchanting','trade_engraving'),356=>array('Fishing','trade_fishing'),393=>array('Skinning','inv_misc_pelt_wolf_01'),413=>array('Mail','inv_chest_chain'),414=>array('Leather','inv_chest_leather_09'),415=>array('Cloth','inv_chest_cloth_21'),433=>array('Shield','inv_shield_05'),473=>array('Fist Weapons','inv_gauntlets_04'),533=>array('Raptor Riding','ability_mount_raptor'),553=>array('Mechanostrider Piloting','ability_mount_mechastrider'),554=>array('Undead Horsemanship','ability_mount_undeadhorse'),573=>array('Restoration','spell_nature_healingtouch'),613=>array('Discipline','spell_holy_powerwordshield'),633=>array('Lockpicking','spell_nature_moonkey'),755=>array('Jewelcrafting','inv_misc_gem_01'),762=>array('Riding','spell_nature_swiftness'),770=>array('Blood','spell_deathknight_bloodpresence'),771=>array('Frost','spell_deathknight_frostpresence'),772=>array('Unholy','spell_deathknight_unholypresence'),773=>array('Inscription','inv_inscription_tradeskill01')
    );
    return isset($m[(int)$id])?$m[(int)$id]:array('Skill #'.(int)$id,'inv_misc_questionmark');
}
function wa_skill_info($db,$world,$id){ $x=wa_skill_static($id); $n=wa_get_world_name($db,$world,array('skillline_dbc','skill_line','skillline'), 'ID', array('DisplayName_Lang_enUS','Name_Lang_enUS','name','Name'), (int)$id); if($n!=='') $x[0]=$n; return array('name'=>$x[0],'icon'=>$x[1]); }
function wa_rep_static($id){
    $m=array(21=>'Booty Bay',46=>'Darnassus',47=>'Ironforge',54=>'Gnomeregan Exiles',59=>'Thorium Brotherhood',67=>'Horde',68=>'Undercity',69=>'Darnassus',72=>'Stormwind',76=>'Orgrimmar',81=>'Thunder Bluff',87=>'Bloodsail Buccaneers',92=>'Gelkis Clan Centaur',93=>'Magram Clan Centaur',270=>'Zandalar Tribe',469=>'Alliance',470=>'Ratchet',529=>'Argent Dawn',576=>'Timbermaw Hold',609=>'Cenarion Circle',729=>'Frostwolf Clan',730=>'Stormpike Guard',749=>'Hydraxian Waterlords',889=>'Warsong Outriders',890=>'Silverwing Sentinels',909=>'Darkmoon Faire',910=>'Brood of Nozdormu',922=>'Tranquillien',930=>'Exodar',932=>'The Aldor',933=>'The Consortium',934=>'The Scryers',935=>'The Sha\'tar',941=>'The Mag\'har',942=>'Cenarion Expedition',946=>'Honor Hold',947=>'Thrallmar',967=>'The Violet Eye',970=>'Sporeggar',978=>'Kurenai',989=>'Keepers of Time',990=>'The Scale of the Sands',1011=>'Lower City',1012=>'Ashtongue Deathsworn',1015=>'Netherwing',1031=>'Sha\'tari Skyguard',1037=>'Alliance Vanguard',1050=>'Valiance Expedition',1052=>'Horde Expedition',1064=>'The Taunka',1067=>'The Hand of Vengeance',1068=>'Explorers\' League',1073=>'The Kalu\'ak',1077=>'Shattered Sun Offensive',1090=>'Kirin Tor',1091=>'The Wyrmrest Accord',1094=>'The Silver Covenant',1098=>'Knights of the Ebon Blade',1104=>'Frenzyheart Tribe',1105=>'The Oracles',1106=>'Argent Crusade',1119=>'The Sons of Hodir',1124=>'The Sunreavers',1156=>'The Ashen Verdict');
    return isset($m[(int)$id])?$m[(int)$id]:'Faction #'.(int)$id;
}
function wa_rep_name($db,$world,$id){ $n=wa_get_world_name($db,$world,array('faction_dbc','faction'), 'ID', array('Name_Lang_enUS','name','Name'), (int)$id); return $n!==''?$n:wa_rep_static($id); }
function wa_rep_progress($standing){
    $s=(int)$standing; $ranks=array(array('Hated',-42000,-6000),array('Hostile',-6000,-3000),array('Unfriendly',-3000,0),array('Neutral',0,3000),array('Friendly',3000,9000),array('Honored',9000,21000),array('Revered',21000,42000),array('Exalted',42000,43000));
    foreach($ranks as $r){ if($s>=$r[1] && $s<$r[2]){ $cur=max(0,$s-$r[1]); $max=max(1,$r[2]-$r[1]); return array($r[0],$cur,$max,min(100,round(($cur/$max)*100))); } }
    if($s>=42000) return array('Exalted',999,999,100); return array('Hated',0,36000,0);
}

$uid=isset($_GET['uid'])?(int)$_GET['uid']:(isset($_GET['id'])?(int)$_GET['id']:0);
$charGuid=isset($_GET['char'])?(int)$_GET['char']:0;
$realmId=isset($_GET['realm'])?(int)$_GET['realm']:1;
$realmWasRequested=isset($_GET['realm']);
if(isset($_GET['wa_icon_ajax'])){
    $type=(isset($_GET['type']) && $_GET['type']==='achievement')?'achievement':'item';
    $id=isset($_GET['id'])?(int)$_GET['id']:0;
    $icon=wa_fetch_wowhead_icon($type,$id);
    if($icon==='') $icon=($type==='achievement')?'achievement_general':'inv_misc_questionmark';
    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: public, max-age=2592000');
    echo json_encode(array('ok'=>true,'type'=>$type,'id'=>$id,'icon'=>$icon,'url'=>wa_icon_url($icon)));
    exit;
}
if($uid<=0 && isset($CURUSER) && $CURUSER->isOnline()) $uid=(int)$CURUSER->get('id');
if($realmId<=0) $realmId=1;

$user=false; $auth=false; $chars=array(); $selected=false; $equipment=array(); $achievements=array(); $inventoryCount=0; $totalAch=0; $charStats=false; $skills=array(); $reputations=array(); $charDB=false;
if($uid>0){ $user=wa_get_cms($DB,$uid); $auth=wa_get_auth($CORE,$uid); if(!$realmWasRequested && $user && !empty($user['selected_realm'])) $realmId=(int)$user['selected_realm']; }
if(isset($realms_config[$realmId])){ $charDB=$CORE->RealmDatabaseConnection($realmId); }
if($charDB){
    if($uid>0){
        $charSelect='guid,name,race,class,gender,level,online,money,totaltime,totalKills,logout_time,position_x,position_y,position_z,map,totalHonorPoints,todayHonorPoints,yesterdayHonorPoints,todayKills,yesterdayKills,arenaPoints';
        try{ $s=$charDB->prepare('SELECT '.$charSelect.' FROM characters WHERE account=:a ORDER BY level DESC,name ASC'); $s->execute(array(':a'=>$uid)); $chars=$s->fetchAll(PDO::FETCH_ASSOC); }
        catch(Exception $e){ try{ $s=$charDB->prepare('SELECT guid,name,race,class,gender,level,online,money,totaltime,totalKills,logout_time,position_x,position_y,position_z,map FROM characters WHERE account=:a ORDER BY level DESC,name ASC'); $s->execute(array(':a'=>$uid)); $chars=$s->fetchAll(PDO::FETCH_ASSOC); }catch(Exception $e2){ $chars=array(); } }
    }
    if($charGuid>0){ foreach($chars as $c){ if((int)$c['guid']===$charGuid){ $selected=$c; break; } } }
    if(!$selected && count($chars)>0) $selected=$chars[0];
    if($selected){
        $world=wa_world_db_name($charDB);
        try{
            $sql='SELECT ci.slot, ii.itemEntry AS entry, COALESCE(it.name, CONCAT("Item #",ii.itemEntry)) AS name, COALESCE(it.Quality,0) AS Quality, COALESCE(it.ItemLevel,0) AS ItemLevel, COALESCE(it.InventoryType,0) AS InventoryType, COALESCE(it.class,0) AS itemClass, COALESCE(it.subclass,0) AS subclass, ii.durability, ii.count FROM character_inventory ci INNER JOIN item_instance ii ON ii.guid=ci.item LEFT JOIN '.wa_sql_ident($world).'.item_template it ON it.entry=ii.itemEntry WHERE ci.guid=:g AND ci.bag=0 AND ci.slot BETWEEN 0 AND 18 ORDER BY ci.slot ASC';
            $s=$charDB->prepare($sql); $s->execute(array(':g'=>(int)$selected['guid'])); while($r=$s->fetch(PDO::FETCH_ASSOC)){ $r['icon']=wa_item_icon_guess($r['entry'],$r['name'],$r['itemClass'],$r['InventoryType']); $equipment[(int)$r['slot']]=$r; }
        }catch(Exception $e){ try{ $s=$charDB->prepare('SELECT ci.slot, ii.itemEntry AS entry, CONCAT("Item #",ii.itemEntry) AS name, 0 AS Quality, 0 AS ItemLevel, 0 AS InventoryType, 0 AS itemClass, 0 AS subclass, ii.durability, ii.count FROM character_inventory ci INNER JOIN item_instance ii ON ii.guid=ci.item WHERE ci.guid=:g AND ci.bag=0 AND ci.slot BETWEEN 0 AND 18 ORDER BY ci.slot ASC'); $s->execute(array(':g'=>(int)$selected['guid'])); while($r=$s->fetch(PDO::FETCH_ASSOC)){ $r['icon']=wa_item_icon_guess($r['entry'],$r['name']); $equipment[(int)$r['slot']]=$r; } }catch(Exception $e2){} }
        try{ $s=$charDB->prepare('SELECT COUNT(*) FROM character_inventory WHERE guid=:g'); $s->execute(array(':g'=>(int)$selected['guid'])); $inventoryCount=(int)$s->fetchColumn(); }catch(Exception $e){}
        if(wa_table_exists($charDB,'character_achievement')){ try{ $s=$charDB->prepare('SELECT achievement,date FROM character_achievement WHERE guid=:g ORDER BY date DESC LIMIT 12'); $s->execute(array(':g'=>(int)$selected['guid'])); $achievements=$s->fetchAll(PDO::FETCH_ASSOC); $s=$charDB->prepare('SELECT COUNT(*) FROM character_achievement WHERE guid=:g'); $s->execute(array(':g'=>(int)$selected['guid'])); $totalAch=(int)$s->fetchColumn(); }catch(Exception $e){} }
        if(wa_table_exists($charDB,'character_stats')){ try{ $s=$charDB->prepare('SELECT * FROM character_stats WHERE guid=:g LIMIT 1'); $s->execute(array(':g'=>(int)$selected['guid'])); $charStats=$s->fetch(PDO::FETCH_ASSOC); }catch(Exception $e){ $charStats=false; } }
        if(wa_table_exists($charDB,'character_skills')){ try{ $s=$charDB->prepare('SELECT skill,value,max FROM character_skills WHERE guid=:g ORDER BY value DESC LIMIT 40'); $s->execute(array(':g'=>(int)$selected['guid'])); $skills=$s->fetchAll(PDO::FETCH_ASSOC); }catch(Exception $e){ $skills=array(); } }
        if(wa_table_exists($charDB,'character_reputation')){ try{ $s=$charDB->prepare('SELECT faction,standing,flags FROM character_reputation WHERE guid=:g ORDER BY standing DESC LIMIT 30'); $s->execute(array(':g'=>(int)$selected['guid'])); $reputations=$s->fetchAll(PDO::FETCH_ASSOC); }catch(Exception $e){ $reputations=array(); } }
    }
}
$display=$user && $user['displayName']!=='' ? $user['displayName'] : ($auth ? $auth['username'] : 'Unknown');
$rankName='Member'; try{ if($user){ $r=new UserRank((int)$user['rank']); $rankName=$r->string()?:'Member'; } }catch(Exception $e){}
$avatar=wa_get_avatar($user);
list($gold,$silver,$copper)=wa_money($selected ? (int)$selected['money'] : 0);
$slots=array(0=>'Head',1=>'Neck',2=>'Shoulders',14=>'Back',4=>'Chest',3=>'Shirt',18=>'Tabard',8=>'Wrists',9=>'Hands',5=>'Waist',6=>'Legs',7=>'Feet',10=>'Ring',11=>'Ring',12=>'Trinket',13=>'Trinket',15=>'Main Hand',16=>'Off Hand',17=>'Ranged');
$leftSlots=array(0,1,2,14,4,3,18,8,9); $rightSlots=array(5,6,7,10,11,12,13,15,16,17);
function wa_slot_html($slot,$equipment,$label){
    $it=isset($equipment[$slot])?$equipment[$slot]:false;
    if($it){
        $entry=(int)$it['entry']; $q=wa_quality($it['Quality']);
        $safeIcon=preg_match('/^[a-z0-9_]+$/',(string)$it['icon'])?(string)$it['icon']:'inv_misc_questionmark';
        $icon=wa_icon_url($safeIcon);
        return '<a class="wa-slot filled q-'.$q.'" href="https://www.wowhead.com/wotlk/item='.$entry.'" target="_blank" rel="item='.$entry.'" data-wowhead="item='.$entry.'" data-wh-entry="'.$entry.'"><img class="wa-item-icon" alt="" src="'.wa_h($icon).'" data-wh-icon-item="'.$entry.'" data-wh-fallback="'.wa_h($safeIcon).'" onerror="this.src=\'https://wow.zamimg.com/images/wow/icons/large/inv_misc_questionmark.jpg\'"/><span><b>'.wa_h($it['name']).'</b><em>iLvl '.(int)$it['ItemLevel'].' · '.wa_h($label).'</em></span></a>';
    }
    return '<div class="wa-slot empty"><span class="wa-empty-icon"></span><span><b>'.wa_h($label).'</b><em>Empty slot</em></span></div>';
}
?>
<style>
.wa-wrap{padding:26px 30px 42px;text-align:left;color:#d8ccb0}.wa-card{background:linear-gradient(180deg,rgba(20,14,10,.94),rgba(3,3,3,.96));border:1px solid rgba(214,156,48,.24);border-radius:12px;box-shadow:0 26px 90px rgba(0,0,0,.6);overflow:hidden}.wa-hero{position:relative;min-height:205px;padding:28px;background:radial-gradient(circle at 48% 0,rgba(173,102,22,.34),transparent 43%),linear-gradient(90deg,rgba(0,0,0,.82),rgba(0,0,0,.28)),url('./template/style/images/headers/media.jpg');background-size:cover;background-position:center}.wa-top{display:grid;grid-template-columns:112px 1fr auto;gap:20px;align-items:center}.wa-avatar{width:98px;height:98px;border-radius:12px;background-size:cover;background-position:center;border:1px solid rgba(231,177,61,.68);box-shadow:0 0 0 4px rgba(0,0,0,.45)}.wa-name{font-size:35px;color:#f2bd4b;font-family:Georgia,serif;text-shadow:0 2px 4px #000;margin:0}.wa-meta{color:#d6c49d;margin-top:5px}.wa-badges{display:flex;gap:8px;flex-wrap:wrap;margin-top:13px}.wa-badge{padding:7px 11px;border:1px solid rgba(214,156,48,.27);background:rgba(0,0,0,.42);border-radius:999px;font-size:12px;color:#dfd0ad}.wa-search{background:rgba(0,0,0,.42);border:1px solid rgba(255,255,255,.09);border-radius:9px;padding:13px}.wa-search select{background:#0d0d0d;color:#e3d5b1;border:1px solid rgba(214,156,48,.27);padding:10px;border-radius:6px;min-width:190px}.wa-inspect{display:grid;grid-template-columns:minmax(260px,1fr) minmax(300px,390px) minmax(260px,1fr);gap:15px;padding:22px}.wa-model{position:relative;min-height:535px;border:1px solid rgba(214,156,48,.27);border-radius:13px;background:#050505;display:flex;align-items:center;justify-content:center;overflow:hidden}.wa-armory-scene{position:absolute;inset:0;background-size:cover;background-position:center;background-repeat:no-repeat}.wa-model:before{content:"";position:absolute;inset:0;background:linear-gradient(180deg,rgba(0,0,0,.03),rgba(0,0,0,.20) 58%,rgba(0,0,0,.46));z-index:1}.wa-character-title{position:absolute;left:50%;bottom:22px;transform:translateX(-50%);z-index:3;display:inline-block;padding:8px 15px;border-radius:999px;background:rgba(0,0,0,.58);border:1px solid rgba(214,156,48,.32);color:#f2bd4b;text-shadow:0 2px 4px #000;font-size:17px;font-family:Georgia,serif;white-space:nowrap}.wa-slots{display:grid;gap:8px}.wa-slot{display:grid;grid-template-columns:48px 1fr;gap:10px;align-items:center;min-height:57px;padding:7px;background:rgba(0,0,0,.42);border:1px solid rgba(255,255,255,.075);border-radius:9px;text-decoration:none!important;color:#d8ccb0!important;transition:.15s}.wa-slot:hover{transform:translateY(-1px);border-color:rgba(214,156,48,.48);background:rgba(31,20,9,.78)}.wa-slot img,.wa-empty-icon,.wa-ach img{width:44px;height:44px;border-radius:7px;background:#111;border:1px solid rgba(255,255,255,.15);display:block}.wa-slot b{display:block;font-size:12px;line-height:1.2}.wa-slot em{display:block;font-style:normal;color:#92866e;font-size:10px;margin-top:3px}.wa-slot.empty{opacity:.55}.wa-empty-icon{background:linear-gradient(135deg,#111,#242424)}.q-poor b{color:#9d9d9d}.q-common b{color:#fff}.q-uncommon b{color:#1eff00}.q-rare b{color:#0070dd}.q-epic b{color:#a335ee}.q-legendary b{color:#ff8000}.q-artifact b{color:#e6cc80}.wa-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;padding:0 22px 22px}.wa-stat{background:rgba(0,0,0,.38);border:1px solid rgba(255,255,255,.075);border-radius:9px;padding:14px}.wa-stat strong{display:block;color:#f2bd4b;font-size:22px;line-height:1.15}.wa-stat span{font-size:11px;text-transform:uppercase;color:#998d74}.wa-sections{display:grid;grid-template-columns:1fr 1fr;gap:18px;padding:0 22px 24px}.wa-section{background:rgba(0,0,0,.34);border:1px solid rgba(255,255,255,.075);border-radius:10px;overflow:hidden}.wa-section h3{margin:0;padding:13px 16px;border-bottom:1px solid rgba(255,255,255,.075);color:#f2bd4b;font-family:Georgia,serif}.wa-section-body{padding:14px 16px}.wa-ach{display:grid;grid-template-columns:44px 1fr auto;gap:10px;align-items:center;padding:9px 0;border-bottom:1px solid rgba(255,255,255,.055)}.wa-ach a{color:#e6d2a8!important;text-decoration:none!important}.wa-ach a:hover{color:#f2bd4b!important}.wa-line{display:flex;justify-content:space-between;gap:12px;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.055)}.wa-progress-row{padding:9px 0;border-bottom:1px solid rgba(255,255,255,.055)}.wa-progress-top{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:7px}.wa-progress-name{display:flex;align-items:center;gap:10px;color:#f6d36b}.wa-mini-icon{width:26px;height:26px;border-radius:5px;border:1px solid rgba(255,255,255,.16);box-shadow:0 0 10px rgba(0,0,0,.45)}.wa-bar{height:8px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.08);border-radius:999px;overflow:hidden}.wa-bar span{display:block;height:100%;background:linear-gradient(90deg,#8b5a17,#f2bd4b);border-radius:999px}.wa-small{font-size:11px;color:#a99b7b}.wa-muted{color:#91856d}.wa-online{color:#55d979}.wa-offline{color:#c17465}.wa-class-warrior{color:#C79C6E}.wa-class-paladin{color:#F58CBA}.wa-class-hunter{color:#ABD473}.wa-class-rogue{color:#FFF569}.wa-class-priest{color:#fff}.wa-class-deathknight{color:#C41F3B}.wa-class-shaman{color:#0070DE}.wa-class-mage{color:#69CCF0}.wa-class-warlock{color:#9482C9}.wa-class-druid{color:#FF7D0A}@media(max-width:1050px){.wa-inspect{grid-template-columns:1fr}.wa-model{min-height:400px}.wa-stats,.wa-sections{grid-template-columns:1fr 1fr}.wa-top{grid-template-columns:1fr;text-align:center}.wa-avatar{margin:auto}}@media(max-width:650px){.wa-stats,.wa-sections{grid-template-columns:1fr}.wa-wrap{padding:16px}.wa-ach{grid-template-columns:44px 1fr}.wa-ach em{grid-column:2}}
</style>
<div class="content_holder"><div class="sub-page-title"><div id="title"><h1>Armory Profile<p></p><span></span></h1></div></div><div class="container_2 account" align="center"><div class="cont-image"><div class="container_3 account_sub_header"><div class="grad"><div class="page-title">Character Inspection</div><a href="<?php echo wa_h($config['BaseURL']); ?>/index.php?page=armory">Back to Armory</a></div></div>
<?php if(!$user && !$auth): ?><div class="container_3 account-wide" style="padding:30px;text-align:center"><h2 style="color:#d4af37">Profile not found</h2><p>This account does not exist.</p></div><?php else: ?>
<div class="wa-wrap"><div class="wa-card"><div class="wa-hero"><div class="wa-top"><div class="wa-avatar" style="background-image:url('<?php echo wa_h($avatar); ?>')"></div><div><h2 class="wa-name"><?php echo wa_h($display); ?></h2><div class="wa-meta"><?php echo $selected ? wa_h($selected['name'].' · Level '.$selected['level'].' '.wa_race($selected['race']).' '.wa_class($selected['class']).' · '.wa_faction($selected['race'])) : 'No character selected'; ?></div><div class="wa-badges"><span class="wa-badge">Account <?php echo wa_h(wa_mask($auth ? $auth['username'] : $display)); ?></span><span class="wa-badge"><?php echo wa_h($rankName); ?></span><span class="wa-badge <?php echo $selected && (int)$selected['online']===1 ? 'wa-online' : 'wa-offline'; ?>"><?php echo $selected && (int)$selected['online']===1 ? 'Online' : 'Offline'; ?></span><span class="wa-badge"><?php echo count($chars); ?> Characters</span><span class="wa-badge">Realm <?php echo isset($realms_config[$realmId]['name']) ? wa_h($realms_config[$realmId]['name']) : (int)$realmId; ?></span></div></div><form class="wa-search" method="get"><input type="hidden" name="page" value="profile"><input type="hidden" name="uid" value="<?php echo (int)$uid; ?>"><input type="hidden" name="realm" value="<?php echo (int)$realmId; ?>"><select name="char" onchange="this.form.submit()"><?php foreach($chars as $c): ?><option value="<?php echo (int)$c['guid']; ?>" <?php echo $selected && (int)$selected['guid']===(int)$c['guid']?'selected':''; ?>><?php echo wa_h($c['name'].' - Lv '.$c['level']); ?></option><?php endforeach; ?></select></form></div></div>
<?php if($selected): ?><div class="wa-inspect"><div class="wa-slots"><?php foreach($leftSlots as $sl) echo wa_slot_html($sl,$equipment,$slots[$sl]); ?></div><?php $sceneAsset=wa_armory_scene_asset($selected['race'],$selected['gender']); ?><div class="wa-model"><div class="wa-armory-scene" style="background-image:url('<?php echo wa_h($sceneAsset); ?>')"></div><div class="wa-character-title"><?php echo wa_h(wa_race($selected['race']).' '.wa_class($selected['class'])); ?></div></div><div class="wa-slots"><?php foreach($rightSlots as $sl) echo wa_slot_html($sl,$equipment,$slots[$sl]); ?></div></div>
<div class="wa-stats"><div class="wa-stat"><strong><?php echo (int)$selected['level']; ?></strong><span>Level</span></div><div class="wa-stat"><strong><?php echo (int)$totalAch; ?></strong><span>Achievements</span></div><div class="wa-stat"><strong><?php echo wa_h($gold.'g '.$silver.'s '.$copper.'c'); ?></strong><span>Character Gold</span></div><div class="wa-stat"><strong><?php echo wa_h(wa_playtime($selected['totaltime'])); ?></strong><span>Played Time</span></div><div class="wa-stat"><strong><?php echo isset($selected['totalKills'])?(int)$selected['totalKills']:0; ?></strong><span>Total Kills</span></div><div class="wa-stat"><strong><?php echo isset($selected['totalHonorPoints'])?(int)$selected['totalHonorPoints']:0; ?></strong><span>Total Honor</span></div><div class="wa-stat"><strong><?php echo isset($selected['arenaPoints'])?(int)$selected['arenaPoints']:0; ?></strong><span>Arena Points</span></div><div class="wa-stat"><strong><?php echo (int)$inventoryCount; ?></strong><span>Items Owned</span></div></div>
<?php if($charStats): ?><div class="wa-stats"><div class="wa-stat"><strong><?php echo wa_stat_value($charStats,array('maxhealth','maxHealth')); ?></strong><span>Health</span></div><div class="wa-stat"><strong><?php echo wa_stat_value($charStats,array('maxpower1','maxPower1','maxpower0','maxPower0')); ?></strong><span>Power</span></div><div class="wa-stat"><strong><?php echo wa_stat_value($charStats,'strength'); ?></strong><span>Strength</span></div><div class="wa-stat"><strong><?php echo wa_stat_value($charStats,'agility'); ?></strong><span>Agility</span></div><div class="wa-stat"><strong><?php echo wa_stat_value($charStats,'stamina'); ?></strong><span>Stamina</span></div><div class="wa-stat"><strong><?php echo wa_stat_value($charStats,'intellect'); ?></strong><span>Intellect</span></div><div class="wa-stat"><strong><?php echo wa_stat_value($charStats,'spirit'); ?></strong><span>Spirit</span></div><div class="wa-stat"><strong><?php echo wa_stat_value($charStats,'armor'); ?></strong><span>Armor</span></div><div class="wa-stat"><strong><?php echo wa_stat_value($charStats,'attackPower'); ?></strong><span>Attack Power</span></div><div class="wa-stat"><strong><?php echo wa_stat_value($charStats,'spellPower'); ?></strong><span>Spell Power</span></div><div class="wa-stat"><strong><?php echo wa_stat_value($charStats,array('critPct','critPercentage'),2); ?>%</strong><span>Crit</span></div><div class="wa-stat"><strong><?php echo wa_stat_value($charStats,'resilience'); ?></strong><span>Resilience</span></div></div><?php endif; ?>
<div class="wa-sections"><div class="wa-section"><h3>Characters</h3><div class="wa-section-body"><?php foreach($chars as $c): ?><div class="wa-line"><a href="<?php echo wa_h($config['BaseURL']); ?>/index.php?page=profile&uid=<?php echo (int)$uid; ?>&char=<?php echo (int)$c['guid']; ?>&realm=<?php echo (int)$realmId; ?>" class="wa-class-<?php echo wa_class_slug($c['class']); ?>"><?php echo wa_h($c['name']); ?></a><span>Lv <?php echo (int)$c['level']; ?> · <?php echo wa_h(wa_race($c['race']).' '.wa_class($c['class'])); ?> · <b class="<?php echo (int)$c['online']===1?'wa-online':'wa-offline'; ?>"><?php echo (int)$c['online']===1?'Online':'Offline'; ?></b></span></div><?php endforeach; ?></div></div><div class="wa-section"><h3>Recent Achievements</h3><div class="wa-section-body"><?php if(!$achievements): ?><div class="wa-muted">No achievements found yet.</div><?php else: foreach($achievements as $a): $achId=(int)$a['achievement']; $ach=wa_achievement_info($charDB,$world,$achId); ?><div class="wa-ach"><img alt="" src="https://wow.zamimg.com/images/wow/icons/large/<?php echo wa_h($ach['icon']); ?>.jpg" onerror="this.src='https://wow.zamimg.com/images/wow/icons/large/achievement_general.jpg'"><a href="https://www.wowhead.com/wotlk/achievement=<?php echo $achId; ?>" target="_blank" rel="achievement=<?php echo $achId; ?>" data-wowhead="achievement=<?php echo $achId; ?>"><?php echo wa_h($ach['name']); ?></a><em><?php echo !empty($a['date'])?date('d M Y',(int)$a['date']):'Unknown date'; ?></em></div><?php endforeach; endif; ?></div></div></div>
<div class="wa-sections"><div class="wa-section"><h3>Skills & Professions</h3><div class="wa-section-body"><?php if(!$skills): ?><div class="wa-muted">No skill data available.</div><?php else: foreach($skills as $sk): $si=wa_skill_info($charDB,$world,(int)$sk['skill']); $sv=(int)$sk['value']; $sm=max(1,(int)$sk['max']); $sp=min(100,round(($sv/$sm)*100)); ?><div class="wa-progress-row"><div class="wa-progress-top"><span class="wa-progress-name"><img class="wa-mini-icon" src="https://wow.zamimg.com/images/wow/icons/large/<?php echo wa_h($si['icon']); ?>.jpg" onerror="this.src='https://wow.zamimg.com/images/wow/icons/large/inv_misc_questionmark.jpg'"> <?php echo wa_h($si['name']); ?></span><strong><?php echo $sv; ?> / <?php echo $sm; ?></strong></div><div class="wa-bar"><span style="width:<?php echo $sp; ?>%"></span></div></div><?php endforeach; endif; ?></div></div><div class="wa-section"><h3>Reputation</h3><div class="wa-section-body"><?php if(!$reputations): ?><div class="wa-muted">No reputation data available.</div><?php else: foreach($reputations as $rep): $rn=wa_rep_name($charDB,$world,(int)$rep['faction']); $rp=wa_rep_progress((int)$rep['standing']); ?><div class="wa-progress-row"><div class="wa-progress-top"><span><?php echo wa_h($rn); ?> <em class="wa-small">· <?php echo wa_h($rp[0]); ?></em></span><strong><?php echo (int)$rp[1]; ?> / <?php echo (int)$rp[2]; ?></strong></div><div class="wa-bar"><span style="width:<?php echo (int)$rp[3]; ?>%"></span></div><div class="wa-small">Raw standing: <?php echo (int)$rep['standing']; ?></div></div><?php endforeach; endif; ?></div></div></div>
<?php else: ?><div class="wa-section" style="margin:24px"><h3>No characters</h3><div class="wa-section-body wa-muted">This account has no characters on the selected realm.</div></div><?php endif; ?></div></div><?php endif; ?></div></div></div>
<script>
var whTooltips = {colorLinks:false, iconizeLinks:false, renameLinks:false};
(function(){
  function iconUrl(icon){ return 'https://wow.zamimg.com/images/wow/icons/large/' + icon + '.jpg'; }
  function applyIcon(img, icon){
    if(!img || !icon || !/^[a-z0-9_]+$/.test(icon)) return;
    img.src = iconUrl(icon);
    img.setAttribute('data-wh-current', icon);
  }
  function repairArmoryIcons(){
    var imgs=document.querySelectorAll('img[data-wh-icon-item]');
    for(var i=0;i<imgs.length;i++){
      (function(img){
        var id=img.getAttribute('data-wh-icon-item'); if(!id) return;
        var key='warcry_wh_item_icon_'+id;
        try { var cached=localStorage.getItem(key); if(cached){ applyIcon(img,cached); return; } } catch(e){}
        var url='<?php echo wa_h($config['BaseURL']); ?>/index.php?page=profile&wa_icon_ajax=1&type=item&id='+encodeURIComponent(id)+'&t=1';
        fetch(url,{credentials:'same-origin',cache:'force-cache'}).then(function(r){return r.json();}).then(function(j){
          if(j && j.icon && j.icon !== 'inv_misc_questionmark'){ try{localStorage.setItem(key,j.icon);}catch(e){} applyIcon(img,j.icon); }
        }).catch(function(){});
      })(imgs[i]);
    }
  }
  repairArmoryIcons();
  window.addEventListener('load', repairArmoryIcons);
  setTimeout(repairArmoryIcons, 700);
  setTimeout(repairArmoryIcons, 1800);
  var s=document.createElement('script');s.src='https://wow.zamimg.com/js/tooltips.js';s.async=true;s.onload=repairArmoryIcons;document.head.appendChild(s);
})();
</script>
<?php $TPL->LoadFooter(); ?>
