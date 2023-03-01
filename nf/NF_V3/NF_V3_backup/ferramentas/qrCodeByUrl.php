<?php
    
    require_once("../libs/phpqrcode/qrlib.php");
    QRcode::png( $url=$argv[1], $dir=$argv[2]);
    
