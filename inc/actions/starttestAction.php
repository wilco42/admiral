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
class StarttestAction extends Action {

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

		$runId = $request->getInt( "run_id" );
    $uaId = $request->getVal( "ua_id" );
    $clientId = $request->getInt( "client_id" );

    $resultInserted = $db->query(str_queryf(
      'INSERT INTO runresults
      (run_id, client_id, status, store_token, updated, created)
      VALUES(%u, %u, 1, %s, %s, %s);',
      $runId,
      $clientId,
      0,
      swarmdb_dateformat( SWARM_NOW ),
      swarmdb_dateformat( SWARM_NOW )
    ));
    $runresultsId = $db->getInsertId();


    $isInserted = $db->query(str_queryf(
      "INSERT INTO run_useragent (run_id, useragent_id, max, results_id, updated, created)
      VALUES(%u, %s, %u, %u, %s, %s);",
      $runId,
      $uaId,
      1,
      $runresultsId,
      swarmdb_dateformat( SWARM_NOW ),
      swarmdb_dateformat( SWARM_NOW )
    ));

    $newRunUAId = $db->getInsertId();

    $this->setData(array(
			"resultsId" => $runresultsId,
			"runUAId" => $newRunUAId
		));
	}
}
