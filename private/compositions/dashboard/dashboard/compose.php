<?php
$alias = Composition::$Active->Context['data']['alias'];
switch($alias){
    case 'contacts': require_once 'contacts.php'; break;
    case 'resources': require_once 'resources.php'; break;
    case 'leads': require_once 'leads.php'; break;
    case 'support': require_once 'support.php'; break;
    case 'settings': require_once 'settings.php'; break;
    case 'pages': require_once 'pages.php'; break;
    case 'posts': require_once 'posts.php'; break;
    case 'listings': require_once 'listings.php'; break;
    default: break;
}





?>