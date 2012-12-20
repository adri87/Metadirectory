<?php
include_once("../inc/import.inc.php");

$queries = array( "mapping", "voice", "image", "twitter" );

$methods = array( 
			array(
				array( 1, "degreeCentrality" ),
				array( 0, "betweennessCentrality" ),
				array( 0, "closenessCentrality" ),
				array( 0, "eigenvectorCentrality" ),
				array( 0, "plainUserRating" ),
				array( 0, "stackOverflow" )
			),
			array(
				array( 0, "degreeCentrality" ),
				array( 1, "betweennessCentrality" ),
				array( 0, "closenessCentrality" ),
				array( 0, "eigenvectorCentrality" ),
				array( 0, "plainUserRating" ),
				array( 0, "stackOverflow" )
			),
			array(
				array( 0, "degreeCentrality" ),
				array( 0, "betweennessCentrality" ),
				array( 1, "closenessCentrality" ),
				array( 0, "eigenvectorCentrality" ),
				array( 0, "plainUserRating" ),
				array( 0, "stackOverflow" )
			),
			array(
				array( 0, "degreeCentrality" ),
				array( 0, "betweennessCentrality" ),
				array( 0, "closenessCentrality" ),
				array( 1, "eigenvectorCentrality" ),
				array( 0, "plainUserRating" ),
				array( 0, "stackOverflow" )
			),
			array(
				array( 0, "degreeCentrality" ),
				array( 0, "betweennessCentrality" ),
				array( 0, "closenessCentrality" ),
				array( 0, "eigenvectorCentrality" ),
				array( 1, "plainUserRating" ),
				array( 0, "stackOverflow" )
			),
			array(
				array( 0, "degreeCentrality" ),
				array( 0, "betweennessCentrality" ),
				array( 0, "closenessCentrality" ),
				array( 0, "eigenvectorCentrality" ),
				array( 0, "plainUserRating" ),
				array( 1, "stackOverflow" )
			),
			array(
				array( 0, "degreeCentrality" ),
				array( 0, "betweennessCentrality" ),
				array( 0, "closenessCentrality" ),
				array( 2, "eigenvectorCentrality" ),
				array( 1, "plainUserRating" ),
				array( 1, "stackOverflow" )
			),
			array(
				array( 1, "degreeCentrality" ),
				array( 1, "betweennessCentrality" ),
				array( 1, "closenessCentrality" ),
				array( 1, "eigenvectorCentrality" ),
				array( 1, "plainUserRating" ),
				array( 1, "stackOverflow" )
			)
		);

$titles = array ( 		"Degree Centrality",
				"Betweenness Centrality",
				"Closeness Centrality",
				"Eigenvector Centrality",
				"User Rating",
				"StackOverflow",
				"Combination 1",
				"Combination 2" );

foreach( $queries as $query ) {
	$results[ $query ] = array();
	foreach( $methods as $i => $method ) {
		$obj = new romkingRanking( $query, $method );
		$results[ $query ][ $i ] = $obj->aSet;
	}
}

foreach( $results as $query => $result ) {

	$table[ $query ] = array( );
	array_push( $table[ $query ], '\newpage' );
	array_push( $table[ $query ], '\begin{table}[h!]' );
	array_push( $table[ $query ], '	\tiny' );
	array_push( $table[ $query ], '	\centering' );
	array_push( $table[ $query ], '	\caption{Test results for query 1: \'\'' . $query . '\'\' (overall ' . sizeof( $result[ 0 ] ) . ' results)}' );
	array_push( $table[ $query ], '	' );

	for( $j = 0; $j <= 3; $j++ ) {
		array_push( $table[ $query ], '	\begin{tabularx}{\textwidth}{|c|X|l|X|l|}' );
		array_push( $table[ $query ], '		\hline \hline' );
		array_push( $table[ $query ], '		\multirow{2}{*}{ } & \multicolumn{2}{|c|}{\small Degree Centrality}' );
		array_push( $table[ $query ], '			& \multicolumn{2}{|c|}{\small Betweenness Centrality}  \\\\ \cline{2-5}' );
		array_push( $table[ $query ], '			& API & R(s) & API & R(s) \\\\ \hline' );

		for( $i = 0; $i < 10; $i++) {
			array_push( $table[ $query ], '		' . $i . ' & ' . $result[$j][$i]['obj']->aData['title'] . ' & '. $result[$j][$i]['score']['overall'] );
			array_push( $table[ $query ], '			& ' . $result[($j+1)][$i]['obj']->aData['title'] . ' & '. $result[($j+1)][$i]['score']['overall'] . ' \\\\ \hline' );
		}

		array_push( $table[ $query ], '	\end{tabularx}' );
		array_push( $table[ $query ], '	' );
	}

	array_push( $table[ $query ], '	\label{table:results-q-' . $query . '}' );
	array_push( $table[ $query ], '\end{table}' );
	array_push( $table[ $query ], '	' );
	echo implode( "\r\n", $table[ $query ] );

}

echo "\r\n\r\n\r\n";
echo implode( "\r\n", $table );
?>