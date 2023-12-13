<?php

namespace app\controllers;

use app\models\LoginForm;
use app\models\User;
use Dersonsena\JWTTools\JWTTools;
use Yii;
use yii\rest\Controller;

class UserController extends Controller
{

    protected function verbs()
    {
        return [
            'register'  => ['POST'],
            'login'  => ['POST'],
        ];
    }

    public function actionRegister()
    {
        $model = new User();
        $post = Yii::$app->request->post();
        if ($model->load($post, '') && $model->save()) {
            return ['success' => true];
        }
        return ['success' => false];
    }

    public function actionLogin()
    {
        $model = new LoginForm();
        $post = Yii::$app->request->post();
        if ($model->load($post, '') && $model->login()) {

            $user = $model->getUser();

            if ($user) {
                $token = JWTTools::build(Yii::$app->params['jwt']['secret'])
                    ->withModel($user, ['id', 'username'])
                    ->getJWT();

                return ['success' => true, 'token' => $token];
            }
        }

        return ['success' => false];
    }
}