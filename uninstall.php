<?php
// part 1
if(!defined("WP_UNINSTALL_PLUGIN"))
	exit();
// part 2
delete_option('bark_url');
delete_option('bark_key');
delete_option('bark_param');
delete_option('bark_scence');