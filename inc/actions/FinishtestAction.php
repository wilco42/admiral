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
class FinishtestAction extends Action {

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


    $jobId = $request->getInt( "job_id" );
    $testName = $request->getVal( "test_name" );
    $uaId = $request->getVal( "ua_id" );

    $total = $request->getVal( "total" );
    $fail = $request->getInt( "fail" );
    $resultUrl = $request->getVal( "result_url" );
    $buildUrl = $request->getVal( "build_url" );




    $runId = $db->getOne(str_queryf(
      'SELECT
      id
      FROM
      runs
      WHERE job_id = %u
      AND   name = %s
      ORDER BY id DESC
      LIMIT 1;',
      $jobId,
      $testName
    ));


    if ( !$runId ) {
      $this->setError( "internal-error", "Could not get run id" );
      return;
    }

    $clientId = $db->getOne(str_queryf(
      'SELECT
      id
      FROM
      clients
      WHERE useragent_id = %s
      LIMIT 1;',
      $uaId
    ));

    if ( !$clientId ) {
      $this->setError( "internal-error", "Could not get client id" );
      return;
    }

    $resultId = $db->getOne(str_queryf(
      'SELECT
      id
      FROM
      runresults
      WHERE run_id = %u
      AND client_id = %u
      LIMIT 1;',
      $runId,
      $clientId
    ));

    $ret = $db->query(str_queryf(
      "UPDATE runresults
      SET status = 2, total = %u, fail = %u, result_url=%s, build_url=%s
      WHERE id = %u;",
      $total,
      $fail,
      $resultUrl,
      $buildUrl,
      $resultId,
      $row->id
    ));

    $this->setData(array(
			"result" => $ret
		));
	}
}
