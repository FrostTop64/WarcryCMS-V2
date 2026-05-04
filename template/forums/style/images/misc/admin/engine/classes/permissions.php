<?php
if (!defined('init_engine'))
{	
	header('HTTP/1.0 404 not found');
	exit;
}

//Define the permissions columns for more associative use
define('PERMISSION_GIVE_PERMISSIONS', '1');
define('PERMISSION_NEWS', '2');
define('PERMISSION_ARTICLES', '3');
define('PERMISSION_PSTORE', '4');
define('PERMISSION_MEDIA_MOVIES', '5');
define('PERMISSION_MEDIA_SREENSHOTS', '6');
define('PERMISSION_FORUMS', '7');
define('PERMISSION_FORUM_CATS', '8');
define('PERMISSION_LOGS', '9');
define('PERMISSION_PROMO_CODES', '10');
define('PERMISSION_TICKETS', '11');
define('PERMISSION_PREV_BUGTRACKER', '12');
define('PERMISSION_MAN_BUGTRACKER', '13');
define('PERMISSION_PREV_USERS', '14');
define('PERMISSION_STORE', '15');
define('PERMISSION_CHANGE_USER_RANK', '16');

//Make a little array containing the valid permissions, also helps for user permission update
$ACPValidPermissions = array
(
	PERMISSION_GIVE_PERMISSIONS,
	PERMISSION_NEWS,
	PERMISSION_ARTICLES,
	PERMISSION_PSTORE,
	PERMISSION_MEDIA_MOVIES,
	PERMISSION_MEDIA_SREENSHOTS,
	PERMISSION_FORUMS,
	PERMISSION_FORUM_CATS,
	PERMISSION_LOGS,
	PERMISSION_PROMO_CODES,
	PERMISSION_TICKETS,
	PERMISSION_PREV_BUGTRACKER,
	PERMISSION_MAN_BUGTRACKER,
	PERMISSION_PREV_USERS,
	PERMISSION_STORE,
	PERMISSION_CHANGE_USER_RANK,
);

class Permissions
{
    private $data = false;
    private $accountId = 0;
    private $rank = 0;

    public function __construct($account)
    {
        global $DB;

        $this->accountId = (int)$account;

        // Load ACP permission row if it exists.
        $res = $DB->prepare("SELECT * FROM `acp_permissions` WHERE `id` = :account LIMIT 1;");
        $res->bindParam(':account', $this->accountId, PDO::PARAM_INT);
        $res->execute();

        if ($res->rowCount() > 0)
        {
            $this->data = $res->fetch(PDO::FETCH_NUM);
        }
        unset($res);

        // Load CMS rank from account_data. Staff ranks are allowed to access ACP even without acp_permissions.
        try
        {
            $rankRes = $DB->prepare("SELECT `rank` FROM `account_data` WHERE `id` = :account LIMIT 1;");
            $rankRes->bindParam(':account', $this->accountId, PDO::PARAM_INT);
            $rankRes->execute();

            if ($rankRes->rowCount() > 0)
            {
                $rankRow = $rankRes->fetch(PDO::FETCH_ASSOC);
                $this->rank = (int)$rankRow['rank'];
            }
            unset($rankRes);
        }
        catch (Exception $e)
        {
            $this->rank = 0;
        }
    }

    public function IsStaffRank()
    {
        $staffRanks = array(
            RANK_GM,
            RANK_SENIOR_GM,
            RANK_LEAD_GM,
            RANK_CM,
            RANK_SENIOR_CM,
            RANK_LEAD_CM,
            RANK_DEV,
            RANK_LEAD_DEV,
            RANK_MANAGEMENT
        );

        return in_array((int)$this->rank, $staffRanks, true);
    }

    public function IsAllowedToUseACP()
    {
        global $ACPValidPermissions;

        // Any defined staff rank can enter the Admin Panel.
        if ($this->IsStaffRank())
            return true;

        // If the user has no ACP permission record and is not staff, deny.
        if (!$this->data)
            return false;

        // Check if the user has at least one ACP permission.
        foreach ($ACPValidPermissions as $permission)
        {
            if (isset($this->data[$permission]) && (int)$this->data[$permission] == 1)
                return true;
        }

        return false;
    }

    public function isAllowed($index)
    {
        global $ACPValidPermissions;

        if (!in_array($index, $ACPValidPermissions))
            return false;

        // Staff ranks get full ACP access.
        if ($this->IsStaffRank())
            return true;

        if (isset($this->data[$index]))
        {
            return ((int)$this->data[$index] == 1 ? true : false);
        }

        return false;
    }

    public function __destruct()
    {
        unset($this->data);
    }
}