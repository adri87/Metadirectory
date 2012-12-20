package gsi.metadirectory.Social;

import java.io.OutputStreamWriter;
import java.net.URL;
import java.net.URLConnection;
import java.net.URLEncoder;
//import java.text.ParseException;
//
//import org.codehaus.jettison.json.JSONException;

public class Social {
	
	public static String [][] ratingsSocial;
	
	/**
	 * @param apis
	 * @param mashups
	 */
	public static void setSocial(Object [] apis, Object [] mashups, String repository){		
		ratingsSocial = new String [apis.length + mashups.length][2];
		int numLikes = 0, numTweets, ratingSoc = 0, index = 0;
		for (int i = 0; i < mashups.length; i++) {
			String mashup = mashups[i].toString();
			mashup = mashup.substring(38, mashup.length());
			try {
//				numLikes = InfoFacebook.getLikesTot(mashup);
				numTweets = InfoTwitter.getPopularityTwitter(mashup);
				if (Math.abs(numLikes-numTweets)==0)
					ratingSoc= numLikes;
				else
					ratingSoc = (numLikes + numLikes*(numTweets/100))/2;
//			} catch (ParseException e) {
//
//			} catch (JSONException e) {
			} catch (Exception e) {

			}
			ratingsSocial[index][0] = mashups[i].toString();
			ratingsSocial[index][1] = String.valueOf(ratingSoc);
			index++;
		}
		for (int j = 0; j < apis.length; j++) {
			String api = apis[j].toString();
			api = api.substring(35, api.length());
			try {
//				numLikes = InfoFacebook.getLikesTot(api);
				numTweets = InfoTwitter.getPopularityTwitter(api);
				if (Math.abs(numLikes-numTweets)==0)
					ratingSoc= numLikes;
				else
					ratingSoc = (numLikes + numLikes*(numTweets/100))/2;
//			} catch (ParseException e) {
//			}catch (JSONException e) {
			} catch (Exception e) {

			}
			ratingsSocial[index][0] = apis[j].toString();
			ratingsSocial[index][1] = String.valueOf(ratingSoc);
			index++;
		}
		System.out.println("MATRIZ SOCIAL COMPLETADA");
		setRS(repository);
	}
	
	/**
	 * 
	 */
	public static void setRS (String repository) {
		for (int j = 0; j < ratingsSocial.length; j+=100) {
			String insert = "INSERT DATA{ ";
			for (int i = j; i < 100+j; i++) {
				if (i==ratingsSocial.length)	break;
				insert += "<"+ratingsSocial[i][0]+"> <http://www.ict-omelette.eu/schema.rdf#RatSoc> "+ratingsSocial[i][1]+" .";
			}
			insert += " }";
			introduceRatingSocial(insert, repository);
		}
	}
	
	/**
	 * 
	 */
	public static void introduceRatingSocial (String data, String repository){		
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
