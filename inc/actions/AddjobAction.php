<?php
/**
 * "Addjob" action.
 * Addjob ignores the current session. Instead it uses token.
 *
 * @author John Resig, 2008-2011
 * @author Timo Tijhof, 2012-2013
 * @since 0.1.0
 * @package TestSwarm
 */
class AddjobAction extends Action {

	/**
	 * @actionMethod POST: Required.
	 * @actionParam string jobName: May contain HTML.
	 * @actionParam int runMax
	 * @actionParam array runNames
	 * @actionParam array runUrls
	 * @actionParam array browserSets
	 * @actionAuth: Required.
	 */
	public function doAction() {
		$conf = $this->getContext()->getConf();
		$db = $this->getContext()->getDB();
		$request = $this->getContext()->getRequest();

		$projectID = $this->doRequireAuth();
		if ( !$projectID ) {
			return;
		}

    $buildId = $request->getInt( "buildId" );
		$jobName = $request->getVal( "jobName" );

		if ( !$jobName
      || !$buildId
		) {
			$this->setError( "missing-parameters" );
			return;
		}

		// Verify job name maxlength (otherwise MySQL will crop it, which might
		// result in incomplete html, screwing up the JobPage).
		if ( strlen( $jobName ) > 255 ) {
			$this->setError( "invalid-input", "Job name too long (up to 255 characters)." );
		}


    $db->query(str_queryf(
      "LOCK TABLES jobs WRITE;"
    ));

    $jobId = $db->getOne(str_queryf(
      'SELECT
      id
      FROM
      jobs
      WHERE build_id = %u
        AND project_id = %s
      LIMIT 1;',
      $buildId,
      $projectID
    ));

    $isNew = true;

    if ($jobId) {
      $isNew = false;
    } else {

      // Create job
      $isInserted = $db->query(str_queryf(
        "INSERT INTO jobs (build_id, name, project_id, created)
        VALUES (%u, %s, %s, %s);",
        $buildId,
        $jobName,
        $projectID,
        swarmdb_dateformat( SWARM_NOW )
      ));

      $jobId = $db->getInsertId();
    }

    $db->query(str_queryf(
      "UNLOCK TABLES;"
    ));

    if ( !$jobId ) {
      $this->setError( "internal-error", "Get or create of job failed." );
      return;
    }

    $this->setData(array(
			"id" => $jobId,
      "isNew" => $isNew,
			"runTotal" => count( $runs ),
		));
	}
}
