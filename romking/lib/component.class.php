<?php
/**
*
* @author Tilo Zemke <zeti@hrz.tu-chemnitz.de>
* @version 1.0
* @module rOMking - OMELETTE Component Ranking
* @deprec Represents one component and all data concerning it.
*
*/
class romkingComponent
{

	/***********************************************
	* =CONST
	***********************************************/
	
	/***********************************************
	* =VAR
	***********************************************/
	
	/**
	* Identifier
	*
	* IRI (Internationalized Resource Identifier - RFC 3987)
	* used as identifier for current component
	*
	* @var string
	*/
	public $sIRI;
	
	/**
	* Data Array
	*
	* Associative array which contains all information
	* on the current component
	*
	* @var array
	*/
	public $aData;

	/**
	* Triple Store
	*
	* This is the reference to the triple store
	*
	* @var object
	*/
	private $oTripleStore;
	
	/***********************************************
	* =FUNCTION
	***********************************************/
	
	/**
	* Constructor
	*
	* stores given IRI and calls readDataFromTripleStore()
	* returns associative array of gathered information
	*
	* @global romkingStore - global triple store instance
	* @param string sIRI
	* @return array
	*/	
	public function __construct ( $sIRI ) {
	
		# reference to triple store
		global $romkingStore;
		$this->oTripleStore = $romkingStore;

		# store given IRI
		$this->sIRI = $sIRI;
		$this->aData[ 'IRI' ] = $this->sIRI;
		
		# fetch data from triple store and handle it
		# properly
		$this->readDataFromTripleStore();
		
		# return associative array
		return $this->aData;
	
	}
	
	/**
	* Read data from triple store
	*
	* reads data from triple store component, which
	* is identified by its IRI, and stores it into the
	* aData array.
	*
	* @return array
	*/
	private function readDataFromTripleStore ( ) {

		# set up SPARQL Query String
		$sSparqlQuery = "SELECT ?APIName ?api
					WHERE { 
						?api <http://www.ict-omelette.eu/schema.rdf#api> <" . $this->sIRI . "> .
						?api <http://www.w3.org/2000/01/rdf-schema#label> ?APIName
					}"; # .						?api <http://www.ict-omelette.eu/schema.rdf#provider> ?provider
		
		# fetch result
		$aResult = $this->oTripleStore->query( $sSparqlQuery );
		$aResult = $aResult->getRows();
		
		# handle found data
		$this->aData[ 'title' ] = $aResult[0]['APIName'];
		$this->aData[ 'vendor' ] = $aResult[0]['provider'];
		$this->aData[ 'source' ] = $aResult[0]['api'];
		$this->aData[ 'tags' ] = array( );
		$this->aData[ 'dataFormats' ] = array( );

		# find tags
		$this->fetchTagsFromTripleStore();

		# figure out data formats of current component
		$this->fetchDataFormatsFromTripleStore();
	
	}

	/**
	* Fetch all tags of current component
	*
	* This function queries the triple store for all tags
	* related to the current component and stores them in $aData
	*
	* @return array
	*/
	private function fetchTagsFromTripleStore ( ) {

		# set up SPARQL Query String
		$sSparqlQuery = "SELECT ?TagName
					WHERE { 
						?api <http://www.ict-omelette.eu/schema.rdf#api> <" . $this->sIRI . "> .
						?api <http://commontag.org/ns#tagged> ?TagName
					}";
		
		# fetch result
		$aResult = $this->oTripleStore->query( $sSparqlQuery );
		$aResult = $aResult->getRows();

		# process results
		foreach( $aResult as $iKey => $aValue )
			array_push( $this->aData[ 'tags' ], array( "IRI" => $aValue[ 'TagName' ], "title" => $this->escapeTagName( $aValue[ 'TagName' ] ) ) );

		# return findings
		return $this->aData[ 'tags' ];

	}

	/**
	* Fetch all data formats
	*
	* This function queries the triple store for all data formats
	* related to the current component and stores them in $aData
	*
	* @return array
	*/
	private function fetchDataFormatsFromTripleStore ( ) {

		# set up SPARQL Query String
		$sSparqlQuery = "SELECT ?dataFormat
					WHERE { 
						?api <http://www.ict-omelette.eu/schema.rdf#api> <" . $this->sIRI . "> .
						?api <http://www.ict-omelette.eu/schema.rdf#dataFormat> ?dataFormat
					}";
		
		# fetch result
		$aResult = $this->oTripleStore->query( $sSparqlQuery );
		$aResult = $aResult->getRows();

		# process results
		foreach( $aResult as $iKey => $aValue )
			array_push( $this->aData[ 'dataFormats' ], $aValue[ 'dataFormat' ] );

		# return findings
		return $this->aData[ 'dataFormats' ];

	}

	/**
	* Magic Method __toString()
	*
	* Will return aData as JSON-encoded object
	*
	* @return string
	*/
	public function __toString ( ) {

		return json_encode( $this->aData );

	}

	/**
	* Escaping Tag Names
	*
	* This function turns a IRI-shaped tags into better
	* readable strings
	*
	* @param string $sIRI
	* @return string
	*/
	private function escapeTagName ( $sIRI ) {

		return preg_replace( "#http:\/\/www\.ict-omelette\.eu\/omr\/tags\/#Ui", "", $sIRI );

	}
	
}
?>