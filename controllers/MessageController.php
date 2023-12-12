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
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
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
        unset($actions['update']);
        unset($actions['delete']);
        unset($actions['view']);
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

    public function actionView($id)
    {
//        SELECT
//                *
//        FROM
//                `messages`
//        WHERE
//                id = 3 and
//                (type = 0
//                        or type = 1 
//                        or ((type = 2 and to_user = 1) or (type = 2 and from_user = 1)))
        $query = Message::find()
                ->where(['id' => $id])
                ->andWhere(['type' => Message::VISIBLE_TO_ALL]);

//        if (Yii::$app->user->identity) {
//            $query->orWhere(['type' => Message::VISIBLE_TO_REGISTERED_USERS])
//                ->orWhere(['type' => Message::VISIBLE_TO_SPECIFIC_USERS, 'to_user' => Yii::$app->user->identity->id] )
//                ->orWhere(['type' => Message::VISIBLE_TO_SPECIFIC_USERS, 'from_user' => Yii::$app->user->identity->id] );
//        }

        $message = $query->one();

        return $message;
    }

    public function actionCreate()
    {
        $model = new Message();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        $model->from_user = Yii::$app->user->identity->id;
        //добавить проверку на существование пользователя to_userы
        if ($model->save()) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);
            $response->getHeaders()->set('Location', Url::toRoute(['view', 'id' => $model->id], true));
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the message for unknown reason.');
        }

        return $model;
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->from_user === Yii::$app->user->identity->id) {
            $model->load(Yii::$app->getRequest()->getBodyParams(), '');
            $model->from_user = Yii::$app->user->identity->id;

            if ($model->save() === false && !$model->hasErrors()) {
                throw new ServerErrorHttpException('Failed to update the message for unknown reason.');
            }
        } else {
            throw new ForbiddenHttpException('Разрешено обновлять только свои сообщения');
        }

        return $model;
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        //добавить проверку на существование ответа, если есть, то просто очищаем текст
        //Добавить мягкое удаление?
        if ($model->from_user === Yii::$app->user->identity->id) {
            if ($model->delete() === false) {
                throw new ServerErrorHttpException('Failed to delete the message for unknown reason.');
            }

            Yii::$app->getResponse()->setStatusCode(204);
        } else {
            throw new ForbiddenHttpException('Разрешено удалять только свои сообщения');
        }

        return $model;
    }

    public function findModel($id)
    {
        $model = Message::findOne($id);

        if (isset($model)) {
            return $model;
        }

        throw new NotFoundHttpException("Message not found: $id");
    }

}