package gsi.metadirectory.config;

import java.net.Authenticator;
import java.net.PasswordAuthentication;

public class MyAuthenticator extends Authenticator {
  
  protected PasswordAuthentication getPasswordAuthentication() {
	String user = Configuration.getInstance().getProperty("user");
	String password = Configuration.getInstance().getProperty("password");
    return new PasswordAuthentication(user, password.toCharArray());
  }
}