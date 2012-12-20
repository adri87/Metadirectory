<?php

# load libraries etc.
include_once( "../inc/import.inc.php" );

# perform Search by creating new set of components
$oSet = new romkingRanking ( $_POST[ 'query' ] );

# output results
echo $oSet;

?>