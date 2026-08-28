<?php 
//importing required files 
require_once 'DbOperation.php';
require_once 'Firebase.php';
require_once 'Push.php'; 

$db = new DbOperation();

$msgNot['title']   = "5º Congresso Paranaense de Saúde Pública/Coletiva";
$msgNot['message'] = "Ola, obrigado por instalar nosso aplicativo.";
$msgNot['email'] = "";
$msgNot['image'] = "";
/*Ro*/
$msgNot['token'] = "d8pa6aPrqwA:APA91bFEO-oap6d5nTlJcrwknjaLzHUJ7V_1cbgxOQnEVIiqbgRwE1urQO8sabyaVIDfbdCBwiPFnfnDL6QMYkj9_A2xK-o_BK5ykYUP5PKP-F99sjJQ1GTXECod3ZA1_cIZV14aJjmw";
/*Caua*/
$msgNot['token'] = "dpuVRxe3nOw:APA91bEvvSySQk4oTaokqkT0YdxNtpsCZ96krEFkpew147-BXTZKBADJKX4VzJho1_imEvfgUIh16mSVBG1Iov5pvq4uLIxOgAlGTu4RYklDPfOqq-dNcPsL36EiD3RqKmU5qLqq5rL9";
/*Zaka*/
$msgNot['token'] = "ccFvX0muJuw:APA91bENfqyeIZEf5RXY0HJdB2dWRTII518SmgsPLEPgdRn3sY1zdGxOkVVyMTCNwCsMl-MXvAqbJxr3WoVOd408dbshuSTr1jafqZGYlTGyCZr2UaJ1BDF7NdnSXcgIv6WNa-Cq97Ok";

$response = array(); 

if($_SERVER['REQUEST_METHOD']=='POST'){	
	//hecking the required params 
	if(isset($msgNot['title']) and isset($msgNot['message']) and isset($msgNot['email'])){

		//creating a new push
		$push = null; 
		//first check if the push has an image with it
		if(isset($msgNot['image'])){
			$push = new Push(
					$msgNot['title'],
					$msgNot['message'],
					$msgNot['image']
				);
		}else{
			//if the push don't have an image give null in place of image
			$push = new Push(
					$msgNot['title'],
					$msgNot['message'],
					null
				);
		}

		//getting the push from push object
		$mPushNotification = $push->getPush(); 
//                print_r($mPushNotification);
//                exit();
                if ($msgNot['token']!=''){
                    $devicetoken = $msgNot['token'];
                }else{
                    //getting the token from database object 
                    $devicetoken = $db->getTokenByEmail($msgNot['email']);
                }

		//creating firebase class object 
		$firebase = new Firebase(); 

		//sending push notification and displaying result 
		echo $firebase->send($devicetoken, $mPushNotification);
	}else{
		$response['error']=true;
		$response['message']='Parameters missing';
	}
}else{
	$response['error']=true;
	$response['message']='Invalid request';
}

echo json_encode($response);