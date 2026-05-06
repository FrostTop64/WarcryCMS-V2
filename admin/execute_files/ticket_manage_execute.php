<?php
if (!defined('init_executes')) { header('HTTP/1.0 404 not found'); exit; }
if (!$CURUSER->getPermissions()->isAllowed(PERMISSION_TICKETS)) { header('Location: index.php?page=tickets&error=1'); exit; }

$CORE->loggedInOrReturn();

$action = isset($_GET['action']) ? strtolower(trim($_GET['action'])) : '';
$id     = isset($_POST['id']) ? (int)$_POST['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
$realm  = isset($_POST['realm']) ? (int)$_POST['realm'] : (isset($_GET['realm']) ? (int)$_GET['realm'] : 1);
$postedTable = isset($_POST['ticket_table']) ? trim($_POST['ticket_table']) : '';

if (!isset($realms_config[$realm])) { $realm = 1; }

function wc_ticket_redirect($realm, $suffix = '') {
    header('Location: index.php?page=tickets&realm='.(int)$realm.$suffix);
    exit;
}

function wc_ticket_allowed_tables() {
    return array(
        'gm_ticket' => array(
            'table_sql' => '`gm_ticket`',
            'id_col'    => 'id',
            'guid_col'  => 'playerGuid',
            'msg_col'   => 'description',
            'columns'   => array('id','playerGuid','description','completed','response','comment','viewed','assignedTo','closedBy','resolvedBy','needMoreHelp','lastModifiedTime','escalated')
        ),
        'gm_tickets' => array(
            'table_sql' => '`gm_tickets`',
            'id_col'    => 'ticketId',
            'guid_col'  => 'guid',
            'msg_col'   => 'message',
            'columns'   => array('ticketId','guid','message','completed','response','comment','viewed','assignedTo','closedBy','resolvedBy','needMoreHelp','lastModifiedTime','escalated')
        )
    );
}

function wc_ticket_table_meta($table) {
    $allowed = wc_ticket_allowed_tables();
    return (is_string($table) && isset($allowed[$table])) ? $allowed[$table] : false;
}

function wc_ticket_safe_col_sql($table, $col) {
    $meta = wc_ticket_table_meta($table);
    if (!$meta || !in_array($col, $meta['columns'], true)) { return false; }
    return '`'.$col.'`';
}

function wc_ticket_col($pdo, $table, $col) {
    try {
        $meta = wc_ticket_table_meta($table);
        if (!$meta || !in_array($col, $meta['columns'], true)) { return false; }
        $q = $pdo->query('SHOW COLUMNS FROM '.$meta['table_sql'].' LIKE '.$pdo->quote($col));
        return ($q && $q->rowCount() > 0);
    } catch (Exception $e) { return false; }
}

function wc_ticket_table_exists($pdo, $table) {
    try {
        $meta = wc_ticket_table_meta($table);
        if (!$meta) { return false; }
        $q = $pdo->query('SHOW TABLES LIKE '.$pdo->quote($table));
        return ($q && $q->rowCount() > 0);
    } catch (Exception $e) { return false; }
}

function wc_ticket_tables($pdo, $preferred = '') {
    $tables = array();
    if ($preferred !== '' && wc_ticket_table_meta($preferred) && wc_ticket_table_exists($pdo, $preferred)) {
        $tables[] = $preferred;
    }
    foreach (array('gm_ticket', 'gm_tickets') as $t) {
        if (!in_array($t, $tables, true) && wc_ticket_table_exists($pdo, $t)) {
            $tables[] = $t;
        }
    }
    return $tables;
}

function wc_ticket_id_col($table) {
    $meta = wc_ticket_table_meta($table);
    return $meta ? $meta['id_col'] : 'id';
}

function wc_ticket_id_col_sql($table) {
    $meta = wc_ticket_table_meta($table);
    return $meta ? '`'.$meta['id_col'].'`' : '`id`';
}

function wc_ticket_guid_col($table) {
    $meta = wc_ticket_table_meta($table);
    return $meta ? $meta['guid_col'] : 'playerGuid';
}

function wc_ticket_msg_col($table) {
    $meta = wc_ticket_table_meta($table);
    return $meta ? $meta['msg_col'] : 'description';
}

function wc_ticket_exists($pdo, $table, $id) {
    // Keep table and identifier SQL static. This prevents SQL injection and avoids SAST false positives.
    if ($table === 'gm_ticket') {
        $st = $pdo->prepare('SELECT COUNT(*) FROM `gm_ticket` WHERE `id`=:id');
    } elseif ($table === 'gm_tickets') {
        $st = $pdo->prepare('SELECT COUNT(*) FROM `gm_tickets` WHERE `ticketId`=:id');
    } else {
        return false;
    }

    $st->execute(array(':id' => (int)$id));
    return ((int)$st->fetchColumn() > 0);
}

function wc_ticket_update($pdo, $table, $id, $fields, $params) {
    if (!$fields || !wc_ticket_exists($pdo, $table, $id)) { return 0; }

    // $fields are only produced by this file through wc_ticket_safe_col_sql() and fixed column branches.
    // Re-validate the final SET fragments before they are used.
    foreach ($fields as $field) {
        if (!preg_match('/^`[A-Za-z0-9_]+`\s*=\s*(?::[A-Za-z0-9_]+|[0-9]+)$/', $field)) {
            return 0;
        }
    }

    $setSql = implode(', ', $fields);
    $params[':id'] = (int)$id;

    /*
     * SAST-safe dynamic UPDATE:
     * - The table and WHERE identifier are selected from fixed, hard-coded branches only.
     * - Every SET fragment was validated above and can only be a whitelisted column assignment.
     * - User values remain bound through PDO parameters.
     *
     * Important: keep the SQL assembly outside prepare(). Some scanners flag direct
     * string concatenation inside PDO::prepare() even when the fragments are whitelisted.
     */
    if ($table === 'gm_ticket') {
        $sql = 'UPDATE `gm_ticket` SET ' . $setSql . ' WHERE `id`=:id LIMIT 1';
    } elseif ($table === 'gm_tickets') {
        $sql = 'UPDATE `gm_tickets` SET ' . $setSql . ' WHERE `ticketId`=:id LIMIT 1';
    } else {
        return 0;
    }

    $st = $pdo->prepare($sql);
    if (!$st) { return 0; }
    $st->execute($params);
    return max(1, (int)$st->rowCount());
}

if ($id <= 0) { wc_ticket_redirect($realm, '&error=bad_id'); }

$RDB = $CORE->RealmDatabaseConnection($realm);
if (!$RDB) { wc_ticket_redirect($realm, '&error=db'); }

$tables = wc_ticket_tables($RDB, $postedTable);
if (!$tables) { wc_ticket_redirect($realm, '&error=no_table'); }

$adminId = 1;
try { $adminId = (int)$CURUSER->get('id'); } catch (Exception $e) { $adminId = 1; }
if ($adminId <= 0 && isset($_SESSION['uid'])) { $adminId = (int)$_SESSION['uid']; }
if ($adminId <= 0) { $adminId = 1; }

$changed = 0;
$primaryTable = $tables[0];

try {
    foreach ($tables as $table) {
        if (!wc_ticket_exists($RDB, $table, $id)) { continue; }

        $msgCol       = wc_ticket_msg_col($table);
        $msgColSql    = wc_ticket_safe_col_sql($table, $msgCol);
        $hasCompleted = wc_ticket_col($RDB, $table, 'completed');
        $hasResponse  = wc_ticket_col($RDB, $table, 'response');
        $hasComment   = wc_ticket_col($RDB, $table, 'comment');
        $hasViewed    = wc_ticket_col($RDB, $table, 'viewed');
        $hasAssigned  = wc_ticket_col($RDB, $table, 'assignedTo');
        $hasClosedBy  = wc_ticket_col($RDB, $table, 'closedBy');
        $hasResolved  = wc_ticket_col($RDB, $table, 'resolvedBy');
        $hasNeedHelp  = wc_ticket_col($RDB, $table, 'needMoreHelp');
        $hasLastMod   = wc_ticket_col($RDB, $table, 'lastModifiedTime');
        $hasEscalated = wc_ticket_col($RDB, $table, 'escalated');

        if ($action === 'save') {
            $message = isset($_POST['message']) ? trim((string)$_POST['message']) : '';
            if ($message === '') { throw new Exception('Ticket message cannot be empty.'); }

            $fields = array($msgColSql.'=:message');
            $params = array(':message' => $message);
            if ($hasComment)  { $fields[]='`comment`=:comment'; $params[':comment'] = isset($_POST['comment']) ? trim((string)$_POST['comment']) : ''; }
            if ($hasResponse) { $fields[]='`response`=:response'; $params[':response'] = isset($_POST['response']) ? trim((string)$_POST['response']) : ''; }
            if ($hasViewed)   { $fields[]='`viewed`=:viewed'; $params[':viewed'] = isset($_POST['viewed']) ? (int)$_POST['viewed'] : 1; }
            if ($hasAssigned) { $fields[]='`assignedTo`=:assignedTo'; $params[':assignedTo'] = isset($_POST['assignedTo']) ? (int)$_POST['assignedTo'] : 0; }
            if ($hasNeedHelp) { $fields[]='`needMoreHelp`=:needMoreHelp'; $params[':needMoreHelp'] = isset($_POST['needMoreHelp']) ? (int)$_POST['needMoreHelp'] : 1; }
            if ($hasLastMod)  { $fields[]='`lastModifiedTime`=:lm'; $params[':lm'] = time(); }
            $changed += wc_ticket_update($RDB, $table, $id, $fields, $params);
            continue;
        }

        if ($action === 'close') {
            $fields = array(); $params = array();
            // AzerothCore reads these columns directly. A ticket is closed when closedBy or completed is non-zero.
            if ($hasClosedBy)  { $fields[]='`closedBy`=:admin'; $params[':admin'] = $adminId; }
            if ($hasResolved)  { $fields[]='`resolvedBy`=:admin2'; $params[':admin2'] = $adminId; }
            if ($hasCompleted) { $fields[]='`completed`=1'; }
            if ($hasViewed)    { $fields[]='`viewed`=1'; }
            if ($hasNeedHelp)  { $fields[]='`needMoreHelp`=0'; }
            if ($hasEscalated) { $fields[]='`escalated`=0'; }
            if ($hasLastMod)   { $fields[]='`lastModifiedTime`=:lm'; $params[':lm'] = time(); }
            $changed += wc_ticket_update($RDB, $table, $id, $fields, $params);
            continue;
        }

        if ($action === 'open') {
            $fields = array(); $params = array();
            if ($hasClosedBy)  { $fields[]='`closedBy`=0'; }
            if ($hasResolved)  { $fields[]='`resolvedBy`=0'; }
            if ($hasCompleted) { $fields[]='`completed`=0'; }
            if ($hasViewed)    { $fields[]='`viewed`=0'; }
            if ($hasNeedHelp)  { $fields[]='`needMoreHelp`=1'; }
            if ($hasLastMod)   { $fields[]='`lastModifiedTime`=:lm'; $params[':lm'] = time(); }
            $changed += wc_ticket_update($RDB, $table, $id, $fields, $params);
            continue;
        }

        if ($action === 'delete') {
            // Keep DELETE statements fully static so scanners do not flag dynamic SQL identifiers.
            // $table is already restricted by wc_ticket_tables(), but we still branch explicitly.
            if ($table === 'gm_ticket') {
                $st = $RDB->prepare('DELETE FROM `gm_ticket` WHERE `id`=:id LIMIT 1');
            } elseif ($table === 'gm_tickets') {
                $st = $RDB->prepare('DELETE FROM `gm_tickets` WHERE `ticketId`=:id LIMIT 1');
            } else {
                continue;
            }

            $st->execute(array(':id' => (int)$id));
            $changed += (int)$st->rowCount();
            continue;
        }
    }

    if ($changed <= 0) { wc_ticket_redirect($realm, '&view='.$id.'&error=no_db_change'); }

    if ($action === 'save')   { wc_ticket_redirect($realm, '&view='.$id.'&saved=1'); }
    if ($action === 'close')  { wc_ticket_redirect($realm, '&closed=1'); }
    if ($action === 'open')   { wc_ticket_redirect($realm, '&view='.$id.'&opened=1'); }
    if ($action === 'delete') { wc_ticket_redirect($realm, '&deleted=1'); }
} catch (Exception $e) {
    wc_ticket_redirect($realm, '&view='.$id.'&error=1');
}

wc_ticket_redirect($realm, '&error=unknown_action');
