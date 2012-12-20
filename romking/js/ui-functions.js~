function romkingHandleSearchInput () {
	
	$( '#romkingSearchResultPanel' ).html('<em>Loading search results ...</em>');

	$.ajax({
		type:		'POST',
		url:		'ajax-search-results.php',
		data:		{ query: $( '#romkingQuery' ).val() },
		dataType:	'json',
		success:	function( romkingSearchResults ) {
					
					var sHTML = "";
			
					sHTML += "<h2>Search results for '" + $( '#romkingQuery' ).val() + "' (" + romkingSearchResults.length + " matches)</h2>";
					sHTML += "<ol>";
					
					for ( var i in romkingSearchResults ) {	
						sHTML += "<li><h3>";
						sHTML += "(" + ( parseInt(i) + 1 ) + ") ";
						sHTML += romkingSearchResults[ i ].obj.title;
						sHTML += "</h3>";
						sHTML += romkingSearchResults[ i ].obj.IRI;
						sHTML += "<br />";
						sHTML += "Score: " + romkingSearchResults[ i ].score.overall;
						sHTML += "<br />";
						sHTML += "Tags:";
						
						for ( var j in romkingSearchResults[ i ].obj.tags )
							sHTML += "&nbsp;<span style=\"cursor: pointer\" onclick=\"$( '#romkingQuery' ).val('" + romkingSearchResults[ i ].obj.tags[j].title + "')\">" + romkingSearchResults[ i ].obj.tags[j].title + "</span>";
						
						sHTML += "<br />";
						sHTML += "Data Formats:";
						
						for ( var j in romkingSearchResults[ i ].obj.dataFormats )
							sHTML += "&nbsp;" + romkingSearchResults[ i ].obj.dataFormats[ j ];
						
						sHTML += "</li>";
					}
					
					sHTML += "</ol>";
					
					$( '#romkingSearchResultPanel' ).html( sHTML );
					
				},
		error:		function () {
					
					$( '#romkingSearchResultPanel' ).html( "No results for this query." );
				}
	});
	
}