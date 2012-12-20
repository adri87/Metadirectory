package gsi.metadirectory.main;

import gsi.metadirectory.config.Configuration;
import gsi.metadirectory.repository.IRepository;
import gsi.metadirectory.repository.OMRRepository;
import gsi.metadirectory.repository.SesameRepository;

import java.util.Calendar;
import java.util.GregorianCalendar;
import java.util.Timer;
import java.util.TimerTask;

public class Ranking {
	
	private static int refresh = 86400000;
	
	/**
	 * @param args
	 */
	public static void main(String[] args) {
		Calendar calendario = new GregorianCalendar();
		System.out.println("The data base choosen is: "+Configuration.getInstance().getProperty("server"));
		System.out.println("Start: " + calendario.get(Calendar.HOUR) + ":" +
		            calendario.get(Calendar.MINUTE) + ":" + calendario.get(Calendar.SECOND) +
		            ":" + calendario.get(Calendar.MILLISECOND));  
		
		String server = Configuration.getInstance().getProperty("server");
		if (server.equals("sesame")){
			TimerTask timerTask = new TimerTask(){
		         public void run() {
		     		 IRepository sesame = new SesameRepository();
		    		 sesame.init();
		        	 sesame.setDegreeCentrality();
		     		 sesame.setClosenessCentrality();
		     		 sesame.setSocialRating();
		         }
			};
	        Timer timer = new Timer();
	        timer.scheduleAtFixedRate(timerTask, 0, refresh);  
		} else if (server.equals("omr")){
			TimerTask timerTask = new TimerTask(){
		         public void run() {
		     		 IRepository omr = new OMRRepository();
		    		 omr.init();
		        	 omr.setDegreeCentrality();
		     		 omr.setClosenessCentrality();
		     		 omr.setSocialRating();
		         }
			};
	        Timer timer = new Timer();
	        timer.scheduleAtFixedRate(timerTask, 0, refresh);  
		} else {
			System.out.println("ERROR: Debes introducir un tipo de servidor válido");
		}
        
		Calendar calendario2 = new GregorianCalendar();
		System.out.println("End: " + calendario2.get(Calendar.HOUR) + ":" +
		            calendario2.get(Calendar.MINUTE) + ":" + calendario2.get(Calendar.SECOND) +
		            ":" + calendario2.get(Calendar.MILLISECOND));

		long diff = calendario2.getTimeInMillis() - calendario.getTimeInMillis();
		Calendar diferencia = new GregorianCalendar();
		diferencia.setTimeInMillis(diff);
		System.out.println("Time: " + diferencia.get(Calendar.MINUTE) + "min. " +
		            diferencia.get(Calendar.SECOND) + "sec. " + diferencia.get(Calendar.MILLISECOND) + "millisec."); 
	}

}
