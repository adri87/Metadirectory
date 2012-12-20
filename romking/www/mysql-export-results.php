<?php

include_once("../inc/import.inc.php");

$queries = array( "mapping", "voice", "image", "twitter" );
$algo = "PwebUserRating";

header('Content-type: application/octet-stream');
header('Content-Disposition: attachment; filename="romking_eval_ranked_results-'.$algo.'-'.date("Y-m-d").'.sql"');

foreach( $queries as $qry ) {

	$obj = new romkingRanking( $qry );

	foreach( $obj->aSet as $i => $api ) {

		echo "INSERT INTO romking_eval_romking ( `IRI`, `set`, `algo`, `score` ) VALUES ( '".$api['obj']->aData['IRI']."', '".$qry."', '".$algo."', '".$api['score']['overall']."' );";
		echo "\r\n";

	}

	echo "\r\n";

}

?>