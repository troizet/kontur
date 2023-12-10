<?php

namespace app\controllers;

use app\models\Message;
use app\models\MessageSearch;
use Dersonsena\JWTTools\JWTSignatureBehavior;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\auth\HttpBearerAuth;
use yii\helpers\Url;
use yii\rest\ActiveController;
use yii\web\ServerErrorHttpException;

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

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'except' => ['login'] // it's doesn't run in login action
        ];

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
        unset($actions['create']);
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
        $model = new Message();

        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        
        $model->from_user = Yii::$app->user->identity->id;
         
        if ($model->save()) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);
            $id = implode(',', $model->getPrimaryKey(true));
            $response->getHeaders()->set('Location', Url::toRoute(['view', 'id' => $id], true));
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $model;
    }

    public function actionUpdate()
    {

    }

    public function actionDelete()
    {

    }

}