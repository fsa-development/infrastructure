<?php

require_once(__DIR__.'/../../../core.php');
global $DNS_Hosts;
global $Master_DNS_Hostname;
global $RemoteBase;

$Master_DNS_Hostname = 'dns1.'.$RemoteBase;

$servers = LoadObjects('dns_servers');
foreach($servers as $s){
	if( $s->data['master'] == true) $Master_DNS_Hostname = $s->data['host'];

	$DNS_Hosts[ $s->data['host'] ]['inet_ip'] = $s->data['inet_ip'];
	$DNS_Hosts[ $s->data['host'] ]['lan_ip'] = $s->data['lan_ip'];	// BEST TO SSH WITH IN CLUSTER
	$DNS_Hosts[ $s->data['host'] ]['vpn_ip'] = $s->data['vpn_ip'];	// BEST TO SSH WITH OUT OF CLUSTER
	$DNS_Hosts[ $s->data['host'] ]['ssh_port'] = $s->data['ssh_port'];
	$DNS_Hosts[ $s->data['host'] ]['pass'] = $server['pass'];
}


?>
