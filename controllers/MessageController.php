<?php

namespace app\controllers;

use app\components\JWTSignatureBehavior;
use app\models\Message;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\auth\HttpBearerAuth;
use yii\helpers\Url;
use yii\rest\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class MessageController extends Controller
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['jwtValidator'] = [
            'class' => JWTSignatureBehavior::class,
            'secretKey' => Yii::$app->params['jwt']['secret'],
        ];

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'optional' => ['index', 'view']
        ];

        return $behaviors;
    }

    protected function verbs()
    {
        return [
            'index'  => ['GET', 'HEAD'],
            'view'  => ['GET', 'HEAD'],
            'update'  => ['PUT', 'PATCH'],
            'create'  => ['POST'],
            'delete'  => ['DELETE']
        ];
    }

    public function actionIndex()
    {
        $messages = [];

        if (Yii::$app->user->identity) {
            $messages = Message::findForUserQuery(Yii::$app->user->identity->id);
        } else {
            $messages = Message::findMessagesQuery();
        }

        $provider = new ActiveDataProvider([
            'query' => $messages
        ]);

        return $provider;
    }

    public function actionView($id)
    {
        $message = null;

        if (Yii::$app->user->identity) {
            $message = Message::findOneByIdForUser($id, Yii::$app->user->identity->id);
        } else {
            $message = Message::findOneById($id);
        }
        return $message;
    }

    public function actionCreate()
    {
        $model = new Message();
        $model->scenario = Message::SCENARIO_CREATE;
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        $model->from_user = Yii::$app->user->identity->id;
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
        $model->scenario = Message::SCENARIO_UPDATE;

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

        if ($model->from_user != Yii::$app->user->identity->id) {
            throw new ForbiddenHttpException('Разрешено удалять только свои сообщения');
        }
        if ($model->childMessage) {
            throw new ForbiddenHttpException('У сообщения имеются ответы');
        }

        if ($model->delete() === false) {
            throw new ServerErrorHttpException('Failed to delete the message for unknown reason.');
        }

        Yii::$app->getResponse()->setStatusCode(204);

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