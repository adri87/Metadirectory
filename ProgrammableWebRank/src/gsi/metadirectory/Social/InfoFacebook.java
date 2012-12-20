package gsi.metadirectory.Social;

import gsi.metadirectory.config.Configuration;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.net.URL;
import java.net.URLConnection;
import java.text.ParseException;

import org.codehaus.jettison.json.JSONArray;
import org.codehaus.jettison.json.JSONException;
import org.codehaus.jettison.json.JSONObject;

public class InfoFacebook {
	
	
	public static int getLikesTot (String notice) throws ParseException, JSONException{
		int likes = 0;
		JSONObject j;
		String title = notice.replace("ñ", "n");
		title = title.replace(",", "");
		title = title.replace(" ", "%2B");
		String call = Configuration.getInstance().getProperty("scrappyServer")+"/ejson/http://www.google.com/search%3Fq%3D"+title;
		System.out.println("call: "+call);
		JSONArray json = new JSONArray(parser(call));
		String [] sources = new String [json.length()];
	    for (int i=0; i<json.length(); i++){
	    	j = json.getJSONObject(i);
	    	sources[i] = j.getString("id");
	    	sources[i] = sources[i].replace("http://www.google.com/url?q=", "");
	    	likes += Integer.parseInt(getLikesFacebook(sources[i]));
	    }
		return likes;
	}
	
	public static String getLikesFacebook (String source) throws ParseException, JSONException{
		String likes = "0";
		String pag = "https://graph.facebook.com/?ids="+source;
		String page = parser(pag);
        JSONObject json = new JSONObject(page);
        try {
        	source = source.substring(0, source.indexOf("&sa"));
			if(json.getJSONObject(source).getString("shares")!=null)
			   	likes= json.getJSONObject(source).getString("shares");
		} catch (Exception e) {
		}
		return likes;
	}
	
	@SuppressWarnings("null")
	public static String parser (String pag){
		String text = null;	
		try {
			URL page = new URL(pag);
			URLConnection url = page.openConnection();
            BufferedReader in = new BufferedReader(new InputStreamReader(url.getInputStream()));
            String inputLine;
            while ((inputLine = in.readLine()) != null) {
            	text += inputLine;
            }
            text = text.substring(4);
            in.close();
		} catch (Exception e) {
			return text = "{}";
		}
		return text;
	}
	
}