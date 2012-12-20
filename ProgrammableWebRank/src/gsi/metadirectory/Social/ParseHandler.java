package gsi.metadirectory.Social;

import org.xml.sax.Attributes;
import org.xml.sax.SAXException;
import org.xml.sax.helpers.DefaultHandler;

public class ParseHandler extends DefaultHandler {

	private static int count = 0;
	
	@Override
	public void startDocument() throws SAXException {
		count = 0;
	}

	public void startElement(String uri, String localName, String qName, Attributes attributes) {
		if (qName.equals("entry")) {
			count++;
		}
	}
	
	public static int getCount() {
		return count;
	}

}
