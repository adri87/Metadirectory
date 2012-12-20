package gsi.metadirectory.ClosenessCentrality;

import java.io.OutputStreamWriter;
import java.net.URL;
import java.net.URLConnection;
import java.net.URLEncoder;
import java.util.ArrayList;

public class ClosenessCentrality {
	
	private static String [][] ClosenessCentrality;	
	
	public static void closCent (Object [] apis, Object [] mashups, String [][] uses, int numElem, String repository){		
		ClosenessCentrality = new String [numElem][2];
		for (int j = 0; j < numElem; j++) {
			calClosCent(apis, mashups, uses, j);
		}
		setCC(repository);
	}
	/**
	 * @param myRepository
	 * @param apis
	 * @param mashups
	 * @param uses
	 */
	public static void calClosCent (Object [] apis, Object [] mashups, String[][] uses, int vertice){
		double cc = 0;
		int j = 0;
		Vertex[] vertex = new Vertex [apis.length+mashups.length];
		
		// Fill the vertices´ collection
		for (int i = 0; i < apis.length; i++) {
			vertex[j] = new Vertex(apis[i].toString());
			j++;
		}
		for (int i = 0; i < mashups.length; i++) {
			vertex[j] = new Vertex(mashups[i].toString());
			j++;
		}
		
		// Represent all the connections. (Apis-Mashups).
		ArrayList<Edge> connectionsTemp = new ArrayList<Edge>();
		Edge [] connections;
		for (int i = 0; i < vertex.length; i++) {
			connectionsTemp.clear();
			for (int k = 0; k < uses.length; k++) {
				for (int l = 0; l < uses[k].length; l++) {
					if(uses[k][l].equals(vertex[i].toString())){
						if (l == 0) connectionsTemp.add(new Edge(vertex[getVertexIndex(uses[k][1], vertex)],1));
						if (l == 1) connectionsTemp.add(new Edge(vertex[getVertexIndex(uses[k][0], vertex)],1));
					}
				}
			}
			connections = new Edge[connectionsTemp.size()];
			for (int m = 0; m < connectionsTemp.size(); m++) {
				connections[m] = connectionsTemp.get(m);
			}
			
			vertex[i].adjacencies = connections;
		}
		
		// Calculate the degree of the closeness centrality for each element
		Dijkstra.computePaths(vertex[vertice]);
		for (Vertex v : vertex){
			if (!v.equals(vertex[vertice]))
				cc += 1/v.minDistance;
		}
//		System.out.println("La CC de "+vertex[vertice]+" es "+cc);
		ClosenessCentrality[vertice][0] = vertex[vertice].toString();
		ClosenessCentrality[vertice][1] = String.valueOf(cc);
	}
	
	/**
	 * @param name
	 * @param vertex
	 * @return
	 */
	public static int getVertexIndex (String name, Vertex[] vertex){
		int index = -1;
		for (int i = 0; i < vertex.length; i++) {
			if(vertex[i].toString().equals(name)) return i;
		}
		return index;
	}
	
	/**
	 * 
	 */
	public static void setCC (String repository) {
		for (int j = 0; j < ClosenessCentrality.length; j+=100) {
			String insert = "INSERT DATA{ ";
			for (int i = j; i < 100+j; i++) {
				if (i==ClosenessCentrality.length)	break;
				insert += "<"+ClosenessCentrality[i][0]+"> <http://www.ict-omelette.eu/schema.rdf#ClosCent> "+ClosenessCentrality[i][1]+" .";
			}
			insert += " }";
			introduceClosenessCentrality(insert, repository);
		}
	}
	
	/**
	 * 
	 */
	public static void introduceClosenessCentrality (String data, String repository){		
		try {
		    // Construct data
			String parameter = URLEncoder.encode("update", "UTF-8") + "=" + URLEncoder.encode(data, "UTF-8");
			
		    // Send data
		    URL url = new URL(repository);
		    URLConnection conn = url.openConnection();
		    conn.setDoOutput(true);
		    OutputStreamWriter wr = new OutputStreamWriter(conn.getOutputStream());
		    wr.write(parameter);
		    wr.flush();
		    try { conn.getInputStream(); } catch (Exception e){ /*Capture TimeOut*/}
		    
		    // Close connection
		    wr.close();
		    
		} catch (Exception e) {
			e.printStackTrace();
		}
		
	}

}
