package gsi.metadirectory.ClosenessCentrality;


public class Vertex implements Comparable<Vertex> {

	public final String name;
	    
	public Edge[] adjacencies;

	public double minDistance = Double.POSITIVE_INFINITY;

	public Vertex previous;

	/**
	 * @param argName
	 */
	public Vertex(String argName) { 
		name = argName; 
	}
	
	/* (non-Javadoc)
	 * @see java.lang.Object#toString()
	 */
	public String toString() { 
		return name; 
	}
	
	/* (non-Javadoc)
	 * @see java.lang.Comparable#compareTo(java.lang.Object)
	 */
	public int compareTo(Vertex other) {
		return Double.compare(minDistance, other.minDistance);
	}
}
