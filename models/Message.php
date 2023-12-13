<?php

namespace app\models;

use Yii;
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

    public const SCENARIO_UPDATE = 'update';
    public const SCENARIO_CREATE = 'create';

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_UPDATE] = ['message'];
        return $scenarios;
    }

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
          [['to_user'], 'required', 'on' => self::SCENARIO_CREATE, 'when' => function() {
            return $this->type == 2;
          }],
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
          [['parent_id'], 'exist', 'targetAttribute' => 'id'],
          [['from_user'], 'exist', 'targetClass' => User::class, 'targetAttribute' => 'id'],
          [['to_user'], 'exist', 'targetClass' => User::class, 'targetAttribute' => 'id'],
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
        return [
          'answers' => function() {
            if (Yii::$app->user->identity) {
                return $this->getQuery(Yii::$app->user->identity->id)->andWhere(['parent_id' => $this->id])->all();
            } else {
                return $this->getQuery()->andWhere(['parent_id' => $this->id])->all();
            }
          }
        ];
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
        return $this->hasMany(static::class, ['parent_id' => 'id']);
    }

    public function getParentMessage()
    {
        return $this->hasOne(static::class, ['id' => 'parnet_id']);
    }

    public function findOneById($id)
    {
        return static::getQuery()->andWhere(['id' => $id])->one();
    }

    public function findOneByIdForUser($id, $userId)
    {
        return static::getQuery($userId)->andWhere(['id' => $id])->one();
    }

    public function findAllMessages()
    {
        return static::findAllMessagesQuery()->all();
    }

    public function findAllForUser($userId)
    {
        return static::findAllForUserQuery($userId)->all();
    }

    public function findAllMessagesQuery()
    {
        return static::getQuery();
    }

    public function findAllForUserQuery($userId)
    {
        return static::getQuery($userId);
    }

    private function getQuery($userId = null)
    {
        if ($userId) {
            return static::find()
            ->where(['or',
                ['type' => Message::VISIBLE_TO_ALL],
                ['type' => Message::VISIBLE_TO_REGISTERED_USERS],
                ['and',
                    ['type' => Message::VISIBLE_TO_SPECIFIC_USERS],
                    ['or',
                        ['to_user' => $userId],
                        ['from_user' => $userId]
                    ]
                ]
            ]);
        } else {
            return static::find()
            ->where(['type' => Message::VISIBLE_TO_ALL]);
        }
    }

}
