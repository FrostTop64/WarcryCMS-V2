<?php
if (!defined('init_pages')) { header('HTTP/1.0 404 not found'); exit; }

require_once $config['RootPath'].'/engine/helpers/account_modules.php';
require_once $config['RootPath'].'/engine/helpers/lottery.php';
warcry_lottery_ensure_tables();

function ham($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

if ($success = $ERRORS->successPrint(array('account_modules'))) { echo $success; }
if ($error = $ERRORS->DoPrint('account_modules')) { echo $error; }

$d = warcry_account_modules_defaults();
foreach ($d as $k => $v) { $d[$k] = warcry_account_module_get($k, $v); }

$allowed_categories = array('shop', 'lottery', 'level-rewards', 'character-services', 'account-services', 'misc');
$category = isset($_GET['cat']) ? strtolower(trim($_GET['cat'])) : 'shop';
if (!in_array($category, $allowed_categories, true)) { $category = 'shop'; }

$tabs = array(
    'shop' => array(
        'label' => 'Shop',
        'icon'  => '💰',
        'desc'  => 'Gold, shop services and economy modules.'
    ),
    'lottery' => array(
        'label' => 'Lottery',
        'icon'  => '🎟️',
        'desc'  => 'Scratch ticket cost, default rewards and item prize pool.'
    ),
    'level-rewards' => array(
        'label' => 'Level Rewards',
        'icon'  => '⭐',
        'desc'  => 'Character level packages, reward gold, bags and costs.'
    ),
    'character-services' => array(
        'label' => 'Character Services',
        'icon'  => '🧙',
        'desc'  => 'Faction change, re-customization and character options.'
    ),
    'account-services' => array(
        'label' => 'Account Services',
        'icon'  => '👤',
        'desc'  => 'Account related modules reserved for future services.'
    ),
    'misc' => array(
        'label' => 'Misc',
        'icon'  => '⚙️',
        'desc'  => 'Other modules and future settings.'
    )
);
?>
<nav id="secondary" class="disable-tabbing">
    <ul>
        <li class="current"><a href="index.php?page=account-modules">Account Modules</a></li>
    </ul>
</nav>

<section id="content">
    <div class="tab" id="maintab">
        <h2>Account Modules Manager</h2>
        <div class="notice">Manage Account panel services by category. This keeps the page clean even when more modules are added later.</div>

        <style>
            .warcry-module-tabs {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                gap: 10px;
                margin: 18px 0 22px 0;
            }
            .warcry-module-tabs a {
                display: block;
                padding: 14px 16px;
                border-radius: 8px;
                text-decoration: none;
                background: rgba(255,255,255,0.045);
                border: 1px solid rgba(255,255,255,0.08);
                color: #cfcfcf;
                transition: all .15s ease-in-out;
            }
            .warcry-module-tabs a:hover,
            .warcry-module-tabs a.active {
                background: rgba(180,130,60,0.16);
                border-color: rgba(218,170,85,0.45);
                color: #fff;
            }
            .warcry-module-tabs strong {
                display: block;
                font-size: 14px;
                margin-bottom: 4px;
            }
            .warcry-module-tabs small {
                display: block;
                line-height: 1.35;
                opacity: .75;
            }
            .warcry-category-header {
                margin: 0 0 18px 0;
                padding: 14px 16px;
                border-radius: 8px;
                background: rgba(0,0,0,0.16);
                border-left: 3px solid #d5a24a;
            }
            .warcry-category-header h3 {
                margin: 0 0 4px 0;
            }
            .warcry-category-header p {
                margin: 0;
                opacity: .78;
            }
            .warcry-module-card {
                margin-bottom: 20px;
                padding: 16px;
                border-radius: 8px;
                background: rgba(255,255,255,0.035);
                border: 1px solid rgba(255,255,255,0.075);
            }
            .warcry-module-card h3 {
                margin-top: 0;
                padding-bottom: 10px;
                border-bottom: 1px solid rgba(255,255,255,0.08);
            }
            .warcry-level-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: 14px;
                margin-top: 12px;
            }
            .warcry-level-package {
                padding: 14px;
                border-radius: 10px;
                background: rgba(0,0,0,0.20);
                border: 1px solid rgba(255,255,255,0.075);
                box-shadow: inset 0 1px 0 rgba(255,255,255,0.04);
            }
            .warcry-level-package h4 {
                margin: 0 0 12px 0;
                padding-bottom: 9px;
                border-bottom: 1px solid rgba(218,50,65,0.45);
                color: #fff;
            }
            .warcry-mini-field {
                margin-bottom: 11px;
            }
            .warcry-mini-field label {
                display: block;
                float: none !important;
                width: auto !important;
                margin: 0 0 5px 0 !important;
                font-size: 12px;
                opacity: .86;
            }
            .warcry-mini-field input {
                width: 100% !important;
                box-sizing: border-box;
            }
            .warcry-two-cols {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 10px;
            }
            .warcry-three-cols {
                display: grid;
                grid-template-columns: 1fr 1fr 1fr;
                gap: 10px;
            }
            @media (max-width: 760px) {
                .warcry-two-cols, .warcry-three-cols { grid-template-columns: 1fr; }
            }
            .warcry-empty-category {
                padding: 22px;
                border-radius: 8px;
                text-align: center;
                background: rgba(255,255,255,0.035);
                border: 1px dashed rgba(255,255,255,0.16);
                color: #cfcfcf;
            }
        </style>

        <div class="admin-card">
            <div class="warcry-module-tabs">
                <?php foreach ($tabs as $key => $tab): ?>
                    <a href="index.php?page=account-modules&amp;cat=<?php echo ham($key); ?>" class="<?php echo $category === $key ? 'active' : ''; ?>">
                        <strong><?php echo ham($tab['icon'].' '.$tab['label']); ?></strong>
                        <small><?php echo ham($tab['desc']); ?></small>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="warcry-category-header">
                <h3><?php echo ham($tabs[$category]['icon'].' '.$tabs[$category]['label']); ?></h3>
                <p><?php echo ham($tabs[$category]['desc']); ?></p>
            </div>

            <form method="post" action="execute.php?take=account_modules" class="form pro-form">
                <input type="hidden" name="return_cat" value="<?php echo ham($category); ?>">
                <?php if ($category === 'shop'): ?>
                    <div class="warcry-module-card">
                        <h3>Purchase In-Game Gold</h3>
                        <section>
                            <label>Enabled</label>
                            <div class="field-inline">
                                <select name="purchase_gold_enabled">
                                    <option value="1"<?php echo $d['purchase_gold_enabled']=='1'?' selected':''; ?>>Enabled</option>
                                    <option value="0"<?php echo $d['purchase_gold_enabled']=='0'?' selected':''; ?>>Disabled</option>
                                </select>
                            </div>
                        </section>
                        <section>
                            <label>Title</label>
                            <div class="field-stack"><input type="text" name="purchase_gold_title" value="<?php echo ham($d['purchase_gold_title']); ?>"></div>
                        </section>
                        <section>
                            <label>Description</label>
                            <div class="field-stack"><textarea name="purchase_gold_description" rows="3"><?php echo ham($d['purchase_gold_description']); ?></textarea></div>
                        </section>
                        <section>
                            <label>Price Rate <small>Gold coins charged per unit.</small></label>
                            <div class="field-inline"><input type="text" name="purchase_gold_rate" value="<?php echo ham($d['purchase_gold_rate']); ?>"> <span class="badge">Gold Coins</span></div>
                        </section>
                        <section>
                            <label>Gold Unit <small>Example: 1000 means 1 price rate per 1000 gold.</small></label>
                            <div class="field-stack"><input type="text" name="purchase_gold_unit" value="<?php echo ham($d['purchase_gold_unit']); ?>"></div>
                        </section>
                        <section>
                            <label>Min / Max Gold</label>
                            <div class="field-inline">
                                <input type="text" name="purchase_gold_min" value="<?php echo ham($d['purchase_gold_min']); ?>">
                                <input type="text" name="purchase_gold_max" value="<?php echo ham($d['purchase_gold_max']); ?>">
                            </div>
                        </section>
                    </div>


                <?php elseif ($category === 'lottery'): ?>
                    <div class="warcry-module-card">
                        <h3>Lottery Settings</h3>
                        <section>
                            <label>Enabled</label>
                            <div class="field-inline">
                                <select name="lottery_enabled">
                                    <option value="1"<?php echo $d['lottery_enabled']=='1'?' selected':''; ?>>Enabled</option>
                                    <option value="0"<?php echo $d['lottery_enabled']=='0'?' selected':''; ?>>Disabled</option>
                                </select>
                            </div>
                        </section>
                        <section>
                            <label>Title</label>
                            <div class="field-stack"><input type="text" name="lottery_title" value="<?php echo ham($d['lottery_title']); ?>"></div>
                        </section>
                        <section>
                            <label>Description</label>
                            <div class="field-stack"><textarea name="lottery_description" rows="3"><?php echo ham($d['lottery_description']); ?></textarea></div>
                        </section>
                        <section>
                            <label>Ticket Price</label>
                            <div class="field-inline"><input type="text" name="lottery_ticket_price" value="<?php echo ham($d['lottery_ticket_price']); ?>"> <span class="badge">Gold Coins</span></div>
                        </section>
                    </div>

                    <div class="warcry-module-card">
                        <h3>Lottery Prize Pool</h3>
                        <div class="notice">Weight controls chance. Higher weight = more common. Icon is the Wowhead icon name without .jpg.</div>
                        <table class="nice-table" style="width:100%;margin:12px 0;">
                            <thead><tr><th>Icon</th><th>Item ID</th><th>Name</th><th>Qty</th><th>Weight</th><th>Enabled</th><th>Actions</th></tr></thead>
                            <tbody>
                            <?php foreach (warcry_lottery_prizes(false) as $prize): ?>
                                <tr>
                                    <form method="post" action="execute.php?take=lottery_prize">
                                    <td><img src="<?php echo warcry_lottery_icon_url($prize['icon'], 'medium'); ?>" width="32" height="32" style="border:1px solid #6f5528;"><br><input type="text" name="icon" value="<?php echo ham($prize['icon']); ?>" style="width:120px"></td>
                                    <td><input type="text" name="item_entry" value="<?php echo (int)$prize['item_entry']; ?>" style="width:70px"></td>
                                    <td><input type="text" name="item_name" value="<?php echo ham($prize['item_name']); ?>" style="width:230px"></td>
                                    <td><input type="text" name="quantity" value="<?php echo (int)$prize['quantity']; ?>" style="width:50px"></td>
                                    <td><input type="text" name="weight" value="<?php echo (int)$prize['weight']; ?>" style="width:55px"></td>
                                    <td><select name="enabled"><option value="1"<?php echo $prize['enabled']?' selected':''; ?>>Yes</option><option value="0"<?php echo !$prize['enabled']?' selected':''; ?>>No</option></select></td>
                                    <td><input type="hidden" name="id" value="<?php echo (int)$prize['id']; ?>"><button type="submit" name="action" value="save" class="button">Save</button> <button type="submit" name="action" value="delete" class="button" onclick="return confirm('Delete this lottery prize?');">Delete</button></td>
                                    </form>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <form method="post" action="execute.php?take=lottery_prize">
                                <td><input type="text" name="icon" value="inv_misc_questionmark" style="width:120px"></td>
                                <td><input type="text" name="item_entry" value="" style="width:70px"></td>
                                <td><input type="text" name="item_name" value="" style="width:230px" placeholder="New prize name"></td>
                                <td><input type="text" name="quantity" value="1" style="width:50px"></td>
                                <td><input type="text" name="weight" value="10" style="width:55px"></td>
                                <td><select name="enabled"><option value="1">Yes</option><option value="0">No</option></select></td>
                                <td><button type="submit" name="action" value="add" class="button primary">Add Prize</button></td>
                                </form>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                <?php elseif ($category === 'level-rewards'): ?>
                    <div class="warcry-module-card">
                        <h3>Character Level Up</h3>
                        <section>
                            <label>Enabled</label>
                            <div class="field-inline">
                                <select name="levels_enabled">
                                    <option value="1"<?php echo $d['levels_enabled']=='1'?' selected':''; ?>>Enabled</option>
                                    <option value="0"<?php echo $d['levels_enabled']=='0'?' selected':''; ?>>Disabled</option>
                                </select>
                            </div>
                        </section>
                        <section>
                            <label>Title</label>
                            <div class="field-stack"><input type="text" name="levels_title" value="<?php echo ham($d['levels_title']); ?>"></div>
                        </section>
                        <section>
                            <label>Description</label>
                            <div class="field-stack"><textarea name="levels_description" rows="3"><?php echo ham($d['levels_description']); ?></textarea></div>
                        </section>
                    </div>

                    <div class="warcry-module-card">
                        <h3>Level Packages</h3>
                        <div class="notice">Edit each package cleanly without mixing it with the shop gold module.</div>
                        <div class="warcry-level-grid">
                            <?php for ($i = 1; $i <= 3; $i++): ?>
                                <div class="warcry-level-package">
                                    <h4>Package <?php echo (int)$i; ?></h4>

                                    <div class="warcry-two-cols">
                                        <div class="warcry-mini-field">
                                            <label>Target Level</label>
                                            <input type="text" name="levels_<?php echo (int)$i; ?>_level" value="<?php echo ham($d['levels_'.$i.'_level']); ?>" placeholder="60">
                                        </div>
                                        <div class="warcry-mini-field">
                                            <label>Cost Gold Coins</label>
                                            <input type="text" name="levels_<?php echo (int)$i; ?>_price" value="<?php echo ham($d['levels_'.$i.'_price']); ?>" placeholder="4">
                                        </div>
                                    </div>

                                    <div class="warcry-mini-field">
                                        <label>Reward Gold</label>
                                        <input type="text" name="levels_<?php echo (int)$i; ?>_reward_gold" value="<?php echo ham($d['levels_'.$i.'_reward_gold']); ?>" placeholder="2000">
                                    </div>

                                    <div class="warcry-three-cols">
                                        <div class="warcry-mini-field">
                                            <label>Bags Amount</label>
                                            <input type="text" name="levels_<?php echo (int)$i; ?>_bags" value="<?php echo ham($d['levels_'.$i.'_bags']); ?>" placeholder="4">
                                        </div>
                                        <div class="warcry-mini-field">
                                            <label>Bag Item ID</label>
                                            <input type="text" name="levels_<?php echo (int)$i; ?>_bag_item" value="<?php echo ham($d['levels_'.$i.'_bag_item']); ?>" placeholder="14155">
                                        </div>
                                        <div class="warcry-mini-field">
                                            <label>Bag Slots</label>
                                            <input type="text" name="levels_<?php echo (int)$i; ?>_bag_slots" value="<?php echo ham($d['levels_'.$i.'_bag_slots']); ?>" placeholder="16">
                                        </div>
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                <?php elseif ($category === 'character-services'): ?>
                    <div class="warcry-module-card">
                        <h3>Faction Change</h3>
                        <section>
                            <label>Enabled</label>
                            <div class="field-inline">
                                <select name="faction_enabled">
                                    <option value="1"<?php echo $d['faction_enabled']=='1'?' selected':''; ?>>Enabled</option>
                                    <option value="0"<?php echo $d['faction_enabled']=='0'?' selected':''; ?>>Disabled</option>
                                </select>
                            </div>
                        </section>
                        <section>
                            <label>Title</label>
                            <div class="field-stack"><input type="text" name="faction_title" value="<?php echo ham($d['faction_title']); ?>"></div>
                        </section>
                        <section>
                            <label>Description</label>
                            <div class="field-stack"><textarea name="faction_description" rows="3"><?php echo ham($d['faction_description']); ?></textarea></div>
                        </section>
                        <section>
                            <label>Price</label>
                            <div class="field-inline"><input type="text" name="faction_price" value="<?php echo ham($d['faction_price']); ?>"> <span class="badge">Gold Coins</span></div>
                        </section>
                    </div>

                    <div class="warcry-module-card">
                        <h3>Character Re-customization</h3>
                        <section>
                            <label>Enabled</label>
                            <div class="field-inline">
                                <select name="recustomization_enabled">
                                    <option value="1"<?php echo $d['recustomization_enabled']=='1'?' selected':''; ?>>Enabled</option>
                                    <option value="0"<?php echo $d['recustomization_enabled']=='0'?' selected':''; ?>>Disabled</option>
                                </select>
                            </div>
                        </section>
                        <section>
                            <label>Title</label>
                            <div class="field-stack"><input type="text" name="recustomization_title" value="<?php echo ham($d['recustomization_title']); ?>"></div>
                        </section>
                        <section>
                            <label>Description</label>
                            <div class="field-stack"><textarea name="recustomization_description" rows="3"><?php echo ham($d['recustomization_description']); ?></textarea></div>
                        </section>
                        <section>
                            <label>Price</label>
                            <div class="field-inline"><input type="text" name="recustomization_price" value="<?php echo ham($d['recustomization_price']); ?>"> <span class="badge">Gold Coins</span></div>
                        </section>
                    </div>
                <?php elseif ($category === 'account-services'): ?>
                    <div class="warcry-empty-category">
                        No account service module yet. This category is ready for future modules like rename account, unstuck, VIP upgrades or account tools.
                    </div>
                <?php else: ?>
                    <div class="warcry-empty-category">
                        No misc module yet. This category is ready for future custom services.
                    </div>
                <?php endif; ?>

                <section>
                    <label></label>
                    <div><button type="submit" class="button primary">Save Modules</button></div>
                </section>
            </form>
        </div>
    </div>
</section>
