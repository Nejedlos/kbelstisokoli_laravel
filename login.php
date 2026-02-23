<?php
$u = 'kbelstisok378_MN'; // uzivatelske jmeno
$p = 'Gesto?335'; // uzivatelske heslo
$h = 'mysql2.ebola.cz'; // adresa mysql serveru
$jmeno_db = 'kbelstisokolicz_kbelstisokoli'; // jmeno databaze
$db_prefix = ''; // prefix tabulek (napr. 'blog_' ) )


//------------------------------------------------ 

require dirname(__FILE__).'/admin/libs/db-layer.php';

$db = new DB($h,$u,$p,$jmeno_db);

$db->query('SET NAMES cp1250');
$db->query('SET CHARACTER SET cp1250');
if(!$db->spojeni)
  {
  require $admin_prefix.'mysql-error.php';
  exit;
  }

if(!$db->spojeno)
  {
  exit('<p>MySQL server byl pripojen (spravne prihlasovaci udaje),
	ale <b>nebyla nalezena databaze</b> se zadanym jmenem.
	Patrne chybny udaj.</p>');
  }
?>