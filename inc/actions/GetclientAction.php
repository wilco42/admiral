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
class GetclientAction extends Action {

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

    $uaId = $request->getVal( "ua_id" );

    $isNew = false;

    $clientId = $db->getOne(str_queryf(
      'SELECT
      id
      FROM
      clients
      WHERE useragent_id = %s
      LIMIT 1;',
      $uaId
    ));

    if (!$clientId) {
      $isNew = true;
      $isInserted = $db->query(str_queryf(
        "INSERT INTO clients (name, useragent_id, useragent, ip, updated, created)
        VALUES(%s, %s, %s, %s, %s, %s);",
        $uaId,
        $uaId,
        "SauceLabs",
        "123.456.789.000",
        swarmdb_dateformat( SWARM_NOW ),
        swarmdb_dateformat( SWARM_NOW )
      ));

      $clientId = $db->getInsertId();
    }


    $this->setData(array(
			"clientId" => $clientId,
			"isNew" => $isNew
		));
	}
}
