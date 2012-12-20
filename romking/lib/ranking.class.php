<?php
/**
*
* @author Tilo Zemke <zeti@hrz.tu-chemnitz.de>
* @version 1.0
* @module rOMking - OMELETTE Component Ranking
* @deprec Does the ranking on a romkingComponentSet
*
*/
class romkingRanking extends romkingComponentSet
{
	/***********************************************
	* =VAR
	***********************************************/

	/**
	* Ranking algorithms to use
	*
	* This var defines the methodology of how the
	* matched components are ranked, i.e. how their ranking
	* score is calculated.
	* This array is an indexed array of (factor, elementary
	* algorithm)-pairs.
	* The factor is the coefficient of
	* how important the elementary function is compared to
	* the others.
	* Possible values for 'elementary algorithm':
	*	- 'degreeCentrality'
	*	- 'betweenessCentrality'
	*	- 'closenessCentrality'
	*	- 'eigenvectorCentrality'
	*	- 'plainUserRating'
	*	- 'stackOverflow'
	*	- 'programmersHeaven'
	*	- 'googleSearch'
	*	- 'random'
	*
	* TODO: levenshtein?
	*
	* @var array
	*/
	public $aRankingMethod = array(
						array( 0, "degreeCentrality" ),
						array( 0, "betweennessCentrality" ),
						array( 1, "closenessCentrality" ),
						array( 0, "eigenvectorCentrality" ),
						array( 0, "plainUserRating" ),
						array( 0, "stackOverflow" ),
						array( 0, "programmersHeaven" ),
						array( 0, "googleSearch" ),
						array( 0, "random" )
					);

	/**
	* API-Mashup-Graph (Adjacency Matrix)
	*
	* These 2 associative arrays contain
	* the adjacency matrix of the api-mashup-graph which
	* is the reduction of the original hypergraph.
	* They are necessary for the calculation of several
	* ranking functions.
	*
	* @var array
	*/
	public $aGraph = array();
	
	/**
	* APIs
	*
	* This array contains all IRIs of APIs in the triple
	* store.
	*
	* @var array
	*/
	public $aAPIs = array();

	/**
	* Mashups
	*
	* This array caontains all mashups (their IRIs) in
	* the triple store.
	*
	* @var array
	*/
	public $aMashups = array();

	/**
	* BetweennessCentralities
	*
	* This array stores the calculated BetweennessCentralities
	* for all components
	*
	* @var array
	*/
	public $aBetweennessCentralities = array();

	/**
	* ClosenessCentralities
	*
	* This array stores the calculated ClosenessCentralities
	* for all components
	*
	* @var array
	*/
	public $aClosenessCentralities = array();

	/**
	* EigenvectorCentralities
	*
	* This array stores the calculated EigenvectorCentralities
	* for all components
	*
	* @var array
	*/
	private $aEigenvectorCentralities = array();

	/***********************************************
	* =FUNCTION
	***********************************************/
	
	/**
	* Constructor
	*
	* Stores the given user input and invokes the collection
	* of components from the triple store.
	*
	* @global romkingStore - global triple store instance
	* @param string sIRI
	* @return array
	*/	
	public function __construct ( $sQueryExpression, $aRankingMethod = array() ) {

		if( $aRankingMethod )
			$this->aRankingMethod = $aRankingMethod;
	
		# reference to triple store
		global $romkingStore;
		$this->oTripleStore = $romkingStore;
	
		# store given search query
		$this->sQueryExpression = strtolower( trim( $sQueryExpression ) );

		# call: analyze query string
		$this->analyzeUserInputString();

		# call: build SPARQL query
		$this->buildSPARQLQuery();

		# call: execute Query
		$this->executeSPARQLQuery();

		# call: calulate ranking scores
		$this->calculateRankingScores();

		# sort set according to components' ranking scores
		usort( $this->aSet, array( $this, "compareComponentsAccordingToScore" ) );
		
		# return associative array
		return $this->aSet;
	
	}
	
	/***********************************************
	* =RANKING
	***********************************************/

	/**
	* Combine Ranking Functions
	*
	* This function combines the ranking functions specified in
	* {@link romkingComponentSet::$aRankingMethod $aRankingMethod}
	* and calculates the overall ranking score for each component
	* in the current set.
	*
	* @uses romkingComponentSet::$aRankingMethod
	* @uses romkingComponentSet::$aSet
	* @return void
	*/
	private function calculateRankingScores () {

		# sums for all ranking scores
		# needed to normalize the scores
		$aSums = array();

		# iterate over the whole set
		foreach( $this->aSet as $iIndex => $aComp )
			# iterate over ranking functions
			foreach( $this->aRankingMethod as $aRankingFunction )
				# test if ranking function is relevant and exists
				if( method_exists( $this, "componentRankingFunction_" . $aRankingFunction[ 1 ] ) &&
					$aRankingFunction[ 0 ] > 0 ) {

					# calculate score of current component in current ranking function
					$this->aSet[ $iIndex ][ 'score' ][ $aRankingFunction[ 1 ] ] = call_user_func( array( $this, "componentRankingFunction_" . $aRankingFunction[ 1 ] ), $aComp[ 'obj' ] );

					# add the score to the overall sum of this ranking function
					$aSums[ $aRankingFunction[ 1 ] ] += $this->aSet[ $iIndex ][ 'score' ][ $aRankingFunction[ 1 ] ];

				}

		# normalize each score
		foreach( $this->aSet as $iIndex => $aComp ) 
			foreach( $aComp[ 'score' ] as $sScoringFunction => $iScore )
				$this->aSet[ $iIndex ][ 'score' ][ $sScoringFunction ] = $iScore / $aSums[ $sScoringFunction ];

		# calculate overall score according to the
		# weight factors of the specific ranking functions
		foreach( $this->aSet as $iIndex => $aComp )  {

			# init overall score
			$iOverallScore = 0;

			# init sum of weights
			$iWeightSum = 0;

			foreach( $this->aRankingMethod as $aRankingFunction ) {
				$iOverallScore += $aRankingFunction[ 0 ] * $aComp[ 'score' ][ $aRankingFunction [ 1 ] ];
				$iWeightSum += $aRankingFunction[ 0 ];
			}

			# store normalized overall score
			$this->aSet[ $iIndex ][ 'score' ][ 'overall' ] = $iOverallScore / $iWeightSum;

		}
		

	}

	/**
	* Compare Scores
	*
	* This is an elementary function that compares two arrays,
	* i.e. rankingComponents in a rankingComponentSet, according to their
	* scores. The higher the score, the higher the position of
	* the object.
	* Used to sort the set of components with the help of
	* {@link http://php.net/manual/en/function.usort.php PHP's usort}.
	*
	* @param array
	* @param array
	* @return int
	*/
	public static function compareComponentsAccordingToScore ( $aCompA, $aCompB ) {
		
		# returns a value greater than, equal to or lower than 0
		# if the score of component A is greater than, equal to or lower than
		# the score of component B
		# NOTE: scores are multiplied with high int in order to solve problem
		#	with the precision on float
		return ( round( $aCompA[ 'score' ][ 'overall' ] * 1000000000 ) - round( $aCompB[ 'score' ][ 'overall' ] * 1000000000 ) ) * ( -1 );

	}

	/**
	* Random Ranking Function
	*
	* This will return a random ranking score for the given
	* component. Default score value will range between 0 and
	* 10*{@link romkingComponentSet::$iRealSetSize $iRealSetSize}
	*
	* @uses romkingComponentSet::$iRealSetSize
	*
	* @param object component
	* @return integer
	*/
	private function componentRankingFunction_random ( $oComponent ) {

		mt_srand((double)microtime()*1000000);
		return mt_rand(0, $this->iRealSetSize * 10);
	
	}

	/**
	* DegreeCentrality Ranking Function
	*
	* This function counts the mashups that use the given component
	* and returns the voerall amount.
	*
	* @uses romkingComponentSet::$oTripleStore
	*
	* @param object component
	* @return integer
	*/
	private function componentRankingFunction_degreeCentrality ( $oComponent ) {

		# build SPARQL query
		$sSPARQLQuery  = "\r\n";
		$sSPARQLQuery .= "PREFIX  rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>\r\n";
		$sSPARQLQuery .= "PREFIX  oml: <http://www.ict-omelette.eu/schema.rdf#>\r\n";
		$sSPARQLQuery .= "SELECT (count(distinct ?mashup) as ?degreeCentrality)\r\n";
		$sSPARQLQuery .= "\tWHERE {\r\n";
		$sSPARQLQuery .= "\t\t?api oml:api <" . $oComponent->sIRI . "> .\r\n";
		$sSPARQLQuery .= "\t\t?mashup oml:uses ?api .\r\n";
		$sSPARQLQuery .= "\t}";

		# fetch result
		$aRows = $this->oTripleStore->query( $sSPARQLQuery );
		$aRows = $aRows->getRows();

		# return result
		return $aRows[ 0 ][ 'degreeCentrality' ];
		
	}

	/**
	* Build bipartite Mashup-API Graph
	*
	* This functions builds the adjacency matrix of the bipartite
	* graph of mashups and APIs by reducing the original hypergraph
	* to this undirected and unweighted graph:
	* 	G = (A {union} M, E);
	*	A... The set of APIs
	*	M... The set of Mashups
	*	E = { (a,m) | mashup m uses API a }
	*
	* @uses romkingComponentSet::$aMashups
	* @uses romkingComponentSet::$aAPIs
	* @return array
	*/
	public function constructAdjacencyMatrixOfAPIMashupGraph () {

		# Testsets
// 		$this->aGraph = array(
// 			array(4, 6),
// 			array(4),
// 			array(3,4,6),
// 			array(2,4),
// 			array(0,1,2,3,5),
// 			array(4),
// 			array(0,2)
// 		);

// 		$this->aGraph = array(
// 			array(1, 2),
// 			array(0),
// 			array(0,3,4),
// 			array(2),
// 			array(2)
// 		);

// 		$this->aGraph = array(
// 			array(1, 2),
// 			array(0),
// 			array(0,4),
// 			array(4),
// 			array(2,3)
// 		);

// 		$this->aGraph = array(
// 			array(1),
// 			array(0, 2),
// 			array(1, 3),
// 			array(2),
// 		);

// 		$this->aGraph = array(
// 			array(1, 2),
// 			array(3),
// 			array(4, 1),
// 			array(1,4,5),
// 			array(5,6,7),
// 			array(7),
// 			array(0,4,7),
// 			array(5,6),
// 		);

// 		$this->aGraph = array(
// 			array(1, 2),
// 			array(0, 2, 4),
// 			array(0, 1, 3),
// 			array(2, 5, 6),
// 			array(1, 5),
// 			array(3, 4),
// 			array(3),
// 			array(8),
// 			array(7, 9),
// 			array(8),
// 			array()
// 		);

		# fetch the set of all APIs in the triple Store
		$sAPISPARQLQuery  = "\r\n";
		$sAPISPARQLQuery .= "PREFIX  rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>\r\n";
		$sAPISPARQLQuery .= "PREFIX  oml: <http://www.ict-omelette.eu/schema.rdf#>\r\n";
		$sAPISPARQLQuery .= "SELECT distinct ?apiIRI\r\n";
		$sAPISPARQLQuery .= "\tWHERE {\r\n";
		$sAPISPARQLQuery .= "\t\t?api oml:api ?apiIRI .\r\n";
		$sAPISPARQLQuery .= "\t}";

		$aAPIRows = $this->oTripleStore->query( $sAPISPARQLQuery );
		$i = 0;
		foreach( $aAPIRows->getRows() as $aResult ) {
			$this->aGraph[ $i ] = array();
			$this->aAPIs[ $aResult[ 'apiIRI' ] ] = $i;
			$i++;
		}

		# fetch the set of all mashups in the triple store
		$sMashupSPARQLQuery  = "\r\n";
		$sMashupSPARQLQuery .= "PREFIX  rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>\r\n";
		$sMashupSPARQLQuery .= "PREFIX  oml: <http://www.ict-omelette.eu/schema.rdf#>\r\n";
		$sMashupSPARQLQuery .= "SELECT distinct ?mashupIRI\r\n";
		$sMashupSPARQLQuery .= "\tWHERE {\r\n";
		$sMashupSPARQLQuery .= "\t\t?mashup oml:uses ?api .\r\n";
		$sMashupSPARQLQuery .= "\t\t?mashup oml:endpoint ?mashupIRI .\r\n";
		$sMashupSPARQLQuery .= "\t}";

		$aMashupRows = $this->oTripleStore->query( $sMashupSPARQLQuery );
		foreach( $aMashupRows->getRows() as $aResult ) {
			$this->aGraph[ $i ] = array();
			$this->aMashups[ $aResult[ 'mashupIRI' ] ] = $i;
			$i++;
		}

		# fetch all oml:uses from the triple store
		$sUsageSPARQLQuery  = "\r\n";
		$sUsageSPARQLQuery .= "PREFIX  rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>\r\n";
		$sUsageSPARQLQuery .= "PREFIX  oml: <http://www.ict-omelette.eu/schema.rdf#>\r\n";
		$sUsageSPARQLQuery .= "SELECT ?apiIRI ?mashupIRI\r\n";
		$sUsageSPARQLQuery .= "\tWHERE {\r\n";
		$sUsageSPARQLQuery .= "\t\t?api oml:api ?apiIRI .\r\n";
		$sUsageSPARQLQuery .= "\t\t?mashup oml:uses ?api .\r\n";
		$sUsageSPARQLQuery .= "\t\t?mashup oml:endpoint ?mashupIRI .\r\n";
		$sUsageSPARQLQuery .= "\t}";

		$aUsageRows = $this->oTripleStore->query( $sUsageSPARQLQuery );
		foreach( $aUsageRows->getRows() as $aResult ) {
			array_push( $this->aGraph[ $this->aAPIs[ $aResult[ 'apiIRI' ] ] ], $this->aMashups[ $aResult[ 'mashupIRI'] ] );
			array_push( $this->aGraph[ $this->aMashups[ $aResult[ 'mashupIRI' ] ] ], $this->aAPIs[ $aResult[ 'apiIRI'] ] );
		}

	}

	/**
	* Calculation of Betweenness and Closeness Centralities
	*
	* This is the implementation of
	* {@link http://www.informatik.uni-konstanz.de/algo/publications/b-fabc-01.pdf
	* Brandes' Algorithm} to calculate the Betweenness Centrality
	* as well as the Closeness Centrality in the Mashup-API-Graph.
	* Closeness Centrality is modified to be applicable on disconnected
	* graphs to according to
	* {@link http://toreopsahl.com/2010/03/20/closeness-centrality-in-networks-with-disconnected-components/
	* Tore Opsahl's Proposal}
	*
	* @uses romkingRanking::$aBetweennessCentralities
	* @uses romkingRanking::$aClosenessCentralities
	* @return array
	*/
	public function calculateBetweennessAndClosenessCentralities ( ) {

// 		$time = microtime();
// 		$time = explode(' ', $time);
// 		$time = $time[1] + $time[0];
// 		$begintime = $time;

		# Needed for writing to the triple store
		$aIRIs = array_flip( $this->aAPIs );

		# union of Mashups and APIs
		# i.e. the set of vertices of the graph
		foreach( $this->aGraph as $iIndex => $aNeighbours )
			if( $aNeighbours )
				$aBigV[ $iIndex ] = $aNeighbours;

		# amount of vertices in the graph
		$iAmountOfVertices = sizeof( $aBigV );

		# CB[v] = 0, v€V
		$this->aBetweennessCentralities = array();

		# CC[v] = 0, v€V
		$this->aClosenessCentralities = array();

		# for s € V
		foreach ( $aBigV as $iSIndex => $aNeighboursOfS ) {

			# S <- empty stack
			$aS = array();

			# P[w] <- empty list, w € V
			$aP = array();
			foreach( $aBigV as $iIndex => $aNeighbours ) // for( $iIndex = 0; $iIndex < $iAmountOfVertices; $iIndex++ )
				$aP[ $iIndex ] = array();

			# sigma(t) = 0, t € V; sigma(s) = 1
			$aSigma = array( $iSIndex => 1 );

			# d(t) = -1, t € V; d(s) = 0
			$aD = array();
			foreach( $aBigV as $iIndex => $aNeighbours ) // for( $iIndex = 0; $iIndex < $iAmountOfVertices; $iIndex++ )
				$aD[ $iIndex ] = -1;
			$aD[ $iSIndex ] = 0;

			# Q <- empty queue
			$aQ = array();

			# enqueue s -> Q
			array_push( $aQ, $iSIndex );

			# while (Q not empty) do
			while ( sizeof ( $aQ ) ) {

				# unqueue v <- Q
				$iV = array_shift( $aQ );

				# push v -> S
				array_push( $aS, $iV );

				# foreach neighbour w of v do
				foreach( $aBigV[ $iV ] as $iW ) {

					# // w found for the first time?
					# if d(w) < 0 then
					if( $aD[ $iW ] < 0 ) {

						# enqueue w -> Q
						array_push( $aQ, $iW );

						# d(w) <- d(v) + 1
						$aD[ $iW ] = $aD[ $iV ] + 1;

					}

					# // shortest path to w via v?
					# if d(w) = d(v) + 1 then
					if( $aD[ $iW ] == ( $aD[ $iV ] + 1 ) ) {

						# sigma(w) <- sigma(w) + sigma(v)
						$aSigma[ $iW ] = $aSigma[ $iW ] + $aSigma[ $iV ];

						# append v -> P(w)
						array_push( $aP[ $iW ], $iV );

					}
				}
			}

			# delta(v) <- 0, v € V
			$aDelta = array();

			# // S returns vertices in order of non-increasing distance from s
			# while (S not empty) do
			while ( sizeof( $aS ) ) {

				# pop w <- S
				$iW = array_pop( $aS );

				# for v € P(w) do delta(v) <- delta(v) + (sigma(v) / sigma(w)) * (1 + delta(w))
				foreach( $aP[ $iW ] as $iV )
					$aDelta[ $iV ] = $aDelta[ $iV ] + ( $aSigma[ $iV ] / $aSigma[ $iW ] ) * ( 1 + $aDelta[ $iW ] );
	
				# if w != s then CB(w) <- CB(w) + delta(w)
				if( $iW != $iSIndex )
					$this->aBetweennessCentralities[ $iW ] += $aDelta[ $iW ];
			}

			// Closeness Centrality
			$this->aClosenessCentralities[ $iSIndex ]  = array_sum( array_map( function ( $v ) { return ( $v > 0 ) ? ( 1 / $v ) : 0; }, $aD ) );

// 			$i++;
// 			echo $i . " of " . $iAmountOfVertices . "<br />";
// 			if( $i == 500 ) {
// 				$time = microtime();
// 				$time = explode(" ", $time);
// 				$time = $time[1] + $time[0];
// 				$endtime = $time;
// 				$totaltime = ($endtime - $begintime);
// 				echo $totaltime. ' seconds.';
// 
// 				exit;
// 			}
		}
	}

	/**
	* BetweennessCentralities to triple store
	*
	*
	*/
	public function saveCentralities () {

// 		foreach ( $this->aAPIs as $sIRI => $iIndex ) {
// 
// 			# Write results to triple Store
// 			$aData = array( $sIRI => array(
// 				"oml:betweennessCentrality" => $this->aBetweennessCentralities[ $iIndex ],
// 				"oml:closenessCentrality" => $this->aClosenessCentralities[ $iIndex ]
// 			) );
// 
// 			$conf = array( 'ns' => array(
// 							'rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
// 							'owl' => 'http://www.w3.org/2002/07/owl#',
// 							'oml' => 'http://www.ict-omelette.eu/schema.rdf#'
// 			) );
// 			$serializer = ARC2::getRDFXMLSerializer($conf);
// 
// 			$rdfxml = $serializer->getSerializedIndex($index);
// 			$this->oTripleStore->append($rdfxml);
// 
// 		}

	}

	/**
	* BetweennessCentrality Ranking Function
	*
	* This function calculates the betweenness centrality of
	* the matched APIs and returns them as their ranking score.
	*
	* @uses romkingComponentSet::$aMashups
	* @uses romkingComponentSet::$aAPIs
	*
	* @param object component
	* @return integer
	*/
	private function componentRankingFunction_betweennessCentrality ( $oComponent ) {

// 		# if adjacency matrix is not yet constructed, do it
// 		if( !$this->aAPIs )
// 			$this->constructAdjacencyMatrixOfAPIMashupGraph ();
// 
// 		# if betweenness centralities have not been calculated, do it
// 		if( !$this->aBetweennessCentralities )
// 			$this->calculateBetweennessAndClosenessCentralities();
// 
// 		# return the betweenness centrality as score
// 		return $this->aBetweennessCentralities[ $oComponent->sIRI ];


		# build SPARQL query
		$sSPARQLQuery  = "\r\n";
		$sSPARQLQuery .= "PREFIX  rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>\r\n";
		$sSPARQLQuery .= "PREFIX  oml: <http://www.ict-omelette.eu/schema.rdf#>\r\n";
		$sSPARQLQuery .= "SELECT ?betweennessCentrality\r\n";
		$sSPARQLQuery .= "\tWHERE {\r\n";
		$sSPARQLQuery .= "\t\t<" . $oComponent->sIRI . "> oml:betweennessCentrality ?betweennessCentrality.\r\n";
		$sSPARQLQuery .= "\t}";

		# fetch result
		$aRows = $this->oTripleStore->query( $sSPARQLQuery );
		$aRows = $aRows->getRows();

		# return result
		return $aRows[ 0 ][ 'betweennessCentrality' ];
		
	}

	/**
	* ClosenessCentrality Ranking Function
	*
	* This function calculates the closeness centrality of
	* the matched APIs and returns them as their ranking score.
	*
	* @uses romkingComponentSet::$aMashups
	* @uses romkingComponentSet::$aAPIs
	*
	* @param object component
	* @return integer
	*/
	private function componentRankingFunction_closenessCentrality ( $oComponent ) {

// 		# if adjacency matrix is not yet constructed, do it
// 		if( !$this->aAPIs )
// 			$this->constructAdjacencyMatrixOfAPIMashupGraph ();
// 
// 		# if closeness centralities have not been calculated, do it
// 		if( !$this->aClosenessCentralities )
// 			$this->calculateBetweennessAndClosenessCentralities();
// 
// 		# return the betweenness centrality as score
// 		return $this->aClosenessCentralities[ $oComponent->sIRI ];

		# build SPARQL query
		$sSPARQLQuery  = "\r\n";
		$sSPARQLQuery .= "PREFIX  rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>\r\n";
		$sSPARQLQuery .= "PREFIX  oml: <http://www.ict-omelette.eu/schema.rdf#>\r\n";
		$sSPARQLQuery .= "SELECT ?closenessCentrality\r\n";
		$sSPARQLQuery .= "\tWHERE {\r\n";
		$sSPARQLQuery .= "\t\t<" . $oComponent->sIRI . "> oml:closenessCentrality ?closenessCentrality.\r\n";
		$sSPARQLQuery .= "\t}";

		# fetch result
		$aRows = $this->oTripleStore->query( $sSPARQLQuery );
		$aRows = $aRows->getRows();

		# return result
		return $aRows[ 0 ][ 'closenessCentrality' ];
		
	}

	/**
	* Calculation of Eigenvector Centralities
	*
	* Power iteration method
	*
	* @uses romkingRanking::$aEigenvectorCentralities
	* @return array
	*/
	private function calculateEigenvectorCentralities ( ) {

// 		$time = microtime();
// 		$time = explode(' ', $time);
// 		$time = $time[1] + $time[0];
// 		$begintime = $time;

		# Number of iterations
		$iAmountOfIterations = 30;

		# union of Mashups and APIs
		# i.e. the set of vertices of the graph
		# NOTE: For this we will need the transposed
		# Matrix for dealing with directed graphs
		$aBigV = array();
		foreach( $this->aGraph as $iIndex => $aNeighbours ) {
			$aBigV[ $iIndex ] = array();
			$iAmountOfNeighbours = sizeof( $aNeighbours );
			foreach( $aNeighbours as $iNeighbourIndex => $iNeighbour )
				$aBigV[ $iIndex ][ $iNeighbourIndex ] = array( 
					'dir' => $iNeighbour,
					'weight' => 1 / $iAmountOfNeighbours
				);
		}

		# amount of vertices in the graph
		$iAmountOfVertices = sizeof( $aBigV );

		# CE[v] = 0, v€V
		$this->aEigenvectorCentralities = array();

		# b(0)
		$aB = array( 0 => array() );
		foreach( $aBigV as $iIndex => $aNeighbours )
			$aB[ 0 ][ $iIndex ] = 1 / $iAmountOfVertices; // 0;

		//$aB[0][0] = 1;

		# Power iteration
		for( $i = 1; $i <= $iAmountOfIterations; $i++ ) {

			foreach( $aBigV as $iIndex => $aNeighbours )
				foreach( $aNeighbours as $aNeighbour )
					$aB[ $i ][ $aNeighbour[ 'dir' ] ] += $aNeighbour[ 'weight' ] * $aB[ ( $i - 1 ) ][ $iIndex ];

			$iNormFactor = array_sum( $aB[ $i ] );
			
// 			foreach( $aB [ $i ] as $iIndex => $iValue )
// 				$aB[ $i ][ $iIndex ] = $iValue / $iNormFactor;

			# echo $i . ". iteration<br />";
		}

// 		$time = microtime();
// 		$time = explode(" ", $time);
// 		$time = $time[1] + $time[0];
// 		$endtime = $time;
// 		$totaltime = ($endtime - $begintime);
// 		echo $totaltime. ' seconds needed for ' . $iAmountOfIterations . ' iterations.';

		$this->aEigenvectorCentralities = $aB[ $iAmountOfIterations ];
	}

	/**
	* EigenvectorCentrality Ranking Function
	*
	* This function calculates the eigenvector centrality of
	* the matched APIs and returns them as their ranking score.
	*
	* @uses romkingComponentSet::$aEigenvectorCentralities
	* @uses romkingComponentSet::$aAPIs
	*
	* @param object component
	* @return integer
	*/
	private function componentRankingFunction_eigenvectorCentrality ( $oComponent ) {

		# if adjacency matrix is not yet constructed, do it
		if( !$this->aAPIs )
			$this->constructAdjacencyMatrixOfAPIMashupGraph ();

		# if betweenness centralities have not been calculated, do it
		if( !$this->aEigenvectorCentralities )
			$this->calculateEigenvectorCentralities();

		# return the betweenness centrality as score
		return $this->aEigenvectorCentralities[ $this->aAPIs[ $oComponent->sIRI ] ];
		
	}

	/**
	* Plain User Rating Ranking Function
	*
	* This function grabs the user rating of each matched API
	* from the triple store.
	*
	* @param object
	*		current Component
	* @return integer
	*		score
	*/
	private function componentRankingFunction_plainUserRating ( $oComponent ) {

		# build SPARQL query
		$sSPARQLQuery  = "\r\n";
		$sSPARQLQuery .= "PREFIX  rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>\r\n";
		$sSPARQLQuery .= "PREFIX  oml: <http://www.ict-omelette.eu/schema.rdf#>\r\n";
		$sSPARQLQuery .= "SELECT ?rating\r\n";
		$sSPARQLQuery .= "\tWHERE {\r\n";
		$sSPARQLQuery .= "\t\t?api oml:api <" . $oComponent->sIRI . "> .\r\n";
		$sSPARQLQuery .= "\t\t?api oml:rating ?rating .\r\n";
		$sSPARQLQuery .= "\t}";

		# fetch result
		$aRows = $this->oTripleStore->query( $sSPARQLQuery );
		$aRows = $aRows->getRows();

		# return result
		return (float) $aRows[ 0 ][ 'rating' ];

	}

	/**
	* Stackoverflow.com Function
	*
	* This function grabs the amount of results on Google's
	* search engine for the query "site:stackoverflow.com [Name of the API]"
	*
	* @param object
	*		current Component
	* @return integer
	*		score
	*/
	private function componentRankingFunction_stackOverflow ( $oComponent ) {
		
		# run jifv's ruby scriptito with
		# the components name
		$sOutput = shell_exec( "ruby ../ruby/google site:stackoverflow.com \\\"" . $oComponent->aData[ 'title' ] . "\\\"" );

		# return result
		return (int) str_replace( ",", "", substr( $sOutput, 0, strpos( $sOutput, "\n" ) ) );

	}

	/**
	* Programmersheaven.com Function
	*
	* This function grabs the amount of results on Google's
	* search engine for the query "site:programmersheaven.com [Name of the API]"
	*
	* @param object
	*		current Component
	* @return integer
	*		score
	*/
	private function componentRankingFunction_programmersHeaven ( $oComponent ) {

		# run jifv's ruby scriptito with
		# the components name
		$sOutput = shell_exec( "ruby ../ruby/google site:programmersheaven.com \\\"" . $oComponent->aData[ 'title' ] . "\\\"" );

		# return result
		return (int) str_replace( ",", "", substr( $sOutput, 0, strpos( $sOutput, "\n" ) ) );

	}

	/**
	* Google.com Function
	*
	* This function grabs the amount of results on Google's
	* search engine for the query "[Name of the API]"
	*
	* @param object
	*		current Component
	* @return integer
	*		score
	*/
	private function componentRankingFunction_googleSearch ( $oComponent ) {

		# run jifv's ruby scriptito with
		# the components name
		$sOutput = shell_exec( "ruby ../ruby/google \\\"" . $oComponent->aData[ 'title' ] . "\\\"" );

		# return result
		return (int) str_replace( ",", "", substr( $sOutput, 0, strpos( $sOutput, "\n" ) ) );

	}			
}
