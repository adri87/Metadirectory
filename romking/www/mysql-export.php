<?php

include_once("../inc/import.inc.php");

$queries = array( "mapping", "voice", "image", "twitter" );

header('Content-type: application/octet-stream');
header('Content-Disposition: attachment; filename="romking_eval_results-'.date("Y-m-d").'.sql"');

foreach( $queries as $qry ) {

	$obj = new romkingComponentSet( $qry );

	foreach( $obj->aSet as $api ) {

		echo "INSERT INTO romking_eval_results ( `IRI`, `set`, `name`, `vendor`, `source`) VALUES ( '".$api['obj']->aData['IRI']."', '".$qry."', '".$api['obj']->aData['title']."', '".$api['obj']->aData['vendor']."', '".$api['obj']->aData['source']."' );";
		echo "\r\n";

	}

	echo "\r\n";

}

?>