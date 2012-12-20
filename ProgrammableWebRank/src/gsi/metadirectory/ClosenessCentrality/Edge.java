package gsi.metadirectory.ClosenessCentrality;


public class Edge {

	public final Vertex target;
	    
	public final double weight;
	    
	/**
	 * @param argTarget
	 * @param argWeight
	 */
	public Edge(Vertex argTarget, double argWeight){ 
		target = argTarget; 
	   	weight = argWeight; 
	}

}
