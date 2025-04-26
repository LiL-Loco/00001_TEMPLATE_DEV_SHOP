<?php
define('PFAD_ROOT', '/home/sellx-template/htdocs/template.sellx.studio/');
define('URL_SHOP', 'https://template.sellx.studio');
define('DB_HOST','localhost');
define('DB_NAME','templates');
define('DB_USER','templatedev');
define('DB_PASS','fKkcOz2nyldTAxOGMjnq');

define('BLOWFISH_KEY', '60e0f399bbf9864c4753565b549c6e');
// enables printing of warnings/infos/errors for the shop frontend
define('SHOP_LOG_LEVEL', E_ALL);
// enables printing of warnings/infos/errors for the dbeS sync
define('SYNC_LOG_LEVEL', E_ALL ^ E_NOTICE ^ E_DEPRECATED ^ E_WARNING);
// enables printing of warnings/infos/errors for the admin backend
define('ADMIN_LOG_LEVEL', E_ALL);
// enables printing of warnings/infos/errors for the smarty templates
define('SMARTY_LOG_LEVEL', E_ALL);
// excplicitly show/hide errors
ini_set('display_errors', 0);
