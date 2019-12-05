<?php
namespace backend\models;

use Yii;
use yii\base\Exception;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $access_token
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $password write-only password
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_INACTIVE = 9;
    const STATUS_ACTIVE = 10;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['status', 'default', 'value' => self::STATUS_INACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_DELETED]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
		$identity = static::findOne(['access_token' => $token]);
		
		if (static::isAccessTokenValid($token)) {

		} else {
			return null;
		}

		return static::findOne(['access_token' => $token]);
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

	/**
	 * Finds out if access token is valid
	 *
	 * @param string $accessToken access token
	 * @return bool
	 */
	public static function isAccessTokenValid($accessToken)
	{
		if (empty($accessToken)) {
			return false;
		}

		$timestamp = (int) substr($accessToken, strrpos($accessToken, '_') + 1);
		$expire = Yii::$app->params['user.passwordResetTokenExpire'];
		return $timestamp + $expire >= time();
	}

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

	/**
	 * Generates password hash from password and sets it to the model
	 *
	 * @param string $password
	 * @throws Exception
	 */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

	/**
	 * Generates new access token
	 * @throws Exception
	 */
	public function generateAccessToken()
	{
		$this->access_token = Yii::$app->security->generateRandomString();
	}

	/**
	 * @inheritDoc
	 */
	public function getAccessToken()
	{
		return $this->access_token;
	}

	/**
	 * @inheritDoc
	 * @throws NotSupportedException
	 */
	public function getAuthKey()
	{
		throw new NotSupportedException('"getAuthKey" is not implemented.');
	}

	/**
	 * @inheritDoc
	 */
	public function validateAccessToken($accessToken)
	{
		return $this->getAccessToken() === $accessToken;
	}

	/**
	 * @inheritDoc
	 * @throws NotSupportedException
	 */
	public function validateAuthKey($authKey)
	{
		throw new NotSupportedException('"validateAuthKey" is not implemented.');
	}
}
