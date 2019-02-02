<?php

define('IncludeDNS',true);
require_once( __DIR__ .'/../../core.php');
global $Master_DNS_Hostname;
global $DNS_Hosts;


$dns_update_trigger = $SupportPath . '/dataset/transactions/dns_update.trigger';
$nginx_update_trigger = $SupportPath . '/dataset/transactions/nginx_update.trigger';
$dns_update_queue = $SupportPath . '/dataset/transactions/dns_update.queue';
$nginx_update_queue = $SupportPath . '/dataset/transactions/nginx_update.queue';
$file_root = '/srv/suiteux/support/';

if( !file_exists($file_root.$dns_update_trigger) ) exit();
$queue_file = fopen($file_root.$nginx_update_queue, 'r');

if($queue_file) while (($line = fgets($queue_file)) !== false) {

	$arr = explode(' ',$line);
	$UserID = $arr[0];
	$domain = $arr[1];

	$output = shell_exec('pdnsutil check-zone '.$domain);
	$ZoneCreated = strpos('[error]', $output) === false ? true : false;

	$domain_comp = LoadObject('compositions', ['method' => 'WHERE `user` = '.$UserID . ' AND `alias` = '.$domain]); //
	$ips = explode( $domain_comp->data['edge_servers'] );

	foreach($ips as $edge_ip){
		//if($ip == $reset($ips) && !$ZoneCreated)	//only on the first result

		$cmd = 'pdnsutil clear-zone '.$domain;
		$cmd = 'pdnsutil create-zone '.$domain.' '.$Master_DNS_Hostname;

		$dns_i=0;
		foreach($DNS_Hosts as $hostname => $p){
			//adds NS record for dns[1..99].suiteux.com and a given IP
			$cmd = 'pdnsutil add-record '.$hostname.' '.$p['inet_ip'];
			$dns_i++;
		}

		$cmd = 'pdnsutil add-record '.$domain.      ' @ A '.$edge_ip;
		$cmd = 'pdnsutil add-record '.$domain.    ' www A '.$edge_ip;
		$cmd = 'pdnsutil add-record '.$domain. ' static A '.$edge_ip;
		$cmd = 'pdnsutil add-record '.$domain.' service A '.$edge_ip;

		//foreach( LoadExternalRecords as $rec)			do more cmds
	}
}
else {
	// error opening the file.
}

fclose($queue_file);


?>
