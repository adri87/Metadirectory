<?php
// Including the HTML header file
// Starting output to browser
include("../tpl/header.html.tpl");
?>
		<fieldset id="romkingQueryFieldset">
			<!--<legend>Search Web Services</legend>-->
			<form action="handle-search-request.php" method="post">
				<input id="romkingQuery" name="romkingQuery" />
				<input type="button" onclick="romkingHandleSearchInput()" id="romkingSubmitQueryButton" value="Search" />
			</form>
		</fieldset>
		<div id="romkingSearchResultPanel">
			<span style="font-style: italic;">Please type a search query.</span>
		</div>

		<script type="text/javascript">
			$( '#romkingSubmitQueryButton' ).button();
		</script>
<?php
// Finishing HTML with footer template file
include("../tpl/footer.html.tpl");
?>