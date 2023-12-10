<?php

namespace app\controllers;

use app\models\Message;
use app\models\MessageSearch;
use Dersonsena\JWTTools\JWTSignatureBehavior;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\auth\HttpBearerAuth;
use yii\rest\ActiveController;

class MessageController extends ActiveController
{
    public $modelClass = Message::class;
    
    
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['jwtValidator'] = [
            'class' => JWTSignatureBehavior::class,
            'secretKey' => Yii::$app->params['jwt']['secret'],
            'except' => ['login'] // it's doesn't run in login action
        ];

//        $behaviors['authenticator'] = [
//            'class' => HttpBearerAuth::class,
//            'except' => ['login'] // it's doesn't run in login action
//        ];

        return $behaviors;
    }  
    
    /**
     * @inheritdoc
     */
    public function actions()
    {
        /** @var array<string, array<string, mixed>> $actions */
        $actions = parent::actions();
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        $actions['create'] = [$this, 'actionCreate'];
        return $actions;
    }

    /**
     * @return ActiveDataProvider
     */
    public function prepareDataProvider()
    {
        $searchModel = new MessageSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        
        return $dataProvider;
    }    
    
    public function actionView()
    {

    }

    public function actionCreate()
    {

    }

    public function actionUpdate()
    {

    }

    public function actionDelete()
    {

    }



}