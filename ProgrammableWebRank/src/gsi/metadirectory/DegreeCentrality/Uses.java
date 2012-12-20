package gsi.metadirectory.DegreeCentrality;

import java.util.Arrays;
import java.util.HashMap;
//import java.util.List;
//
//import org.openrdf.model.Value;
//import org.openrdf.query.BindingSet;
//import org.openrdf.query.QueryLanguage;
//import org.openrdf.query.TupleQuery;
//import org.openrdf.query.TupleQueryResult;
//import org.openrdf.repository.Repository;
//import org.openrdf.repository.RepositoryConnection;

public class Uses {
	
	static Object [] union;
	public static String queryPrefix = "PREFIX rdf:<http://www.w3.org/1999/02/22-rdf-syntax-ns#>"
		+ "PREFIX rdfs:<http://www.w3.org/2000/01/rdf-schema#>"
		+ "PREFIX dc:<http://purl.org/dc/elements/1.1/>"
		+ "PREFIX ctag:<http://commontag.org/ns#>"
		+ "PREFIX limon:<http://www.ict-omelette.eu/schema.rdf#>"
		+ "PREFIX src:<http://www.programmableweb.com/>"
		+ "PREFIX api:<http://www.programmableweb.com/api/>"
		+ "PREFIX mashup:<http://www.programmableweb.com/mashup/>"
		+ "PREFIX tags:<http://www.ict-omelette.eu/omr/tags/>";
	
	/**
	 * @return
	 */
//	public static int getNumUses(Repository myRepository, RepositoryConnection con) {
//		int numUses = 0;
//		try {
//			con = myRepository.getConnection();
//			try {
//				String queryString = queryPrefix
//						+ "SELECT (COUNT (?num) AS ?total) WHERE {"
//						+ "?num limon:uses ?o. "
//						+ "}";
//				
//				TupleQuery tupleQuery = con.prepareTupleQuery(QueryLanguage.SPARQL, queryString);
//				TupleQueryResult result = tupleQuery.evaluate();
//				try {
//					List<String> bindingNames = result.getBindingNames();
//					while (result.hasNext()) {
//						BindingSet bindingSet = result.next();
//						Value uses = bindingSet.getValue(bindingNames.get(0));
//						numUses = Integer.parseInt(uses.stringValue());
//					}
//				} finally {
//					result.close();
//				}
//			} finally {
//				con.close();
//			}
//		} catch (Exception e) {
//			e.printStackTrace();
//		}
//		return numUses;		
//	}
	
	/**
	 * @param uses
	 */
	public static void union(String [][] uses) {
		union = new String[uses.length*2];
		int a=0;
		for(int i=0; i<uses.length;i++) {
				union[a]=uses[i][0];
				a++;
				union[a]=uses[i][1];
				a++;
		}
		Arrays.sort(union);
	}

	
	/**
	 * 
	 */
	public static HashMap<String, Integer> count(String[][] uses ,HashMap<String, Integer> usesCount){
		int a=0, count = 1;
		union = new String[uses.length*2];
		
		for(int i=0; i<uses.length;i++) {
				union[a]=uses[i][0];
				a++;
				union[a]=uses[i][1];
				a++;
		}
		
		Arrays.sort(union);
		for (int i = 1; i < union.length; i++) {
			if(union[i].equals(union[i-1])){
				count++;
			} else {
				usesCount.put(union[i-1].toString(), count);
				count=1;
			}
		}
		return usesCount;
	}
	
}
