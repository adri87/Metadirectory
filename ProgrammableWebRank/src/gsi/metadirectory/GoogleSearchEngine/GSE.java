package gsi.metadirectory.GoogleSearchEngine;

import gsi.metadirectory.config.Configuration;

import java.io.IOException;
import java.io.OutputStreamWriter;
import java.net.URL;
import java.net.URLConnection;
import java.net.URLEncoder;

public class GSE {
	
	public static String [][] ratingsGoogle;
	
	public static void getRepercusion(Object [] apis, Object [] mashups) throws IOException{
		ratingsGoogle = new String [apis.length+mashups.length][2];
		int repercusion = 0, index = 0;
		for (int i = 0; i < apis.length; i++) {
			// aqui obtenemos la repercusion parseando
			ratingsGoogle[index][0] = apis[i].toString();
			ratingsGoogle[index][1] = Integer.toString(repercusion);
			index++;
		}
		for (int i = 0; i < mashups.length; i++) {
			// aqui obtenemos la repercusion parseando
			ratingsGoogle[index][0] = mashups[i].toString();
			ratingsGoogle[index][1] = Integer.toString(repercusion);
			index++;
		}
		setGSE();
	}
	
	/**
	 * 
	 */
	public static void setGSE () {
		for (int j = 0; j < ratingsGoogle.length; j+=100) {
			String insert = "INSERT DATA{ ";
			for (int i = j; i < 100+j; i++) {
				if (i==ratingsGoogle.length)	break;
				insert += "<"+ratingsGoogle[i][0]+"> <http://www.ict-omelette.eu/schema.rdf#RatSoc> "+ratingsGoogle[i][1]+" .";
			}
			insert += " }";
			introduceGoogleSearhEngine(insert);
		}
		System.out.println("INFORMACIÓN SOCIAL ACTUALIZADA");
	}
	
	/**
	 * 
	 */
	public static void introduceGoogleSearhEngine (String data){		
		try {
		    // Construct data
			String parameter = URLEncoder.encode("update", "UTF-8") + "=" + URLEncoder.encode(data, "UTF-8");
			
		    // Send data
		    URL url = new URL(Configuration.getInstance().getProperty("repositoryUpdate"));
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
