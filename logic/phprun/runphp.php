<?php
    $content = "gege";
    $fp = fopen("myText.txt","wb");
    fwrite($fp,$content);
    fclose($fp);