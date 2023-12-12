<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\Url;
use yii\web\Link;
use yii\web\Linkable;

class Message extends ActiveRecord implements Linkable
{
    public const VISIBLE_TO_ALL = 0;
    public const VISIBLE_TO_REGISTERED_USERS = 1;
    public const VISIBLE_TO_SPECIFIC_USERS = 2;

    /**
     * message
     * type: 1 - all, 2 - only registered user, 3 - with specify user
     * from_user
     * to_user
     * created_at
     * updated_at
     */

    public static function tableName(): string {
        return 'messages';
    }


    public function behaviors()
    {
        return [
            'timestamp'  => [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
            ],
        ];
    }

    public function rules(): array {
        return [
          [['message', 'type'], 'required'],
          [
            [
                'parent_id',
                'message',
                'type',
                'to_user',
                'created_at',
                'updated_at'
            ],
            'safe'
          ],
          [['from_user'], 'default', 'value' => 1]
        ];
    }

    public function attributes() {
        return [
            'id',
            'parent_id',
            'message',
            'type',
            'from_user',
            'to_user',
            'created_at',
            'updated_at'
        ];
    }

    public function extraFields() {
        return parent::extraFields();
    }

    /**
     * @inheritdoc
     */
    public function getLinks()
    {
        return [
            Link::REL_SELF => Url::to(['message/view', 'id' => $this->id], true),
        ];
    }

    public function getChildMessage()
    {

    }

    public function getParentMessge()
    {

    }

}
