<?php
if (!defined('init_pages'))
{
    header('HTTP/1.0 404 not found');
    exit;
}

$CORE->loggedInOrReturn();

function wc_vote_e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function wc_vote_image_src($value)
{
    $value = trim((string)$value);

    if ($value === '') {
        return '';
    }

    // Allow normal remote images and safe local CMS paths only.
    if (preg_match('#^https?://#i', $value)) {
        return $value;
    }

    if (preg_match('#^(?:template|uploads|images|img|assets)/[A-Za-z0-9_./%+\-]+$#', $value)) {
        return $value;
    }

    return '';
}

//Set the title
$TPL->SetTitle('Vote for us');
//CSS
$TPL->AddCSS('template/style/page-vote.css');
//Print the header
$TPL->LoadHeader();

?>
<div class="content_holder">

<div class="sub-page-title">
    <div id="title"><h1>Account Panel<p></p><span></span></h1></div>
  
    <div class="quick-menu">
        <a class="arrow" href="#"></a>
        <ul class="dropdown-qmenu">
            <li><a href="<?php echo $config['BaseURL']; ?>/index.php?page=store">Store</a></li>
            <li><a href="<?php echo $config['BaseURL']; ?>/index.php?page=teleporter">Teleporter</a></li>
            <li><a href="<?php echo $config['BaseURL']; ?>/index.php?page=buycoins">Buy Coins</a></li>
            <li><a href="<?php echo $config['BaseURL']; ?>/index.php?page=pstore">Premium Store</a></li>
            <li><a href="<?php echo $config['BaseURL']; ?>/index.php?page=unstuck">Unstuck</a></li>
            <li><a href="<?php echo $config['BaseURL']; ?>/index.php?page=settings">Settings & Options</a></li>
            <!--<li id="messages-ddm">
                <a href="<?php echo $config['BaseURL']; ?>/index.php?page=pm">
                    <b>55</b> <i>Private Messages</i>
                </a>
            </li>-->
        </ul>
    </div>
</div>
 
    <div class="container_2 account" align="center">
     <div class="cont-image">
    
    <?php
    if ($error = $ERRORS->DoPrint('vote'))
    {
        echo $error, '<br><br>';
    }           
    if ($error = $ERRORS->successPrint('vote'))
    {
        echo $error, '<br><br>';
    }           
    unset($error);
    ?>
   
      <div class="container_3 account_sub_header">
         <div class="grad">
            <div class="page-title">Vote</div>
            <a href="<?php echo $config['BaseURL'], '/index.php?page=account'; ?>">Back to account</a>
         </div>
      </div>
      
      <!-- VOTE -->
        <div class="vote-page">
            
            <div class="page-desc-holder">
                With every vote you will recieve <font color="#808080"><b>2 silver</b></font> coins. <br/>
                You can spend your coins for amazing stuff on our website.
            </div>
            
            <div class="container_3 account-wide" align="center">
             
                    <ul class="vote-sites-cont">
                        
                        <?php
                            
                            $VoteSites = new VoteSitesData();
                            
                            foreach ($VoteSites->data as $id => $data)
                            {
                                $id = (int)$id;
                                $siteName = isset($data['name']) ? $data['name'] : 'Vote Site';
                                $siteUrl = isset($data['url']) ? trim((string)$data['url']) : '#';
                                $siteImg = wc_vote_image_src(isset($data['img']) ? $data['img'] : '');
                                $imageMarkup = $siteImg !== ''
                                    ? '<span class="vote-site-image"><img src="'.wc_vote_e($siteImg).'" alt="'.wc_vote_e($siteName).'" loading="lazy" onerror="this.style.display=\'none\';this.parentNode.className+=\' vote-site-image-empty\';"></span>'
                                    : '<span class="vote-site-image vote-site-image-empty"></span>';

                                $cooldown = $CURUSER->getCooldown('votingsite'.$id);
                                
                                //if the site is availible for voting
                                if (time() > $cooldown)
                                {
                                    echo '
                                    <li>
                                      <a href="', $config['BaseURL'], '/execute.php?take=vote&amp;site=', $id, '" onclick="window.open(', wc_vote_e(json_encode($siteUrl)), ', \'_newtab\'); return true;">
                                        ', $imageMarkup, '
                                        <p>You can vote now!</p>
                                      </a>
                                    </li>';
                                }
                                else
                                {
                                    //convert the cooldown to minutes and stuff
                                    $cooldownArr = $CORE->convertCooldown($cooldown);
                                    
                                    echo '
                                    <li class="not-active">
                                      <a href="', wc_vote_e($siteUrl), '" rel="noopener">
                                        ', $imageMarkup, '
                                        <p>';
                                        
                                        if ($cooldownArr['hours'] > 0)
                                        {
                                            echo (int)$cooldownArr['hours'], ' hours until vote!';
                                        }
                                        else if ($cooldownArr['minutes'] > 0)
                                        {
                                            echo (int)$cooldownArr['minutes'], ' minutes until vote!';
                                        }
                                        else if ($cooldownArr['seconds'] > 0)
                                        {
                                            echo (int)$cooldownArr['seconds'], ' seconds until vote!';
                                        }
                                        
                                        echo ' 
                                        </p>
                                      </a>
                                    </li>';
                                    
                                    unset($cooldownArr);
                                }
                                unset($cooldown, $siteName, $siteUrl, $siteImg, $imageMarkup);
                            }
                            
                            unset($VoteSites, $data, $id);
                        ?>
                                                    
                    </ul>
             
            </div>
            
        </div>
      <!-- VOTE.End -->
    
     </div>
    </div>
 
</div>

</div>

<?php

$TPL->LoadFooter();

?>
