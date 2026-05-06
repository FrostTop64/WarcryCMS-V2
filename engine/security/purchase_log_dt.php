<?php
// Shared DataTables renderer for purchase_log AJAX endpoints (PullLogs*Ajax.php).
// Each Pull*Ajax script just calls warcry_render_purchase_log_dt($DB, $CURUSER, $sSource).
// Output format matches the legacy per-file scripts so the existing JS/HTML keeps working.

if (!function_exists('warcry_render_purchase_log_dt')) {
    function warcry_render_purchase_log_dt(PDO $db, $curUser, $source)
    {
        if (!$curUser->isOnline()) {
            echo json_encode(array('error' => 'You must be logged in.'));
            return;
        }

        if (!$curUser->getPermissions()->isAllowed(PERMISSION_LOGS)) {
            echo json_encode(array('error' => 'You dont have the required permissions.'));
            return;
        }

        $aColumns = array('id', 'text', 'account', 'time', 'status');
        $sIndexColumn = 'id';
        $sTable = 'purchase_log';

        list($rResult, $iFilteredTotal, $iTotal) = warcry_dt_query(
            $db, $aColumns, $sTable, $sIndexColumn, array('source' => (string)$source)
        );

        $output = array(
            'sEcho' => isset($_GET['sEcho']) ? intval($_GET['sEcho']) : 0,
            'iTotalRecords' => $iTotal,
            'iTotalDisplayRecords' => $iFilteredTotal,
            'aaData' => array(),
        );

        $accountStmt = $db->prepare('SELECT displayName FROM `account_data` WHERE `id` = :id LIMIT 1;');

        while ($aRow = $rResult->fetch()) {
            $textArr = explode('| Update:', (string)$aRow['text']);
            $text = '';
            foreach ($textArr as $val) {
                $text .= $val . '<br />';
            }

            $accountStmt->bindValue(':id', $aRow['account'], PDO::PARAM_INT);
            $accountStmt->execute();
            if ($accountStmt->rowCount() > 0) {
                $accRow = $accountStmt->fetch();
                $aRow['account'] = '<a href="index.php?page=user-preview&uid=' . $aRow['account'] . '">'
                    . $accRow['displayName'] . '</a> [' . $aRow['account'] . ']';
            }

            $row = array();
            $row[0] = $aRow['id'];
            $row[1] = '
				<div class="datatable-expander" style="position: relative;">
					<p>' . $text . '</p>
					<span style="position: absolute; top: 1px; right: 0px;">
						<a href="#" onclick="return Toggle(this);">Open</a>
					</span>
				</div>';
            $row[2] = $aRow['account'];
            $row[3] = $aRow['time'];
            $row[4] = ucfirst((string)$aRow['status']);

            $output['aaData'][] = $row;
        }

        echo json_encode($output);
    }
}
