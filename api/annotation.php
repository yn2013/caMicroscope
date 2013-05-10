<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);
include_once("RestRequest.php");
$callList = file_get_contents("bindaasCalls");
$callArray = explode(',',$callList);
$getUrl =  $callArray[0];
$postUrl = $callArray[1];
switch ($_SERVER['REQUEST_METHOD'])
{
	case 'GET':
		if(isset($_GET["iid"]))
		{
			$iid=$_GET["iid"];
			$maxWidth=$_GET["maxWidth"];
			$maxHeight=$_GET["maxHeight"];
			$url = $getUrl . $iid;	
			//$url = 'http://localhost:9099/services/annotations/Annotations/query/getAnnotsByID?iid=' . $iid;
			$getRequest = new RestRequest($url,'GET');
			$getRequest->execute();
			//Figure out how to parse reponse
			$annotationList = json_decode($getRequest->responseBody);
			
			foreach($annotationList as $singleAnnot)
			{
				if($singleAnnot->type=='rect' || $singleAnnot->type=='ellipse')
				{
					$x1 = $singleAnnot->x;
					$y1 = $singleAnnot->y;
					$w1 = $singleAnnot->w;
					$h1 = $singleAnnot->h;

					$singleAnnot->x = $x1/$maxWidth;
					$singleAnnot->y = $y1/$maxHeight;
					$singleAnnot->w = $w1/$maxWidth;
					$singleAnnot->h = $h1/$maxHeight;
				}
			
				if($singleAnnot->type=='pencil' || $singleAnnot->type=='polyline' || $singleAnnot->type=='line')
				{
					$x2 = $singleAnnot->x;
					$y2 = $singleAnnot->y;
					$w2 = $singleAnnot->w;
					$h2 = $singleAnnot->h;
					$points = $singleAnnot->points;
					$pointsArray = explode(' ',$points);
					$points1 = "";
					foreach($pointsArray as $singlePoint)
					{
						$pointXY = explode(',',$singlePoint);
						if(count($pointXY) >= 2)
						{
							$pointX = $pointXY[0]/$maxWidth;
							$pointY = $pointXY[1]/$maxHeight;
							$points1 .= $pointX . "," . $pointY . " ";
						}
					}
					$points1 = substr($points1,0,-1);	
					$singleAnnot->x = $x2/$maxWidth;
					$singleAnnot->y = $y2/$maxHeight;
					$singleAnnot->w = $w2/$maxWidth;
					$singleAnnot->h = $h2/$maxHeight;
					$singleAnnot->points = $points1;
				}	
			}
			if(json_encode($annotationList) == null)
				echo json_encode("NoAnnotations");
			else
				echo json_encode($annotationList);
			break;
		}
	case 'POST':
		$annotationList =$_POST["annot"];
		$maxWidth = $_POST["maxWidth"];
		$maxHeight = $_POST["maxHeight"];
		$url = $postUrl;
		//$url = 'http://localhost:9099/services/annotations/Annotations/submit/singleInput';
		$count = count($annotationList);
		$newestAnnot = $annotationList[$count-1];
		if($newestAnnot['type'] == 'rect'||$newestAnnot['type'] == 'ellipse')
		{
			$x = $newestAnnot['x'];
			$y = $newestAnnot['y'];
			$w = $newestAnnot['w'];
			$h = $newestAnnot['h'];
			
			$newestAnnot['x'] = $x * $maxWidth;
			$newestAnnot['y'] = $y * $maxHeight;
			$newestAnnot['w'] = $w * $maxWidth;
			$newestAnnot['h'] = $h * $maxHeight;			
		}
		
		if($newestAnnot['type']=='pencil' || $newestAnnot['type']=='polyline' || $newestAnnot['type']=='line')
		{
			$x3 = $newestAnnot['x'];
			$y3 = $newestAnnot['y'];
			$w3 = $newestAnnot['w'];
			$h3 = $newestAnnot['h'];
			$points3 = $newestAnnot['points'];
			$pointsArray = explode(' ',$points3);
			$points2 = "";
			foreach($pointsArray as $singlePoint)
			{
				$pointXY = explode(',',$singlePoint);
				if(count($pointXY) >= 2)
				{
					$pointX = $pointXY[0] * $maxWidth;
					$pointY = $pointXY[1] * $maxHeight;
					$points2 .= $pointX . "," . $pointY . " ";
				}
			}	
			$newestAnnot['x'] = $x3 * $maxWidth;
			$newestAnnot['y'] = $y3 * $maxHeight;
			$newestAnnot['w'] = $w3 * $maxWidth;
			$newestAnnot['h'] = $h3 * $maxHeight;
			$newestAnnot['points'] = $points2;		
		}
		$postRequest = new RestRequest($url,'POST',json_encode($newestAnnot));
		$postRequest->execute();
		echo(json_encode("success"));
		break;
}
?>
