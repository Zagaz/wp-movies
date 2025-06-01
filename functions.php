<?php 

  // Dinamically  load from /includes 

    $includes = glob(__DIR__ . '/includes/*.php');
    foreach ($includes as $file) {
        require_once $file;
    }
    

