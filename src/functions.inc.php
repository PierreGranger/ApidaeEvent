<?php

function __(string $msg, bool $echo=true) {
    if ( ! $echo ) return _($msg) ;
    echo _($msg) ;
}
