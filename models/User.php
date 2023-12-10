<?php

namespace app\models;

use Dersonsena\JWTTools\JWTTools;
use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{    
    public static function tableName(): string {
        return "users";
    }
    
    public function rules(): array {
        return [
          [['username', 'password'], 'required'],  
          [['username', 'password'], 'string'],  
          [['username', 'password'], 'safe'],  
        ];
    }

        /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::find()->where(['id' => $id])->one();
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $decodedToken = JWTTools::build(Yii::$app->params['jwt']['secret'])
            ->decodeToken($token);

        return static::findOne(['id' => $decodedToken->sub]);
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::find()->where(['username' => $username])->one();     
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return $this->password === $password;
    }
}
