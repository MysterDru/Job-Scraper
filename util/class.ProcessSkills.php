<?php

class ProcessSkills {
	
	private $db;

	public function ProcessSkills( $text, $jobID, $skillCat = 0 ) {
		$this->db = new DatabaseManager;
		$this->db->OpenDB();
		
		set_time_limit( 0 );
		//print( "SKILL CATEGORY: " . $skillCat );
		$skills = $this->db->GetSkillsList( null, null, true );
/*		if( $skillCat == 0 )
			$skills = $this->db->GetSkillsList( null, null, true );
		else {
			$skills = $this->db->GetSkillsList( null, null, $skillCat, false );
		}
*/		foreach( $skills as $skill ) {
			$skillName 	= strtolower( $skill[ 0 ] );
			$skillID 	= $skill[ 1 ];
			// remove all commas from source text				
			// force all text to lowercase
			$text = strtolower( str_replace( ",", " ", $text ) ); 
			
			// remove any white space from the skill name, if it exists
			$skl = trim( $skillName );
			
			// check for for skill match with #skill# (#=space)
			$skill_space = " " . $skl . " ";
			// check for skill match with /skill#
			$skill_slash1 = "/" . $skl . " ";
			// check for skill match with #skill/
			$skill_slash2 = " " . $skl . "/";
						
			$found1 = strpos( $text, $skill_space );
			$found2 = strpos( $text, $skill_slash1 );
			$found3 = strpos( $text, $skill_slash2 );
			if( $found1 != false || $found2 != false || $found3 != false ) {
				$this->db->InsertJobSkill( $jobID, $skillID );
				//print( "<br />" . $skillName );
			} 
		}
		$this->db->CloseDB();
		return true;	
	}

}

?>