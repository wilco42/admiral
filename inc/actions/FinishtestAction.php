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

		$resultId = $request->getInt( "result_id" );
    $total = $request->getVal( "total" );
    $fail = $request->getInt( "fail" );

    $ret = $db->query(str_queryf(
      "UPDATE runresults
      SET status = 2, total = %u, fail = %u
      WHERE id = %u;",
      $total,
      $fail,
      $resultId,
      $row->id
    ));

    $this->setData(array(
			"result" => $ret
		));
	}
}
