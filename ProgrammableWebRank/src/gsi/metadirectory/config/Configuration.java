package gsi.metadirectory.config;

import java.io.FileInputStream;
import java.io.IOException;
import java.util.Properties;
 
public class Configuration {
 
    Properties properties = null;
 
    /** Configuration file name */
    private final static String CONFIG_FILE_NAME = "configuration.properties";
 
    Configuration() {
    	properties = new Properties();
    	try {
    		FileInputStream in = new FileInputStream(CONFIG_FILE_NAME);
    		properties.load(in);
			in.close();
		} catch (IOException e) {
			e.printStackTrace();
		}
    }//Configuration
 
    /**
     * Implementando Singleton
     *
     * @return
     */
    public static Configuration getInstance() {
        return ConfigurationHolder.INSTANCE;
    }
 
    private static class ConfigurationHolder {
        private static final Configuration INSTANCE = new Configuration();
    }
 
    /**
     * Retorna la propiedad de configuración solicitada
     *
     * @param key
     * @return
     */
    public String getProperty(String key) {
        return this.properties.getProperty(key);
    }//getProperty
}