<?php
include ("httplib.php");
include ("xmlfunctions.php");
include ("updateUserChangesetsXML.php");
//include("test.php");
include ("GLOBAL_CONSTANTS.php");
include ("database_query.php");
/* File Structure
- $resultXMLallchangesets='files/'."$username".'_Changesets.xml';  	
- $resultXMLallchangesetsstate='files/'."$username".'_Changesets_state.xml';
*/

class OsmApi
{
    function __construct($created_by = "PhpOsmApi", $api =
        "http://www.openstreetmap.org")
    {
        $this->_created_by = $created_by;
        $this->_api = $api;
        $this->_conn = new HTTPRequest($this->_api, true);
    }
    /*---------------- Using OSM API v0.6 --------------------*/
    function changesetsGet($param = null)
    {
        $min_lon = null;
        $min_lat = null;
        $max_lon = null;
        $max_lat = null;
        $uid = null;
        $username = null;
        $created_after = null;
        $created_before = null;
        //Get parameters
        if ($param <> "") {

            foreach ($param as $key => $value) {
                if ($key == 'min_lon') {
                    $min_lon = $param['min_lon'];
                }
                if ($key == 'min_lat') {
                    $min_lat = $param['min_lat'];
                }
                if ($key == 'max_lon') {
                    $max_lon = $param['max_lon'];
                }
                if ($key == 'max_lat') {
                    $max_lat = $param['max_lat'];
                }
                if ($key == 'uid') {
                    $uid = $param['uid'];
                }
                if ($key == 'username') {
                    $username = $param['username'];
                }
                if ($key == 'created_after') {
                    $created_after = $param['created_after'];
                }
                if ($key == 'created_before') {
                    $created_before = $param['created_before'];
                }
            }
            /*
            if($param['bbox']<>""){
            $bbox=$param['bbox'];
            $min_lon=$bbox['min_lon'];
            $min_lat=$bbox['min_lat'];
            $max_lon=$bbox['max_lon'];
            $max_lat=$bbox['max_lat'];
            }
            */
        }
        $uri = "/api/0.6/changesets";
        $params = array();
        if (($min_lon <> "") && ($min_lat <> "") && ($max_lon <> "") && ($max_lat <> "")) {

            $a = array(
                (string )$min_lon,
                (string )$min_lat,
                (string )$max_lon,
                (string )$max_lat);
            $x = implode(',', $a);
            $params["bbox"] = $x;
        }

        if (($uid <> "")) {
            $params["user"] = $uid;
        }

        if (($username <> "")) {
            $params["display_name"] = $username;
        }

        if (($created_after) <> "") {
            if (!(($created_before) <> "")) {
                $params["time"] = $created_after;
            }
        }

        if (($created_before) <> "") {
            if (!(($created_after) <> "")) {
                $created_after = "1970-01-01T00:00:00Z";
                $params["time"] = $created_after . "," . $created_before;
            }
        }

        if (($created_before <> "") && ($created_after <> "")) {
            $params["time"] = $created_after . "," . $created_before;
        }

        if (($params) <> "") {
            $uri = $uri . '?';
            $count = 0;
            foreach ($params as $key => $value) {
                $s = $this->_conn->ParamEncode($key, $value);
                if ($count == 0) {
                    $uri = $uri . $s;
                } else {
                    $uri = $uri . "&" . $s;
                }
                $count = $count + 1;
            }
        }

        $url = $this->_api . $uri;
        //print $url;
        //print '<br><br>';
        $obj = simplexml_load_file($url);
        return $obj;
    }
    /* ===============changesetGetDownload================*/
    function changesetsGetDownload($id = null)
    {
        $uri = "/api/0.6/changeset";
        if ($id <> "") {
            $uri = $uri . '/' . $id . '/download';
        }
        $url = $this->_api . $uri;
        //print $url;
        //print '<br>';
        $obj = simplexml_load_file($url);
        return $obj;
    }

    /* ===============User Information================*/
    function userGet($param = null)
    {
        $uid = null;
        $username = null;

        foreach ($param as $key => $value) {
            if ($key == 'username') {
                $username = $param['username'];
            }
            if ($key == 'uid') {
                $uid = $value;
            }
        }

        $uri = "/api/0.6/user";
        if ($uid <> "") {
            $uri = $uri . '/' . $uid;
        } elseif (($username <> "") && ($uid == "")) {

            $uid = $this->getuid($username);
            $uri = $uri . '/' . $uid;
        } elseif (($username <> "") && ($uid == "")) {
            return - 1;
        }
        $url = $this->_api . $uri;


        $obj = simplexml_load_file($url);
        return $obj;
    }
    function getuid($username = null)
    {
        $obj = $this->changesetsGet(array('username' => $username));
        $changesetGet_uid = $obj->changeset->attributes()->uid;
        return $changesetGet_uid;
    }
    function getChangesetCount($param = null)
    {
        $uid = null;
        $username = null;
        if ($param <> null) {
            $obj = $this->userGet($param);
            $count = $obj->user->changesets->attributes()->count;
        }
        return $count;
    }
    function getAllChangesets($param = null)
    {
        set_time_limit(10000000000);
        //Declare variables
        $min_lon = null;
        $min_lat = null;
        $max_lon = null;
        $max_lat = null;
        $uid = null;
        $username = null;
        $created_after = null;
        $created_before = null;
        //Get variable values from parameters passed
        if ($param <> null) {
            foreach ($param as $key => $value) {
                if ($key == 'min_lon') {
                    $min_lon = $param['min_lon'];
                }
                if ($key == 'min_lat') {
                    $min_lat = $param['min_lat'];
                }
                if ($key == 'max_lon') {
                    $max_lon = $param['max_lon'];
                }
                if ($key == 'max_lat') {
                    $max_lat = $param['max_lat'];
                }
                if ($key == 'uid') {
                    $uid = $param['uid'];
                }
                if ($key == 'username') {
                    $username = $param['username'];
                }
                if ($key == 'created_after') {
                    $created_after = $param['created_after'];
                }
                if ($key == 'created_before') {
                    $created_before = $param['created_before'];
                }
            }
        }
        //Initialize $creted_before with current time if not in param
        if ($created_before == "") {
            $now = $this->getCurrentTimestamp();
            $created_before = $now;
        }
        $allchangeset_obj = array();

        /*
        //By BBox			
        
        if ($param<>null && isset($param['bbox'])){
        
        $bbox=$param['bbox'];
        $file=getFILE_PATH(array('bbox'=>$bbox));
        $changesetsXML='files/result';
        $resultXML=$file['resultXMLallchangesets'];
        $resultXMLstate=$file['resultXMLallchangesetsstate'];
        $tempXML='files/tempXML.xml';
        $list_changesets=array();
        $size=100;
        $loop_count=0;
        //||
        while(!($size<100)){
        if($loop_count==3){break;}
        print'yes';				
        print "Created Before = ".$created_before;
        print"</br>";
        $data=$this->changesetsGet($param);
        $changesets=$data->changeset;	
        
        if(isset($changesets[0])){
        if ($loop_count==0){
        $data->asXML($resultXML);
        }
        elseif($loop_count>0){
        $data->asXML($tempXML);
        mergeXML(array('toxml'=>$tempXML, 'fromxml'=>$resultXML,'fileout'=>$resultXML));
        unlink($tempXML);
        }					
        
        $size=sizeof($changesets);
        print "Current size ".$size;
        print "</br>";
        $lastindex=$size-1;
        foreach($changesets as $ch)
        {
        array_push($allchangeset_obj,$ch);	
        }
        
        $firsttimestamp=$changesets[0]->attributes()->closed_at;
        $lasttimestamp=$changesets[$lastindex]->attributes()->closed_at;
        print "first timestamp = ".$firsttimestamp;
        print "</br>";
        print "last timestamp = ".$lasttimestamp;
        print "</br>";
        
        $currenttimestamp=new DateTime($lasttimestamp);
        $nexttimestamp=date_sub($currenttimestamp,new DateInterval('P0Y0M0DT0H2M00S'));
        $nexttimestamp_str = $nexttimestamp->format('Y-m-d H:i:s');
        $result=explode(' ',$nexttimestamp_str);
        $nexttimestamp_str=$result[0].'T'.$result[1].'Z';
        print '<br>';
        $created_before=$nexttimestamp_str;
        $param['created_before']=$created_before;
        }else{print 'no more changeset';}
        $loop_count=$loop_count+1;
        }
        $allchangeset_obj=simplexml_load_file($resultXML);
        //unlink($resultXML);
        getChangesetsListStateBbox($allchangeset_obj);
        }
        //print 'sizeof $allchangeset_obj = '.sizeof($allchangeset_obj);
        //print "</br>";print "</br>";	
        */
        //By user
        if ($param <> null && isset($param['username'])) {
            $obj = $this->userGet(array('username' => $username));
            insert_users($obj);
            
            $file = getFILE_PATH(array('username' => $username));
            $changeset_count = $this->getChangesetCount($param);
            $count = 0;
            $allchangeset_obj = array();
            $list_changesets = array();
            $size = 99;
            $loop_count = 0;
            $changesetsXML = 'files/result';
            $resultXML = $file['resultXMLallchangesets'];
            $resultXMLstate = $file['resultXMLallchangesetsstate'];
            $tempXML = 'files/tempXML.xml';
            while ($size >= 99) {
                //print '<br>size in while loop===<br>'.$size;
                set_time_limit(10000000000);
                //print "Created Before = ".$created_before;
                //print"</br>";
                $data = $this->changesetsGet($param);
                insert_changesets($data);
                if ((isset($data))) {
                    if ($loop_count == 0) {
                        $data->asXML($resultXML);
                    } elseif ($loop_count > 0) {
                        unset($data->changeset[0]);
                        $data->asXML($tempXML);

                        mergeXML(array(
                            'toxml' => $tempXML,
                            'fromxml' => $resultXML,
                            'fileout' => $resultXML));
                        unlink($tempXML);
                    }
                    $changesets = $data->changeset;

                    if (isset($changesets)) {
                        $size = sizeof($changesets);

                        $lastindex = $size - 1;
                        if ($lastindex < 0) {
                            break;
                        }
                        //	print "Current size ".$size;
                        //print "</br>";
                        //print '<br>Last index = '.$lastindex.'<br>';

                        $lasttimestamp = $changesets[$lastindex]->attributes()->closed_at;
                        $param['created_before'] = $lasttimestamp;

                    } else { //print 'no more changeset'; break;
                    }
                }
                //print 'sizeof $allchangeset_obj = '.sizeof($allchangeset_obj);
                //print "</br>";print "</br>";
                $loop_count = $loop_count + 1;
                //print '<br>Loop count<br>'.$loop_count;
            } //end of while loop
            $allchangeset_obj = simplexml_load_file($resultXML);
            //unlink($resultXML);
            getChangesetsListState($allchangeset_obj);
        }


        return $allchangeset_obj;
    }
    function countChangesInChangeset($id)
    {
        set_time_limit(10000000000);
        $data = $this->changesetsGetDownload($id);
        $created = $data->create;
        $modified = $data->modify;
        $deleted = $data->delete;
        //created
        $cnc = 0;
        $cwc = 0;
        $crc = 0;
        //modified
        $mnc = 0;
        $mwc = 0;
        $mrc = 0;
        //deleted
        $dnc = 0;
        $dwc = 0;
        $drc = 0;
        foreach ($data->create as $created) {
            foreach ($created->node as $cn) {
                $cnc += 1;
            }
            foreach ($created->way as $cw) {
                $cwc += 1;
            }
            foreach ($created->relation as $cr) {
                $crc += 1;
            }
        }
        foreach ($data->modify as $modified) {
            foreach ($modified->node as $mn) {
                $mnc += 1;
            }
            foreach ($modified->way as $mw) {
                $mwc += 1;
            }
            foreach ($modified->relation as $mr) {
                $mrc += 1;
            }
        }
        foreach ($data->delete as $deleted) {
            foreach ($deleted->node as $dn) {
                $dnc += 1;
            }
            foreach ($deleted->way as $dw) {
                $dwc += 1;
            }
            foreach ($deleted->relation as $dr) {
                $drc += 1;
            }
        }
        /*
        print '-----created-----';
        print '<br>';
        print 'nodes    ='.$cnc;
        print '<br>';
        print 'ways     ='.$cwc;
        print '<br>';
        print 'relations= '.$crc;
        print '<br>';
        print '<br>';
        print '-----modified-----';
        print '<br>';
        print 'nodes    ='.$mnc;
        print '<br>';
        print 'ways     ='.$mwc;
        print '<br>';
        print 'relations= '.$mrc;
        print '<br>';
        print '<br>';
        print '-----deleted-----';
        print '<br>';
        print 'nodes    ='.$dnc;
        print '<br>';
        print 'ways     ='.$dwc;
        print '<br>';
        print 'relations= '.$drc;
        print '<br>';
        print '<br>';	
        */

        $result = array(
            'created' => array(
                'node' => $cnc,
                'way' => $cwc,
                'relation' => $crc,
                ),
            'modified' => array(
                'node' => $mnc,
                'way' => $mwc,
                'relation' => $mrc,
                ),
            'deleted' => array(
                'node' => $dnc,
                'way' => $dwc,
                'relation' => $drc,
                ));
        return $result;
    }
    function getChangesetsDetails($id)
    {

        $data = $this->changesetsGetDownload($id);
        $data->asXML('files/changesetDetailsXML.xml');
    }

    function countChangesInChangeset_user($param = null)
    {
        set_time_limit(10000000000);
        $min_lon = null;
        $min_lat = null;
        $max_lon = null;
        $max_lat = null;
        $uid = null;
        $username = null;
        $created_after = null;
        $created_before = null;
        //Get parameters
        if ($param <> "") {
            foreach ($param as $key => $value) {
                if ($key == 'min_lon') {
                    $min_lon = $param['min_lon'];
                }
                if ($key == 'min_lat') {
                    $min_lat = $param['min_lat'];
                }
                if ($key == 'max_lon') {
                    $max_lon = $param['max_lon'];
                }
                if ($key == 'max_lat') {
                    $max_lat = $param['max_lat'];
                }
                if ($key == 'uid') {
                    $uid = $param['uid'];
                }
                if ($key == 'username') {
                    $username = $param['username'];
                }
                if ($key == 'created_after') {
                    $created_after = $param['created_after'];
                }
                if ($key == 'created_before') {
                    $created_before = $param['created_before'];
                }
            }
        }
        $data = $this->getAllChangesets($param);
        $total = array(
            'created' => array(
                'node' => 0,
                'way' => 0,
                'relation' => 0,
                ),
            'modified' => array(
                'node' => 0,
                'way' => 0,
                'relation' => 0,
                ),
            'deleted' => array(
                'node' => 0,
                'way' => 0,
                'relation' => 0,
                ));
        $count = 0;
        foreach ($data->changeset as $chs) {
            $count += 1;
            $cid = $chs->attributes()->id;
            print $cid;
            print '<br>';
            $result = $this->countChangesInChangeset($cid);
            print_r($result);
            print '<br>';
            $total['created']['node'] = $total['created']['node'] + $result['created']['node'];
            $total['created']['way'] = $total['created']['way'] + $result['created']['way'];
            $total['created']['relation'] = $total['created']['relation'] + $result['created']['relation'];

            $total['modified']['node'] = $total['modified']['node'] + $result['modified']['node'];
            $total['modified']['way'] = $total['modified']['way'] + $result['modified']['way'];
            $total['modified']['relation'] = $total['modified']['relation'] + $result['modified']['relation'];

            $total['deleted']['node'] = $total['deleted']['node'] + $result['deleted']['node'];
            $total['deleted']['way'] = $total['deleted']['way'] + $result['deleted']['way'];
            $total['deleted']['relation'] = $total['deleted']['relation'] + $result['deleted']['relation'];

        }
        print '<br>';
        print '<br>';
        print "bbox     = [ $min_lon, $min_lat, $max_lon, $max_lat ]";
        print '<br>';
        print 'The bbox Contains ' . $count . ' no. of changesets';
        print '<br>';
        return $total;
    }
    /************************helper********************************/
    function getCurrentTimestamp($timezone = null)
    {
        /*
        if ($timezone<>""){
        date_default_timezone_set($timezone);
        }
        */
        $date = date('Y-m-d');
        $time = date('H:i:s');
        $current_timestamp = $date . 'T' . $time . 'Z';
        return $current_timestamp;
    }
}
//End of Osm API

?>

	
