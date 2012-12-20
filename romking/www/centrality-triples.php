<?php
include_once("../inc/import.inc.php");

$src = "../js/betweenness.json";
$f = fopen( $src, "r" );
$data = fread( $f, filesize( $src ) );
fclose( $f );

$aBetweenness = json_decode ( $data, true );

$src = "../js/closeness.json";
$f = fopen( $src, "r" );
$data = fread( $f, filesize( $src ) );
fclose( $f );

$aCloseness = json_decode ( $data, true );

$obj = new romkingRanking( $tag );
$obj->constructAdjacencyMatrixOfAPIMashupGraph ();

foreach( $obj->aAPIs as $IRI => $ID ) {
	echo "<" . $IRI ."> <http://www.ict-omelette.eu/schema.rdf#betweennessCentrality> \"" . $aBetweenness[ $ID ] . "\" .\r\n";
	echo "<" . $IRI ."> <http://www.ict-omelette.eu/schema.rdf#closenessCentrality> \"" . $aCloseness[ $ID ] . "\" .\r\n";
}

?>