<?php

    /*
    *  Copyright (c) Codiad & daeks, distributed
    *  as-is and without warranty under the MIT License. See 
    *  [root]/license.txt for more. This information must remain intact.
    */

    require_once('../../common.php');
    
    //////////////////////////////////////////////////////////////////
    // Verify Session or Key
    //////////////////////////////////////////////////////////////////
    
    checkSession();

    switch($_GET['action']){
            
        //////////////////////////////////////////////////////////////////////
        // Update
        //////////////////////////////////////////////////////////////////////
        
        case 'check':
        
            if(file_exists(BASE_PATH . "/data/" . $_SESSION['user'] . '_acl.php')){ 
            ?>
            <label><?php i18n("Restricted"); ?></label>
            <pre><?php i18n("You can not check for updates"); ?></pre>
            <button onclick="codiad.modal.unload();return false;"><?php i18n("Close"); ?></button>
            <?php } else {
                require_once('class.autoupdate.php');
                $update = new AutoUpdate();
                $vars = json_decode($update->Check(), true);
            ?>
            <form>
            <input type="hidden" name="archive" value="<?php echo $vars[0]['data']['archive']; ?>">
            <input type="hidden" name="remoteversion" value="<?php echo $vars[0]['data']['remoteversion']; ?>">
            <label><?php i18n("Auto Update Check"); ?></label>
            <br><table>
                <tr><td><?php i18n("Your Version"); ?></td><td><?php echo $vars[0]['data']['currentversion']; ?></td></tr>
                <tr><td><?php i18n("Latest Version"); ?></td><td><?php echo $vars[0]['data']['remoteversion']; ?></td></tr>
            </table>
            <?php if($vars[0]['data']['currentversion'] != $vars[0]['data']['remoteversion']) { ?>
            <br><label><?php i18n("Changes on Codiad"); ?></label>
            <pre style="overflow-x: auto; overflow-y: scroll; max-height: 200px; max-width: 450px;"><?php echo $vars[0]['data']['message']; ?></pre>
            <?php } else { ?>
            <br><br><b><label><?php i18n("Congratulation, your system is up to date."); ?></label></b>
            <?php if($vars[0]['data']['name'] != '') { ?>
            <em><?php i18n("Last update was done by "); ?> <?php echo $vars[0]['data']['name']; ?>.</em>
            <?php } } ?>
            <br><?php
                if($vars[0]['data']['currentversion'] != $vars[0]['data']['remoteversion']) {
                    if($vars[0]['data']['autoupdate'] == '1') {
                        echo '<button class="btn-left" onclick="codiad.autoupdate.update();return false;">Update Codiad</button>&nbsp;<button class="btn-left" onclick="codiad.autoupdate.download();return false;">Download Codiad</button>&nbsp;';
                    } else {
                        if($vars[0]['data']['autoupdate'] == '-1') {
                            echo '<button class="btn-left" onclick="codiad.autoupdate.download();return false;">Download Codiad</button>&nbsp;';
                        } else {
                            echo '<button class="btn-left" onclick="codiad.autoupdate.check();return false;">Test Permission</button>&nbsp;<button class="btn-left" onclick="codiad.autoupdate.download();return false;">Download Codiad</button>&nbsp;';
                        }
                    }
                }
            ?><button class="btn-right" onclick="codiad.modal.unload();return false;"><?php i18n("Cancel"); ?></button>
            <form>
            <?php }
            break;
            
        //////////////////////////////////////////////////////////////////
        // Update
        //////////////////////////////////////////////////////////////////
        
        case 'update':
            ?>
            <form>
            <input type="hidden" name="remoteversion" value="<?php echo($_GET['remoteversion']); ?>">
            <label><?php i18n("Confirm Update"); ?></label>
            <pre><?php i18n("Update:"); ?> <?php echo($_GET['remoteversion']); ?></pre>
            <button class="btn-left"><?php i18n("Confirm"); ?></button>&nbsp;<button class="btn-right" onclick="codiad.modal.unload(); return false;"><?php i18n("Cancel"); ?></button>
            <form>
            <?php
            break;
            
    }
    
?>
