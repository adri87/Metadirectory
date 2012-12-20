<?php

include_once("../inc/import.inc.php");

$tag = "mapping";
$IRI = "http://code.google.com/apis/maps/index.html";

// $index = array($IRI => array(
//     "oml:betweennessCentrality" => 31337,
//     "oml:closenessCentrality" => 41337
// ));
// 
// $conf = array('ns' => array('rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'owl' => 'http://www.w3.org/2002/07/owl#', 'oml' => 'http://www.ict-omelette.eu/schema.rdf#'));
// $serializer = ARC2::getRDFXMLSerializer($conf);
// 
// $rdfxml = $serializer->getSerializedIndex($index);
// $romkingStore->overwrite($rdfxml);

// $src = "../js/betweenness.json";
// $f = fopen( $src, "r" );
// $data = fread( $f, filesize( $src ) );
// fclose( $f );
// 
// $aBetweenness = json_decode ( $data, true );
// 
// $src = "../js/closeness.json";
// $f = fopen( $src, "r" );
// $data = fread( $f, filesize( $src ) );
// fclose( $f );
// 
// $aCloseness = json_decode ( $data, true );
// 
// $obj = new romkingRanking( $tag );
// $obj->constructAdjacencyMatrixOfAPIMashupGraph ();
// 
// foreach( $obj->aAPIs as $IRI => $ID ) {
// 	$index = array($IRI => array(
// 		"oml:betweennessCentrality" => $aBetweenness[ $ID ],
// 		"oml:closenessCentrality" => $aCloseness[ $ID ]
// 	));
// 
// 	$conf = array('ns' => array('rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#', 'owl' => 'http://www.w3.org/2002/07/owl#', 'oml' => 'http://www.ict-omelette.eu/schema.rdf#'));
// 	$serializer = ARC2::getRDFXMLSerializer($conf);
// 
// 	$rdfxml = $serializer->getSerializedIndex($index);
// 	$romkingStore->append($rdfxml);
// }

// $output = shell_exec( "ruby ../ruby/google site:programmersheaven.com Skype API" );
// echo (int) str_replace( ",", "", substr( $output, 0, strpos( $output, "\n" ) ) );


// $obj = new romkingComponent( $IRI );
// $obj = new romkingComponentSet( $tag );
 $obj = new romkingRanking( $tag );
// $obj->constructAdjacencyMatrixOfAPIMashupGraph ();
// $obj->calculateBetweennessAndClosenessCentralities ();
// $obj->saveCentralities ();
// echo "hi @ all!<br /><br />";
// echo json_encode( $obj->aBetweennessCentralities );
// echo "<br/><br />";
// echo json_encode( $obj->aClosenessCentralities );

echo "<pre>";
$output = print_r($obj, true);
echo htmlentities($output);
#echo $obj;
echo "</pre>";

?>