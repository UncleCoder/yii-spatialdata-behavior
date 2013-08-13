<?php

/**
 * SpatialDataBehavior class file.
 *
 * @author UncleCoder <UncleCoder@russia.ru>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2013 UncleCoder
 * @license http://www.yiiframework.com/license/
 */

/**
 * SpatialDataBehavior allows to interact with spatial-fields like regular arrays
 *
 * @author UncleCoder <UncleCoder@russia.ru>
 * @version 0.3
 */
class SpatialDataBehavior extends CActiveRecordBehavior {

	public $spatialFields = array();
	private $_storedFields = array();

	public function beforeSave($event) {
		$owner = $event->sender;
		foreach ($this->spatialFields as $field) {
			if (!is_array($owner->$field))
				continue;
			$type = $owner->getTableSchema()->getColumn($field)->dbType;
			$lineString = $this->arrayToGeom($owner->$field);
			$this->_storedFields[$field] = $owner->$field;
			$owner->$field = new CDbExpression("GeomFromText(:data" . $field . ")", array(":data" . $field => $type . '(' . $lineString . ')'));
		}
		$event->isValid = true;
	}

	public function afterSave($event) {
		$owner = $event->sender;
		foreach ($owner->spatialFields as $field) {
			if (isset($owner->$field)) {
				if (isset($this->_storedFields[$field])) {
					$owner->$field = $this->_storedFields[$field];
				}
			}
		}
	}

	public function beforeFind($event) {
		$owner = $event->sender;
		$criteria = new CDbCriteria();
		$lineString = '';
		foreach ($this->spatialFields as $field) {

			$lineString.='AsText(' . $field . ') AS ' . $field . ',';
		}
		$lineString = substr($lineString, 0, -1);
		$criteria->select = (($owner->DBCriteria->select == '*') ? '*, ' : '') . $lineString;
		$owner->dbCriteria->mergeWith($criteria);
		$event->isValid = true;
	}

	public function afterFind($event) {
		$owner = $event->sender;
		foreach ($this->spatialFields as $field) {
			if (!isset($owner->$field))
				continue;
			$type = $owner->getTableSchema()->getColumn($field)->dbType;
			$str = str_replace(' ', ',', '[' . (str_replace(',', '],[', substr($owner->$field, strlen($type) + 1, -1))) . ']');
			if (strpos($str, '],[') !== false) {
				$str = '[' . $str . ']';
			}
			$owner->$field = CJSON::decode(str_replace(')]', ']]', str_replace('[(', '[[', $str)));
		}
	}

	public function arrayToGeom($data) {
		return str_replace(']', '', str_replace('[', '', str_replace(']]]', ')]]', str_replace('[[[', '[[(', str_replace('],[', '),(', str_replace('] [', ',', str_replace(',', ' ', CJSON::encode($data))))))));
	}

	/**
	 * Logs a message.
	 *
	 * @param string $message Message to be logged
	 * @param string $level Level of the message (e.g. 'trace', 'warning',
	 * 'error', 'info', see CLogger constants definitions)
	 */
	public static function log($message, $level = 'error') {
		Yii::log($message, $level, __CLASS__);
	}

	/**
	 * Dumps a variable or the object itself in terms of a string.
	 *
	 * @param mixed variable to be dumped
	 */
	protected function dump($var = 'dump-the-object', $highlight = true) {
		if ($var === 'dump-the-object') {
			return CVarDumper::dumpAsString($this, $depth = 15, $highlight);
		}
		else {
			return CVarDumper::dumpAsString($var, $depth = 15, $highlight);
		}
	}

}