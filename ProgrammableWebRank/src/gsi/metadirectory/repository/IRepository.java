package gsi.metadirectory.repository;

public interface IRepository {
	
	/**
	 * 
	 */
	public void init();
	
	/**
	 * 
	 */
	public void extractApis();
	
	/**
	 * 
	 */
	public void extractMashups(); 
	
	/**
	 * 
	 */
	public void usesRelations();
	
	/**
	 * 
	 */
	public void setDegreeCentrality();
	
	/**
	 * 
	 */
	public void setClosenessCentrality();
	
	/**
	 * 
	 */
	public void setSocialRating();
	
	/**
	 * 
	 */
	public void getNumApisMashups();
	
	/**
	 * @return
	 */
	public int getNumUses();	
}