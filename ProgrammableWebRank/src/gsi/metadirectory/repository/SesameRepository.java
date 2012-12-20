package gsi.metadirectory.repository;

import gsi.metadirectory.ClosenessCentrality.ClosenessCentrality;
import gsi.metadirectory.DegreeCentrality.DegreeCentrality;
import gsi.metadirectory.Social.Social;
import gsi.metadirectory.config.Configuration;

import java.util.ArrayList;
import java.util.Calendar;
import java.util.GregorianCalendar;
import java.util.List;

import org.openrdf.model.Value;
import org.openrdf.query.BindingSet;
import org.openrdf.query.QueryLanguage;
import org.openrdf.query.TupleQuery;
import org.openrdf.query.TupleQueryResult;
import org.openrdf.repository.Repository;
import org.openrdf.repository.RepositoryException;
import org.openrdf.repository.http.*;
import org.openrdf.repository.RepositoryConnection;

public class SesameRepository implements IRepository {
	
	private Repository myRepository;
	private RepositoryConnection con;
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
	 * @see gsi.metadirectory.repository.IRepository#init()
	 */
	public void init() {
		String sesameServer = Configuration.getInstance().getProperty("sesameDataBase");
		String repositoryID = Configuration.getInstance().getProperty("sesameRepository");
		myRepository = new HTTPRepository(sesameServer, repositoryID);
		try {
			myRepository.initialize();
			extractApis();
			extractMashups();
			getNumApisMashups();
			usesRelations();
		} catch (RepositoryException e) {
			e.printStackTrace();
		}
	}

	/* (non-Javadoc)
	 * @see gsi.metadirectory.repository.IRepository#extractApis()
	 */
	public void extractApis() {
		ArrayList <Value> tempApis = new ArrayList <Value>();
		try {
			con = myRepository.getConnection();
			try {
				String queryString = queryPrefix
						+ "SELECT DISTINCT ?api WHERE {"
						+ "?s ?p ?api. "
						+ "FILTER regex(str(?api), \"http://www.programmableweb.com/api/\")"
						+ "}";
				
				TupleQuery tupleQuery = con.prepareTupleQuery(QueryLanguage.SPARQL, queryString);
				TupleQueryResult result = tupleQuery.evaluate();
				try {
					List<String> bindingNames = result.getBindingNames();
					while (result.hasNext()) {
						BindingSet bindingSet = result.next();
						Value api = bindingSet.getValue(bindingNames.get(0));
						tempApis.add(api);
					}
					apis = tempApis.toArray();
				} finally {
					result.close();
				}
			} finally {
				con.close();
			}
		} catch (Exception e) {
			e.printStackTrace();
		}
	}
	
	/* (non-Javadoc)
	 * @see gsi.metadirectory.repository.IRepository#extractMashups()
	 */
	public void extractMashups() {
		ArrayList <Value> tempMashups = new ArrayList <Value>();
		try {
			con = myRepository.getConnection();
			try {
				String queryString = queryPrefix
						+ "SELECT DISTINCT ?mashups WHERE {"
						+ "?mashups ?p ?o. "
						+ "FILTER regex(str(?mashups), \"http://www.programmableweb.com/mashup/\")"
						+ "}";
				
				TupleQuery tupleQuery = con.prepareTupleQuery(QueryLanguage.SPARQL, queryString);
				TupleQueryResult result = tupleQuery.evaluate();
				try {
					List<String> bindingNames = result.getBindingNames();
					while (result.hasNext()) {
						BindingSet bindingSet = result.next();
						Value api = bindingSet.getValue(bindingNames.get(0));
						tempMashups.add(api);
					}
					mashups = tempMashups.toArray();
				} finally {
					result.close();
				}
			} finally {
				con.close();
			}
		} catch (Exception e) {
			e.printStackTrace();
		}
	}
	
	/* (non-Javadoc)
	 * @see gsi.metadirectory.repository.IRepository#usesRelations()
	 */
	public void usesRelations(){
		int i=0, size = getNumUses();
		uses = new String[size][2];
		try {
			con = myRepository.getConnection();
			try {
				String queryString = queryPrefix
						+ "SELECT ?mashup ?api WHERE {"
						+ "?mashup limon:uses ?api. "
						+ "}";
				
				TupleQuery tupleQuery = con.prepareTupleQuery(QueryLanguage.SPARQL, queryString);
				TupleQueryResult result = tupleQuery.evaluate();
				try {
					List<String> bindingNames = result.getBindingNames();
					while (result.hasNext()) {
						BindingSet bindingSet = result.next();
						String mashup = bindingSet.getValue(bindingNames.get(0)).toString();
						String api = bindingSet.getValue(bindingNames.get(1)).toString();
						uses[i][0]=mashup;
						uses[i][1]=api;
						i++;
					}
				} finally {
					result.close();
				}
			} finally {
				con.close();
			}
		} catch (Exception e) {
			e.printStackTrace();
		}
	}

	/* (non-Javadoc)
	 * @see gsi.metadirectory.repository.IRepository#setDegreeCentrality()
	 */
	public void setDegreeCentrality (){	
		Calendar calendario = new GregorianCalendar();
		System.out.println("Start: " + calendario.get(Calendar.HOUR) + ":" +
		            calendario.get(Calendar.MINUTE) + ":" + calendario.get(Calendar.SECOND) +
		            ":" + calendario.get(Calendar.MILLISECOND));  
		
		String repository = Configuration.getInstance().getProperty("repositoryUpdate");
		DegreeCentrality.calDegCent(apis, uses, repository);
		DegreeCentrality.calDegCent(mashups, uses, repository);
		
		Calendar calendario2 = new GregorianCalendar();
		System.out.println("End: " + calendario2.get(Calendar.HOUR) + ":" +
		            calendario2.get(Calendar.MINUTE) + ":" + calendario2.get(Calendar.SECOND) +
		            ":" + calendario2.get(Calendar.MILLISECOND));

		long diff = calendario2.getTimeInMillis() - calendario.getTimeInMillis();
		Calendar diferencia = new GregorianCalendar();
		diferencia.setTimeInMillis(diff);
		System.out.println("Time DC: " + diferencia.get(Calendar.MINUTE) + "min. " +
		            diferencia.get(Calendar.SECOND) + "sec. " + diferencia.get(Calendar.MILLISECOND) + "millisec."); 
	}
	

	/* (non-Javadoc)
	 * @see gsi.metadirectory.repository.IRepository#setClosenessCentrality()
	 */
	public void setClosenessCentrality (){
		Calendar calendario = new GregorianCalendar();
		System.out.println("Start: " + calendario.get(Calendar.HOUR) + ":" +
		            calendario.get(Calendar.MINUTE) + ":" + calendario.get(Calendar.SECOND) +
		            ":" + calendario.get(Calendar.MILLISECOND));  
		
		String repository = Configuration.getInstance().getProperty("repositoryUpdate");
		ClosenessCentrality.closCent(apis, mashups, uses, apis.length+mashups.length, repository);
		
		Calendar calendario2 = new GregorianCalendar();
		System.out.println("End: " + calendario2.get(Calendar.HOUR) + ":" +
		            calendario2.get(Calendar.MINUTE) + ":" + calendario2.get(Calendar.SECOND) +
		            ":" + calendario2.get(Calendar.MILLISECOND));

		long diff = calendario2.getTimeInMillis() - calendario.getTimeInMillis();
		Calendar diferencia = new GregorianCalendar();
		diferencia.setTimeInMillis(diff);
		System.out.println("Time CC: " + diferencia.get(Calendar.MINUTE) + "min. " +
		            diferencia.get(Calendar.SECOND) + "sec. " + diferencia.get(Calendar.MILLISECOND) + "millisec."); 
	}
	
	/* (non-Javadoc)
	 * @see gsi.metadirectory.repository.IRepository#setSocialRating()
	 */
	public void setSocialRating(){
		Calendar calendario = new GregorianCalendar();
		System.out.println("Start: " + calendario.get(Calendar.HOUR) + ":" +
		            calendario.get(Calendar.MINUTE) + ":" + calendario.get(Calendar.SECOND) +
		            ":" + calendario.get(Calendar.MILLISECOND));  
		
		String repository = Configuration.getInstance().getProperty("repositoryUpdate");
		Social.setSocial(apis, mashups, repository);
		
		Calendar calendario2 = new GregorianCalendar();
		System.out.println("End: " + calendario2.get(Calendar.HOUR) + ":" +
		            calendario2.get(Calendar.MINUTE) + ":" + calendario2.get(Calendar.SECOND) +
		            ":" + calendario2.get(Calendar.MILLISECOND));

		long diff = calendario2.getTimeInMillis() - calendario.getTimeInMillis();
		Calendar diferencia = new GregorianCalendar();
		diferencia.setTimeInMillis(diff);
		System.out.println("Time SR: " + diferencia.get(Calendar.MINUTE) + "min. " +
		            diferencia.get(Calendar.SECOND) + "sec. " + diferencia.get(Calendar.MILLISECOND) + "millisec."); 
	}
	

	/* (non-Javadoc)
	 * @see gsi.metadirectory.repository.IRepository#getNumApisMashups()
	 */
	public void getNumApisMashups(){		
		System.out.println("The number of apis is: "+apis.length);
		System.out.println("The number of mashups is: "+mashups.length);		
	}


	/* (non-Javadoc)
	 * @see gsi.metadirectory.repository.IRepository#getNumUses()
	 */
	public int getNumUses() {
		int numUses = 0;
		try {
			con = myRepository.getConnection();
			try {
				String queryString = queryPrefix
						+ "SELECT (COUNT (?num) AS ?total) WHERE {"
						+ "?num limon:uses ?o. "
						+ "}";
				
				TupleQuery tupleQuery = con.prepareTupleQuery(QueryLanguage.SPARQL, queryString);
				TupleQueryResult result = tupleQuery.evaluate();
				try {
					List<String> bindingNames = result.getBindingNames();
					while (result.hasNext()) {
						BindingSet bindingSet = result.next();
						Value uses = bindingSet.getValue(bindingNames.get(0));
						numUses = Integer.parseInt(uses.stringValue());
					}
				} finally {
					result.close();
				}
			} finally {
				con.close();
			}
		} catch (Exception e) {
			e.printStackTrace();
		}
		return numUses;	
	}
	
}
