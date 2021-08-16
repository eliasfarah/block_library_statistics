<?php

$observers = array(
    array(
        'eventname'   => '\mod_data\event\record_viewed',
        'callback'    => '\block_library_statistics\observer::get_record_viewed',
    )

);
