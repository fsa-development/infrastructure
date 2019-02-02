<?php

//echo __DIR__.'/../../core.php';
require_once(__DIR__.'/../../core.php');


$html = new renderable('html',NULL,NULL);
$head = new renderable('head',NULL,NULL);
$body = new renderable('body',NULL,NULL);
$report = new renderable('table',NULL,NULL);
$report->attributes['cellpadding']=4;
$report->attributes['width']='100%';


$head->content='<title>User Repot</title>';


$users = LoadObjects('users');
$labels = ['id','email','username','join_date','login_date','name','agent_license','tele'];
$tr = new renderable('tr',NULL,NULL);
foreach($labels as $l){
  $th = new renderable('th',NULL,NULL);
  $th->content=$l;
  $tr->children[] = $th;
}
$report->children[] = $tr;

foreach($users as $u){
  $tr = new renderable('tr',NULL,NULL);

  $td = new renderable('td');
  $td->content = $u->data['id'];
  $tr->children[] = $td;

  $td = new renderable('td');
  $td->content = $u->data['email'];
  $tr->children[] = $td;

  $td = new renderable('td');
  $td->content = '<a href="https://'.$u->data['username'].'.'.$RemoteBase.'">'.$u->data['username'].'</a>';
  $tr->children[] = $td;

  $td = new renderable('td');
  $td->content = $u->data['join_date'];
  $tr->children[] = $td;

  $td = new renderable('td');
  $td->content = $u->data['login_date'];
  $tr->children[] = $td;

  $td = new renderable('td');
  $td->content = $u->data['first_name'] . ' '.$u->data['last_name'] ;
  $tr->children[] = $td;

  $td = new renderable('td');
  $td->content = $u->data['agent_license'];
  $tr->children[] = $td;

  $td = new renderable('td');
  $td->content = $u->data['tele'];
  $tr->children[] = $td;

  $report->children[] = $tr;
}

$html->children[] = $head;
$html->children[] = $body;
$body->children[] = $report;

echo $html->render();
?>
