<?php

    echo $_SERVER['DOCUMENT_ROOT'].'/'.$_SERVER["HTTP_HOST"]."/".$_SERVER["REQUEST_URI"];
    echo "<br/>";
    echo $_SERVER["HTTP_HOST"];
    echo "<br/>";
    echo $_SERVER["REQUEST_URI"];
?>