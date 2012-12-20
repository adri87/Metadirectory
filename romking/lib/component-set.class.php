<?php
/**
*
* @author Tilo Zemke <zeti@hrz.tu-chemnitz.de>
* @version 1.0
* @module rOMking - OMELETTE Component Ranking
* @deprec Represents a set of components
*
*/
class romkingComponentSet
{

	/***********************************************
	* =CONST
	***********************************************/
	
	/***********************************************
	* =VAR
	***********************************************/
	
	/**
	* Set size
	*
	* This defines the size of each set. If set to '0' the
	* set seize will be unlimited.
	*
	* @var int
	*/
	protected $iSetSize = 0;
	
	/**
	* Current Set size
	*
	* The amount of found matches is stored here.
	*
	* @var int
	*/
	protected $iRealSetSize;
	
	/**
	* Default search operator
	*
	* This string defines the default operator that joins
	* multiple terms in a user's search query.
	* Possible values:
	* 	- "OR"
	*	- "AND"
	*
	* @var string
	*/
	protected $sDefaultSearchQueryOperator = "AND";

	/**
	* Matching algorithm
	*
	* This var defines the methodology of selecting
	* components from the triple store, i.e. the SPARQL 
	* query's WHERE definition.
	*
	* @var array
	*/
	protected $aMatchingMethod = array(
						"api-tags",
						"api-name",
						"api-description"
					);
	
	/**
	* Query string given by user
	*
	* This string is given by the user as input.
	*
	* @var string
	*/
	protected $sQueryExpression;
	
	/**
	* Parsed Query given by user
	*
	* This array contains the elementary terms of the user's
	* query and their respective search operators and search
	* dimension, e.g. api-name, data format, etc.
	*
	* @var array
	*/
	protected $aUserQuery;
	
	/**
	* Current Set
	*
	* This indexed array contains all objects (romkingComponent)
	* of the components in the current set
	*
	* @var array
	*/
	public $aSet;

	/**
	* Triple Store
	*
	* This is the reference to the triple store
	*
	* @var object
	*/
	protected $oTripleStore;
	
	/**
	* SPARQL Query
	*
	* This variable stores the final SPARQL-Query of
	* the matchmaking magical process.
	*
	* @var string
	*/
	protected $sSPARQLQuery;	
	
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
	public function __construct ( $sQueryExpression ) {
	
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
		
		# return associative array
		return $this->aSet;
	
	}

	/**
	* User Query analysis
	*
	* This function analyzes the user's input and parses it.
	* The results will be stored as an array in
	* {@link romkingComponentSet::$aUserQuery $aUserQuery}.
	*
	* @uses romkingComponentSet::$sQueryExpression
	* @uses romkingComponentSet::$aUserQuery
	*
	* @return array
	*/
	protected function analyzeUserInputString () {

		# split query string on white space
		$aUserQuery = explode( " ", $this->sQueryExpression );// str_getcsv( $this->sQueryExpression, " " ); //

		# look for operators
		# note: the constructor has strtolower()ed the string
		for( $i = 0; $i < sizeof( $aUserQuery ); $i++ )
			if (	$aUserQuery[ $i ] == "or" ||
				$aUserQuery[ $i ] == "and" )
					$this->aUserQuery[ $i ] = array(
									"operator" => strtoupper( $aUserQuery[ $i++ ] ),
									"term" => $aUserQuery[ $i ],
									"type" => null
								);
			else
				$this->aUserQuery[ $i ] = array(
								"operator" => $this->sDefaultSearchQueryOperator,
								"term" => $aUserQuery[ $i ],
								"type" => null
							);

		# look for type definitions in the search terms
		foreach( $this->aUserQuery as $iIndex => $aValue )
			if( preg_match ( "#:#", $aValue[ 'term' ] ) )
				list( $this->aUserQuery[ $iIndex ][ 'type' ], $this->aUserQuery[ $iIndex ][ 'term' ] ) = split( ":", $aValue[ 'term' ] );

		# return resulting array
		return $this->aUserQuery;

	}
	
	/***********************************************
	* =FETCH =MATCH =MAKING =MAGIC
	***********************************************/

	/**
	* Build SPARQL Query
	*
	* This function is the core of the matchmaking process. It
	* defines the SPARQL query to fetch all of the relevant components
	* from the triple store based on the user's query
	*
	* @uses romkingComponentSet::$aMatchingMethod
	* @uses romkingComponentSet::$aUserQuery
	* @uses romkingComponentSet::$sSPARQLQuery
	* @return string
	*/
	protected function buildSPARQLQuery () {

		# initialize the query
		# - defining namespaces
		# - selecting the identifier (IRI) as well as
		# - the degree-centrality of the component
		$this->sSPARQLQuery  = "\r\n";
		$this->sSPARQLQuery .= "PREFIX  rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>\r\n";
		$this->sSPARQLQuery .= "PREFIX  rdfs: <http://www.w3.org/2000/01/rdf-schema#>\r\n";
		$this->sSPARQLQuery .= "PREFIX  oml: <http://www.ict-omelette.eu/schema.rdf#>\r\n";
		$this->sSPARQLQuery .= "PREFIX  omltags: <http://www.ict-omelette.eu/omr/tags/>\r\n";
		$this->sSPARQLQuery .= "PREFIX  ctag: <http://commontag.org/ns#>\r\n";
		$this->sSPARQLQuery .= "PREFIX  dc: <http://purl.org/dc/elements/1.1/>\r\n";
		$this->sSPARQLQuery .= "SELECT ?IRI\r\n"; // (count(distinct ?mashup) as ?degreeCentrality)
		$this->sSPARQLQuery .= "\tWHERE {\r\n";
		$this->sSPARQLQuery .= "\t\t?api oml:api ?IRI .\r\n";
		$this->sSPARQLQuery .= "\t\t?api rdf:type oml:Service .\r\n";
		$this->sSPARQLQuery .= "\t\t?api oml:dataFormat ?dataFormat .\r\n";
		$this->sSPARQLQuery .= "\t\t?api ctag:tagged ?apiTag .\r\n";
		$this->sSPARQLQuery .= "\t\t?api rdfs:label ?apiTitle .\r\n";
		$this->sSPARQLQuery .= "\t\t?api dc:description ?apiDescription .\r\n";
		$this->sSPARQLQuery .= "\t\t?mashup oml:uses ?api .\r\n";
		$this->sSPARQLQuery .= "\t\t?mashup ctag:tagged ?mashupTag .\r\n";
		
		# initializing OR-Filters and AND-Filters
		$aFilters = array(
					"OR" => array (),
					"AND" => array ()
				);

		# handling every search term in the user query
		foreach( $this->aUserQuery as $iIndex => $aQueryTerm ) {
			
			# add respective operator of current term to
			# the query string
			# $this->sSPARQLQuery .= " " . $aQueryTerm[ 'operator' ] . " ";

			if( $aQueryTerm[ 'type' ] )
				# the current term has a specific type, e.g. dataformat
				# connected to it. the following block will handle those
				# specific options
				switch( $aQueryTerm[ 'type' ] ) {
					default:
					break; case "dataformat":
						$this->sSPARQLQuery .= "\t\tFILTER regex( ?dataFormat, \"" . $aQueryTerm[ 'term' ] . "\", \"i\" ) .\r\n";
					break;
				}
			else {

				# "if" for every aspect of the general information
				# about the component that should be considered

				# auxiliary array for the necessary conditions
				$aCurrentConditions = array();

				# Tags of the API
				if( in_array( "api-tags", $this->aMatchingMethod ) )
					$aCurrentConditions[] = "?apiTag = omltags:" . $aQueryTerm[ 'term' ];

				# Tags of mashups using the API
				if( in_array( "mashup-tags", $this->aMatchingMethod ) )
					$aCurrentConditions[] = "?mashupTag = omltags:" . $aQueryTerm[ 'term' ];

				# Title of the API
				if( in_array( "api-name", $this->aMatchingMethod ) )
					$aCurrentConditions[] = "regex( ?apiTitle, \" " . $aQueryTerm[ 'term' ] . " \", \"i\" )";

				# Description of the API
				if( in_array( "api-description", $this->aMatchingMethod ) )
					$aCurrentConditions[] = "regex( ?apiDescription, \" " . $aQueryTerm[ 'term' ] . " \", \"i\" )";

				# Glueing Conditions
				$aFilters[ $aQueryTerm[ 'operator' ] ][] = implode( " || ", $aCurrentConditions );

			}
		}

		# get out the deadpool tagged APIs
		$this->sSPARQLQuery .=  "\t\tFILTER NOT EXISTS { ?api ctag:tagged omltags:deadpool }\r\n";#"\t\tFILTER (\r\n\t\t\t?apiTag != omltags:deadpool\r\n\t\t\t).\r\n";

		# concatenation of the filter expressions
		if( $aFilters[ 'OR' ] )
			$this->sSPARQLQuery .= "\t\tFILTER (\r\n\t\t\t( " . implode( " ) ||\r\n\t\t\t( ", $aFilters[ 'OR' ] ) ." )\r\n\t\t) .\r\n";
		if( $aFilters[ 'AND' ] )
			$this->sSPARQLQuery .= "\t\tFILTER (\r\n\t\t\t( " . implode( " ) &&\r\n\t\t\t( ", $aFilters[ 'AND' ] ) ." )\r\n\t\t) .\r\n";

		# finalize the query
		# Grouping by the component's identifier
		# optional limit
		$this->sSPARQLQuery .= "\t}\r\nGROUP BY ?IRI";
		if( $this->iSetSize )
			$this->sSPARQLQuery .= " LIMIT " . $this->iSetSize; # ORDER BY DESC(?score)";
		

	}

	/**
	* Execute SPARQL Query
	*
	* This function sends the query to the triple store and
	* receives the results which it handles afterwards. It
	* returns {@link romkingComponentSet::$aSet $aSet}.
	*
	* @uses romkingComponentSet::$oTripleStore
	* @uses romkingComponentSet::$sSPARQLQuery
	* @return array
	*/
	protected function executeSPARQLQuery () {

		# fetch result
		$aRows = $this->oTripleStore->query( $this->sSPARQLQuery );
		$aRows = $aRows->getRows();

		# store current set size
		$this->iRealSetSize = sizeof( $aRows );
		
		# handle found data
		foreach( $aRows as $iKey => $aResult ) {
			$this->aSet[ $iKey ][ 'obj' ] = new romkingComponent( $aResult[ 'IRI' ] );
			$this->aSet[ $iKey ][ 'score' ] = array();
		}

	}
	
	/***********************************************
	* =OUTPUT
	***********************************************/

	/**
	* Magic Method __toString()
	*
	* Will return 
	* {@link romkingComponentSet::$aSet $aSet}
	* as JSON-encoded object.
	*
	* @return string
	*/
	public function __toString ( ) {
		
		$aReturn = $this->aSet;
		foreach( $this->aSet as $iKey => $aValue ) 
			$aReturn[ $iKey ][ 'obj' ] = json_decode( (string) $this->aSet[ $iKey ][ 'obj' ] );
		
		return json_encode( $aReturn );
	}
	
}
?>