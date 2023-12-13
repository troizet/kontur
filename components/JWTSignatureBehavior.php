<?php

namespace app\components;

use Dersonsena\JWTTools\JWTSignatureBehavior as Signature;
use yii\web\UnauthorizedHttpException;
/*
 * Description of JWTSignatureBehavior
 *
 * @author troizet
 */
class JWTSignatureBehavior extends Signature
{
    public function beforeAction($action)
    {
        try {
            return parent::beforeAction($action);
        } catch (UnauthorizedHttpException $ex) {
            return true;
        }
    }
}
