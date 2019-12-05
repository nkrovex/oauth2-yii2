<?php

namespace backend\controllers;

use yii\filters\auth\HttpBearerAuth;
use yii\rest\ActiveController;

class UserController extends ActiveController
{
	public $modelClass = 'backend\models\User';

	public function behaviors()
	{
		$behaviors = parent::behaviors();
		$behaviors['authenticator'] = [
			'class' => HttpBearerAuth::className(),
		];
		return $behaviors;
	}
}
