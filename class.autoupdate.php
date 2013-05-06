<?php

/*
*  Copyright (c) Codiad & daeks, distributed
*  as-is and without warranty under the MIT License. See
*  [root]/license.txt for more. This information must remain intact.
*/

require_once('../../common.php');

class AutoUpdate extends Common {

    //////////////////////////////////////////////////////////////////
    // PROPERTIES
    //////////////////////////////////////////////////////////////////
    
    public $remote = "";
    public $archive = "";
    public $commit = "";

    //////////////////////////////////////////////////////////////////
    // METHODS
    //////////////////////////////////////////////////////////////////

    // -----------------------------||----------------------------- //

    //////////////////////////////////////////////////////////////////
    // Construct
    //////////////////////////////////////////////////////////////////

    public function __construct(){
        ini_set("user_agent" , "Codiad");
        $this->remote = 'https://api.github.com/repos/Codiad/Codiad/commits';
        $this->archive = 'https://github.com/Codiad/Codiad/archive/master.zip';
    }
    
    //////////////////////////////////////////////////////////////////
    // Set Initial Version
    //////////////////////////////////////////////////////////////////
    
    public function Init() {
        $version = array();
        if(!file_exists(DATA ."/version.php")) {
            if(file_exists(BASE_PATH."/.git/FETCH_HEAD")) {
                $data = file(BASE_PATH."/.git/FETCH_HEAD");
                foreach($data as $line) {
                    $branch = explode("	", $line);
                    if(strpos($branch[2], "master") !== false) {
                        break;
                    }
                }
                $version[] = array("version"=>$branch[0],"time"=>time(),"name"=>"");
                saveJSON('version.php',$version);
            } else {
                $remote = json_decode(file_get_contents($this->remote.'/HEAD'),true);
                $version[] = array("version"=>$remote["sha"],"time"=>time(),"name"=>"");
                saveJSON('version.php',$version);
            }
        } else {
            $app = getJSON('version.php');
            if($app[0]['version'] == '' && $app[0]['name'] == $_SESSION['user']) {
                $remote = json_decode(file_get_contents($this->remote.'/HEAD'),true);
                $version[] = array("version"=>$remote["sha"],"time"=>time(),"name"=>$_SESSION['user']);
                saveJSON('version.php',$version);
            }
            if(file_exists(BASE_PATH."/.git/FETCH_HEAD")) {
                $data = file(BASE_PATH."/.git/FETCH_HEAD");
                foreach($data as $line) {
                    $branch = explode("	", $line);
                    if(strpos($branch[2], "master") !== false) {
                        break;
                    }
                }
                if($app[0]['version'] != $branch[0]) {
                    $version[] = array("version"=>$branch[0],"time"=>time(),"name"=>"");
                    saveJSON('version.php',$version);
                }
            }
        }
    }
    
    //////////////////////////////////////////////////////////////////
    // Clear Version
    //////////////////////////////////////////////////////////////////
    
    public function Clear() {
        $version[] = array("version"=>"","time"=>time(),"name"=>$_SESSION['user']);
        saveJSON('version.php',$version);
    }   
    
    //////////////////////////////////////////////////////////////////
    // Check Version
    //////////////////////////////////////////////////////////////////
    
    public function Check() {
        if(file_exists(BASE_PATH."/.git/FETCH_HEAD")) {
            $data = file(BASE_PATH."/.git/FETCH_HEAD");
            foreach($data as $line) {
                $branch = explode("	", $line);
                if(strpos($branch[2], "master") !== false) {
                    break;
                }
            }
            $app[0]['version'] = $branch[0];
            $app[0]['name'] = "";
            $autoupdate = '-1';
        } else {
            $app = getJSON('version.php');
            if(is_writeable(BASE_PATH) && is_writeable(COMPONENTS) && is_writeable(THEMES)) {
                if(extension_loaded('zip') && extension_loaded('openssl') && ini_get('allow_url_fopen') == 1) {
                    $autoupdate = '1';
                } else {
                    $autoupdate = '-1';
                }
            } else {
                $autoupdate = '0';
            }
        }

        if($this->remote != '') {
            $remote = json_decode(file_get_contents($this->remote),true);
        }
        
        $search = array("\r\n", "\n", "\r");
        $replace = array(" ", " ", " ");
        
        $message = '';
        $merge = '';
        foreach($remote as $commit) {
            if($app[0]['version'] != $commit["sha"]) {
                if(strpos($commit["commit"]["message"],"Merge") === false) {
                    $message .= '- '.str_replace($search,$replace,$commit["commit"]["message"]).'<br/>';
                } else {
                    $merge .= '- '.str_replace($search,$replace,$commit["commit"]["message"]).'<br/>';
                }
            } else {
                break;
            }
        }
        
        if($message == '') {
            $message = $merge;
        }
                
        return "[".formatJSEND("success",array("currentversion"=>$app[0]['version'],"remoteversion"=>$remote[0]["sha"],"message"=>$message,"archive"=>$this->archive,"autoupdate"=>$autoupdate,"name"=>$app[0]['name']))."]";
    }
    
    //////////////////////////////////////////////////////////////////
    // Download Version
    //////////////////////////////////////////////////////////////////
    
    public function Download() {
        if(file_exists('../../'.$this->commit.'.zip')) {
            unlink('../../'.$this->commit.'.zip');
        }
        file_put_contents('../../'.$this->commit.'.zip', fopen(str_replace('master', $this->commit, $this->archive), 'r'));

        $data = '<?php
  
$commit = "'.$this->commit.'";

function rrmdir($path){
    return is_file($path)?
    @unlink($path):
    @array_map("rrmdir",glob($path."/*"))==@rmdir($path);
}

function cpy($source, $dest, $ign){
    if(is_dir($source)) {
        $dir_handle=opendir($source);
        while($file=readdir($dir_handle)){
            if(!in_array($file, $ign)){
                if(is_dir($source."/".$file)){
                    mkdir($dest."/".$file);
                    cpy($source."/".$file, $dest."/".$file, $ign);
                } else {
                    copy($source."/".$file, $dest."/".$file);
                    unlink($source."/".$file);
                }
            }
        }
        closedir($dir_handle);
        rmdir($source);
    } else {
        copy($source, $dest);
        unlink($source);
    }
}

// Getting current codiad path
$path = rtrim(str_replace($commit.".php", "", $_SERVER["SCRIPT_FILENAME"]),"/");
$ignore = array(".","..","data", "workspace", "plugins", "backup", "config.php", $commit.".php",$commit.".zip", "Codiad-".$commit);

$zip = new ZipArchive;
$res = $zip->open($path."/".$commit.".zip");
// open downloaded archive
if ($res === TRUE) {
  // extract archive
  if($zip->extractTo($path) === true) {
    // delete old files except some directories and files
    if(!file_exists($path."/backup")) { mkdir($path."/backup"); }
    cpy($path, $path."/backup", $ignore);
    
    // move extracted files to path
    cpy($path."/Codiad-".$commit, $path, array(".",".."));

    // store current commit to version.json
    $version = array();
    $version[] = array("version"=>$commit);
    file_put_contents($path."/data/version.php", "<?php/*|" . json_encode($version) . "|*/?>");  

    // cleanup and restart codiad
    rrmdir($path."/backup");
    rmdir($path."/backup");
    unlink($commit.".zip");
    unlink($commit.".php");
    header("Location: ".str_replace($commit.".php","",$_SERVER["SCRIPT_NAME"]));
  } else {
    echo "Unable to extract ".$path."/".$commit.".zip to path ".$path;
  }
  $zip->close();
} else {
    echo "Unable to open ".$path."/".$commit.".zip";
}

?>';
        $write = fopen('../../'.$this->commit.'.php', 'w') or die("can't open file");
        fwrite($write, $data);
        fclose($write);
        
        session_unset(); session_destroy(); session_start();
        echo formatJSEND("success",null);
    }

}
