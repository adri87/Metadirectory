package gsi.metadirectory.repository;

import gsi.metadirectory.ClosenessCentrality.ClosenessCentrality;
import gsi.metadirectory.DegreeCentrality.DegreeCentrality;
import gsi.metadirectory.Social.Social;
import gsi.metadirectory.config.Configuration;
import gsi.metadirectory.config.MyAuthenticator;

import java.io.BufferedReader;
import java.io.ByteArrayInputStream;
import java.io.InputStreamReader;
import java.io.OutputStreamWriter;
import java.net.Authenticator;
import java.net.URL;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.GregorianCalendar;
import java.util.List;

import javax.net.ssl.HttpsURLConnection;

import org.openrdf.model.Value;
import org.openrdf.query.BindingSet;
import org.openrdf.query.TupleQueryResult;
import org.openrdf.query.impl.TupleQueryResultBuilder;
import org.openrdf.query.resultio.sparqlxml.SPARQLResultsXMLParser;


public class OMRRepository implements IRepository{
	
	private URL repository;
	private HttpsURLConnection con;
	private Object[] apis;
	private Object[] mashups;
	private String[][] uses;
	private String queryPrefix = "PREFIX rdf:<http://www.w3.org/1999/02/22-rdf-syntax-ns#>"
			+ "PREFIX rdfs:<http://www.w3.org/2000/01/rdf-schema#>"
			+ "PREFIX dc:<http://purl.org/dc/elements/1.1/>"
			+ "PREFIX ctag:<http://commontag.org/ns#>"
			+ "PREFIX limon:<http://www.ict-omelette.eu/schema.rdf#>"
			+ "PREFIX src:<http://www.programmableweb.com/>"
			+ "PREFIX api:<http://www.programmableweb.com/api/>"
			+ "PREFIX mashup:<http://www.programmableweb.com/mashup/>"
			+ "PREFIX tags:<http://www.ict-omelette.eu/omr/tags/>";

	/* (non-Javadoc)
	 * @see gsi.metadirectory.main.IRepository#init()
	 */
	public void init() {
		String omrDataBase = Configuration.getInstance().getProperty("omrDataBase");
		try {
			repository = new URL(omrDataBase);
			extractApis();
			extractMashups();
			getNumApisMashups();
			usesRelations();
		} catch (Exception e) {
			e.printStackTrace();
		}		
	}

	/* (non-Javadoc)
	 * @see gsi.metadirectory.main.IRepository#extractApis()
	 */
	public void extractApis() {
		ArrayList <Value> tempApis = new ArrayList <Value>();
		String content = "";
		try {
			Authenticator.setDefault(new MyAuthenticator());
			con = (HttpsURLConnection) repository.openConnection();
			String queryString = queryPrefix
					+ "SELECT DISTINCT ?api WHERE {"
					+ "?s ?p ?api. "
					+ "FILTER regex(str(?api), \"http://www.programmableweb.com/api/\")"
					+ "}";
			 
			con.setDoOutput(true);
	        con.setRequestProperty("Content-Type","application/x-www- form-urlencoded");
	        con.setRequestProperty("Content-length",String.valueOf(queryString.length()));
	        con.setRequestMethod("POST");
	        OutputStreamWriter post = new OutputStreamWriter(con.getOutputStream());
	        post.write(queryString);
	        post.flush();
	        
	        BufferedReader in = new BufferedReader(new InputStreamReader(con.getInputStream()));
	        String inputLine;
	        while ((inputLine = in.readLine()) != null) {
	        	content += inputLine;
	        }
	        
	        SPARQLResultsXMLParser xmlRes = new SPARQLResultsXMLParser();
	        TupleQueryResultBuilder build = new TupleQueryResultBuilder();
	        xmlRes.setTupleQueryResultHandler(build);

	        ByteArrayInputStream is = new ByteArrayInputStream(content.getBytes("UTF-8"));
	        xmlRes.parse(is);

	        TupleQueryResult tupleRes = build.getQueryResult();
	        List<String> bindingNames = tupleRes.getBindingNames();
	        while (tupleRes.hasNext()) {
	           BindingSet bindingSet = tupleRes.next();
				Value api = bindingSet.getValue(bindingNames.get(0));
				tempApis.add(api);
	        }
			mashups = tempApis.toArray();
			post.close();
			in.close();
			con.disconnect();
		} catch (Exception e) {
			e.printStackTrace();
		}		
	}

	/* (non-Javadoc)
	 * @see gsi.metadirectory.main.IRepository#extractMashups()
	 */
	public void extractMashups() {
		ArrayList <Value> tempMashups = new ArrayList <Value>();
		String content = "";
		try {
			Authenticator.setDefault(new MyAuthenticator());
			con = (HttpsURLConnection) repository.openConnection();
			String queryString = queryPrefix
						+ "SELECT DISTINCT ?mashups WHERE {"
						+ "?mashups ?p ?o. "
						+ "FILTER regex(str(?mashups), \"http://www.programmableweb.com/mashup/\")"
						+ "}";
			 
			con.setDoOutput(true);
	        con.setRequestProperty("Content-Type","application/x-www- form-urlencoded");
	        con.setRequestProperty("Content-length",String.valueOf(queryString.length()));
	        con.setRequestMethod("POST");
	        OutputStreamWriter post = new OutputStreamWriter(con.getOutputStream());
	        post.write(queryString);
	        post.flush();
	        
	        BufferedReader in = new BufferedReader(new InputStreamReader(con.getInputStream()));
	        String inputLine;
	        while ((inputLine = in.readLine()) != null) {
	        	content += inputLine;
	        }
	        
	        SPARQLResultsXMLParser xmlRes = new SPARQLResultsXMLParser();
	        TupleQueryResultBuilder build = new TupleQueryResultBuilder();
	        xmlRes.setTupleQueryResultHandler(build);

	        ByteArrayInputStream is = new ByteArrayInputStream(content.getBytes("UTF-8"));
	        xmlRes.parse(is);

	        TupleQueryResult tupleRes = build.getQueryResult();
	        List<String> bindingNames = tupleRes.getBindingNames();
	        while (tupleRes.hasNext()) {
	           BindingSet bindingSet = tupleRes.next();
				Value mashup = bindingSet.getValue(bindingNames.get(0));
				tempMashups.add(mashup);
	        }
			apis = tempMashups.toArray();
			post.close();
			in.close();
			con.disconnect();
		} catch (Exception e) {
			e.printStackTrace();
		}		
	}

	/* (non-Javadoc)
	 * @see gsi.metadirectory.main.IRepository#usesRelations()
	 */
	public void usesRelations() {
		String content = "";
		int i=0, size = getNumUses();
		uses = new String[size][2];
		try {
			Authenticator.setDefault(new MyAuthenticator());
			con = (HttpsURLConnection) repository.openConnection();
			String queryString = queryPrefix
							+ "SELECT ?mashup ?api WHERE {"
							+ "?mashup limon:uses ?api. "
							+ "}";
			 
			con.setDoOutput(true);
	        con.setRequestProperty("Content-Type","application/x-www- form-urlencoded");
	        con.setRequestProperty("Content-length",String.valueOf(queryString.length()));
	        con.setRequestMethod("POST");
	        OutputStreamWriter post = new OutputStreamWriter(con.getOutputStream());
	        post.write(queryString);
	        post.flush();
	        
	        BufferedReader in = new BufferedReader(new InputStreamReader(con.getInputStream()));
	        String inputLine;
	        while ((inputLine = in.readLine()) != null) {
	        	content += inputLine;
	        }
	        
	        SPARQLResultsXMLParser xmlRes = new SPARQLResultsXMLParser();
	        TupleQueryResultBuilder build = new TupleQueryResultBuilder();
	        xmlRes.setTupleQueryResultHandler(build);

	        ByteArrayInputStream is = new ByteArrayInputStream(content.getBytes("UTF-8"));
	        xmlRes.parse(is);

	        TupleQueryResult tupleRes = build.getQueryResult();
	        List<String> bindingNames = tupleRes.getBindingNames();
	        while (tupleRes.hasNext()) {
	           BindingSet bindingSet = tupleRes.next();
				String mashup = bindingSet.getValue(bindingNames.get(0)).toString();
				String api = bindingSet.getValue(bindingNames.get(1)).toString();
				uses[i][0]=mashup;
				uses[i][1]=api;
				i++;
	        }
			post.close();
			in.close();
			con.disconnect();
		} catch (Exception e) {
			e.printStackTrace();
		}				
	}

	/* (non-Javadoc)
	 * @see gsi.metadirectory.main.IRepository#setDegreeCentrality()
	 */
	public void setDegreeCentrality() {
		Calendar calendario = new GregorianCalendar();
		System.out.println("Starting Degree Centrality: " + calendario.get(Calendar.HOUR) + ":" +
		            calendario.get(Calendar.MINUTE) + ":" + calendario.get(Calendar.SECOND) +
		            ":" + calendario.get(Calendar.MILLISECOND));  
		
		String omrDataBase = Configuration.getInstance().getProperty("omrDataBase");
		DegreeCentrality.calDegCent(apis, uses, omrDataBase);
		DegreeCentrality.calDegCent(mashups, uses, omrDataBase);
		
		Calendar calendario2 = new GregorianCalendar();
		System.out.println("Degree centrality is finished: " + calendario2.get(Calendar.HOUR) + ":" +
		            calendario2.get(Calendar.MINUTE) + ":" + calendario2.get(Calendar.SECOND) +
		            ":" + calendario2.get(Calendar.MILLISECOND));

		long diff = calendario2.getTimeInMillis() - calendario.getTimeInMillis();
		Calendar diferencia = new GregorianCalendar();
		diferencia.setTimeInMillis(diff);
		System.out.println("Time DC: " + diferencia.get(Calendar.MINUTE) + "min. " +
		            diferencia.get(Calendar.SECOND) + "sec. " + diferencia.get(Calendar.MILLISECOND) + "millisec."); 		
	}

	/* (non-Javadoc)
	 * @see gsi.metadirectory.main.IRepository#setClosenessCentrality()
	 */
	public void setClosenessCentrality() {
		Calendar calendario = new GregorianCalendar();
		System.out.println("Starting Closeness Centrality: " + calendario.get(Calendar.HOUR) + ":" +
		            calendario.get(Calendar.MINUTE) + ":" + calendario.get(Calendar.SECOND) +
		            ":" + calendario.get(Calendar.MILLISECOND));  
		
		String omrDataBase = Configuration.getInstance().getProperty("omrDataBase");
		ClosenessCentrality.closCent(apis, mashups, uses, apis.length+mashups.length, omrDataBase);
		
		Calendar calendario2 = new GregorianCalendar();
		System.out.println("Closeness centrality is finished: " + calendario2.get(Calendar.HOUR) + ":" +
		            calendario2.get(Calendar.MINUTE) + ":" + calendario2.get(Calendar.SECOND) +
		            ":" + calendario2.get(Calendar.MILLISECOND));

		long diff = calendario2.getTimeInMillis() - calendario.getTimeInMillis();
		Calendar diferencia = new GregorianCalendar();
		diferencia.setTimeInMillis(diff);
		System.out.println("Time CC: " + diferencia.get(Calendar.MINUTE) + "min. " +
		            diferencia.get(Calendar.SECOND) + "sec. " + diferencia.get(Calendar.MILLISECOND) + "millisec."); 		
	}

	/* (non-Javadoc)
	 * @see gsi.metadirectory.main.IRepository#setSocialRating()
	 */
	public void setSocialRating() {
		Calendar calendario = new GregorianCalendar();
		System.out.println("Starting Google Search Engine: " + calendario.get(Calendar.HOUR) + ":" +
		            calendario.get(Calendar.MINUTE) + ":" + calendario.get(Calendar.SECOND) +
		            ":" + calendario.get(Calendar.MILLISECOND));  
		
		String omrDataBase = Configuration.getInstance().getProperty("omrDataBase");
		Social.setSocial(apis, mashups, omrDataBase);
		
		Calendar calendario2 = new GregorianCalendar();
		System.out.println("Google Search Engine is finished: " + calendario2.get(Calendar.HOUR) + ":" +
		            calendario2.get(Calendar.MINUTE) + ":" + calendario2.get(Calendar.SECOND) +
		            ":" + calendario2.get(Calendar.MILLISECOND));

		long diff = calendario2.getTimeInMillis() - calendario.getTimeInMillis();
		Calendar diferencia = new GregorianCalendar();
		diferencia.setTimeInMillis(diff);
		System.out.println("Time GSO: " + diferencia.get(Calendar.MINUTE) + "min. " +
		            diferencia.get(Calendar.SECOND) + "sec. " + diferencia.get(Calendar.MILLISECOND) + "millisec."); 
		
	}

	/* (non-Javadoc)
	 * @see gsi.metadirectory.main.IRepository#getNumApisMashups()
	 */
	public void getNumApisMashups() {
		System.out.println("El número de apis es de: "+apis.length);
		System.out.println("El número de mashups es de: "+mashups.length);		
	}

	@Override
	public int getNumUses() {
		String content = "";
		int numUses = 0;
		try {
			Authenticator.setDefault(new MyAuthenticator());
			con = (HttpsURLConnection) repository.openConnection();
			String queryString = queryPrefix
						+ "SELECT (COUNT (?num) AS ?total) WHERE {"
						+ "?num limon:uses ?o. "
						+ "}";
				
			con.setDoOutput(true);
		    con.setRequestProperty("Content-Type","application/x-www- form-urlencoded");
		    con.setRequestProperty("Content-length",String.valueOf(queryString.length()));
		    con.setRequestMethod("POST");
		    OutputStreamWriter post = new OutputStreamWriter(con.getOutputStream());
		    post.write(queryString);
		    post.flush();
		        
		    BufferedReader in = new BufferedReader(new InputStreamReader(con.getInputStream()));
		    String inputLine;
		    while ((inputLine = in.readLine()) != null) {
		     	content += inputLine;
		    }
		        
		    SPARQLResultsXMLParser xmlRes = new SPARQLResultsXMLParser();
		    TupleQueryResultBuilder build = new TupleQueryResultBuilder();
		    xmlRes.setTupleQueryResultHandler(build);

		    ByteArrayInputStream is = new ByteArrayInputStream(content.getBytes("UTF-8"));
		    xmlRes.parse(is);

		    TupleQueryResult tupleRes = build.getQueryResult();
		    List<String> bindingNames = tupleRes.getBindingNames();
		    while (tupleRes.hasNext()) {
		    	BindingSet bindingSet = tupleRes.next();
		    	Value uses = bindingSet.getValue(bindingNames.get(0));
		    	numUses = Integer.parseInt(uses.stringValue());
		    }	
		    con.disconnect();
		} catch (Exception e) {
			e.printStackTrace();
		}
		return numUses;	
	}

}
