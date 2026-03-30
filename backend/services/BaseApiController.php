<?php

namespace app\services;

use yii\web\Controller;
use yii\filters\Cors;

class BaseApiController extends Controller
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['cors'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => false,
                'Access-Control-Max-Age' => 86400,
            ],
        ];
        return $behaviors;
    }

    protected function success($data = null, $message = 'ok')
    {
        return [
            'code' => 0,
            'message' => $message,
            'data' => $data,
        ];
    }

    protected function error($message = 'error', $code = 1, $data = null)
    {
        if ($code === 1004) {
            \Yii::$app->response->statusCode = 404;
        } elseif ($code >= 5000) {
            \Yii::$app->response->statusCode = 500;
        } else {
            \Yii::$app->response->statusCode = 400;
        }

        return [
            'code'    => $code,
            'message' => $message,
            'data'    => $data,
        ];
    }

    protected function getJsonBody()
    {
        return \Yii::$app->request->getBodyParams();
    }
}
