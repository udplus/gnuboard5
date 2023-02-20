<?php
define('G5_IS_ADMIN', true);
define('G5_IS_GNUWIZ_ADMIN_PAGE', true);
include_once ('../../common.php');

include_once(G5_ADMIN_PATH.'/admin.lib.php');
include_once('./admin.gnuwiz.lib.php');

if (isset($token)) {
    $token = @htmlspecialchars(strip_tags($token), ENT_QUOTES);
}

run_event('admin_common');