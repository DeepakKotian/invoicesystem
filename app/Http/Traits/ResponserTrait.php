<?php

namespace App\Http\Traits; 

trait ResponserTrait{

    protected function successResponse($msg,$data = NULL, $code = 200)
	{		
		(is_array($msg))?$message = $msg:$message[] = $msg;
		return response()->json([
			'status'=> 'Success', 
			'message' => $message, 
			'data' => $data
		], $code);
	}

	protected function errorResponse($msg, $code = 400)
	{	
		(is_array($msg))?$message = $msg:$message[] = $msg;
		return response()->json([
			'status'=>'Error',
			'message' => $message,
			'data' => null
		], $code);
	}
}