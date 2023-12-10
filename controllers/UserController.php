<?php

namespace app\controllers;

use app\models\LoginForm;
use app\models\User;
use Dersonsena\JWTTools\JWTSignatureBehavior;
use Dersonsena\JWTTools\JWTTools;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\rest\Controller;

/**
 * Делаем простую Basic авторизацию или jwt?
 */
class UserController extends Controller
{
    
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['jwtValidator'] = [
            'class' => JWTSignatureBehavior::class,
            'secretKey' => Yii::$app->params['jwt']['secret'],
            'except' => ['login'] // it's doesn't run in login action
        ];

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'except' => ['login'] // it's doesn't run in login action
        ];

        return $behaviors;
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
            
    public function actionLogout()
    {

    }
}