<?php

use backend\models\User;
use yii\db\Migration;

/**
 * Class m191204_100915_changed_users_table_and_added_new_users
 */
class m191204_100915_changed_users_table_and_added_new_users extends Migration
{
	/**
	 * {@inheritdoc}
	 * @throws \yii\base\Exception
	 */
    public function safeUp()
    {
		$this->addColumn('{{%user}}', 'access_token_expires_at', $this->integer()->notNull());

		$this->dropColumn('{{%user}}', 'verification_token');

    	$this->renameColumn('{{%user}}', 'auth_key', 'access_token');

		$this->insert('user', [
			'username' => 'admin',
			'access_token' => Yii::$app->security->generateRandomString(),
			'access_token_expires_at' => time() + Yii::$app->params['user.accessTokenExpire'],
			'password_hash' => Yii::$app->security->generatePasswordHash('admin'),
			'email' => 'nkrovex@gmail.com',
			'status' => User::STATUS_ACTIVE,
			'created_at' => time(),
			'updated_at' => time()
		]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
		$this->delete('{{%user}}', ['username' => 'admin']);

		$this->renameColumn('{{%user}}', 'access_token', 'auth_key');

		$this->addColumn('{{%user}}', 'verification_token', $this->string()->defaultValue(null));

		$this->dropColumn('{{%user}}', 'access_token_expires_at');
    }
}
