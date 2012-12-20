package gsi.metadirectory.DegreeCentrality;

import java.io.OutputStreamWriter;
import java.net.URL;
import java.net.URLConnection;
import java.net.URLEncoder;
import java.util.HashMap;

public class DegreeCentrality {
	
	public static HashMap<String, Integer> usesCount = new HashMap<String, Integer>();
	
	/**
	 * @param obj
	 * @param uses
	 * @param myRepository
	 */
	public static void calDegCent(Object[] obj, String[][] uses, String repository){
		
		usesCount = Uses.count(uses, usesCount);
		for (int j = 0; j < obj.length; j+=100) {
			String insert = "INSERT DATA{ ";
			for (int i = j; i < 100+j; i++) {
				if (i==obj.length)	break;
				if (usesCount.get(obj[i].toString()) != null)
					insert += "<"+obj[i].toString()+"> <http://www.ict-omelette.eu/schema.rdf#DegCent> "+usesCount.get(obj[i].toString())+" .";
				else 
					insert += "<"+obj[i].toString()+"> <http://www.ict-omelette.eu/schema.rdf#DegCent> 0 .";
			}
			insert += " }";
			introduceDegreeCentrality(insert, repository);
		}
	}
	
	/**
	 * 
	 */
	public static void introduceDegreeCentrality (String data, String repository){		
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
