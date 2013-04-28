<?php

    /*
    *  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
    *  as-is and without warranty under the MIT License. See
    *  [root]/license.txt for more. This information must remain intact.
    */
    

    require_once('../../common.php');
    require_once('class.autoupdate.php');

    //////////////////////////////////////////////////////////////////
    // Verify Session or Key
    //////////////////////////////////////////////////////////////////

    if($_GET['action']!='authenticate'){ checkSession(); }

    $update = new AutoUpdate();
    
    //////////////////////////////////////////////////////////////////
    // Set Initial Version
    //////////////////////////////////////////////////////////////////

    if($_GET['action']=='init'){
        $update->Init();
    }
    
    //////////////////////////////////////////////////////////////////
    // Clear Version
    //////////////////////////////////////////////////////////////////

    if($_GET['action']=='clear'){
        if(checkAccess()) {
            $update->Clear();
        }
    }
    
    //////////////////////////////////////////////////////////////////
    // Test Write Access
    //////////////////////////////////////////////////////////////////

    if($_GET['action']=='test'){
        if(checkAccess()) {
            $update->Test();
        }
    }
    
    //////////////////////////////////////////////////////////////////
    // Download Version
    //////////////////////////////////////////////////////////////////

    if($_GET['action']=='download'){
        if(checkAccess()) {
            $update->commit = $_GET['remoteversion'];
            $update->Download();
        }
    }

?>
