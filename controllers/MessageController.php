<?php

namespace app\controllers;

use app\models\Message;
use yii\data\ActiveDataProvider;
use yii\rest\ActiveController;

class MessageController extends ActiveController
{
    public $modelClass = Message::class;
    
//    public function behaviors()
//    {
//        $behaviors = parent::behaviors();
//        $behaviors['authenticator'] = [
//            'class' => AccessTokenAuth::class,
//        ];
//
//        return $behaviors;
//    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        /** @var array<string, array<string, mixed>> $actions */
        $actions = parent::actions();
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
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