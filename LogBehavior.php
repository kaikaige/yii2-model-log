<?php
namespace kaikaige\modellog;

use yii;
use yii\base\Behavior;
use yii\db\BaseActiveRecord;
use yii\base\InvalidConfigException;
use app\models\LogModel;
use yii\helpers\Json;
/**
 * 日期修改
 * @author gaokai
 *
 */
class LogBehavior extends Behavior {
	
	public $modules = [
		'ms'
	];
	
	public function init() {
		parent::init();
	}
	
	public function events() {
		return [
			BaseActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
		];
	}
	
	
	public function afterUpdate($model) {
		if (!in_array(Yii::$app->controller->module->id, $this->modules)) {
			return;
		}
		$owner = $model->sender;
		$content = [];
		foreach ($model->changedAttributes as $k=>$v) {
			$data = [];
			if ($owner->$k != $v) {
				$content[] = [
					'attribute' => $k,
					'new_value' => $owner->$k,
					'old_value' => $v,
					'des' => $owner->getAttributeLabel($k).'由：'.$v.'修改为：'.$owner->$k 
				];
			}
		}
		if ($content) {
			$model = new LogModel([
				'user_id' => Yii::$app->user->id,	
				'content' => Json::encode($content),
				'module' => Yii::$app->controller->module->id,
				'model' => get_class($owner),
				'pk' => $owner->id
			]);
			$model->save();
		}
	}
}