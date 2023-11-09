<?php 
/**
 * Типаж для вывода логов
 */
namespace Shasoft\Console;
/**
 * Типаж для вывода логов
 */
trait TraitLog {
	/**
	 * @var boolean Глобальный уровень логирования
	 */
	protected $g_logLevel = 0;
	/**
	 * Установить/получить глобальный уровень логирования. Все 
	 * @param null|boolean integer $value Новое значение уровня
	 *                            <li><b>null</b> - Получить текущее значение</li>
	 *                            <li><b>false</b> - ничего не выводить</li>
	 *                            <li><b>true</b> - выводить все</li>
	 *                            <li><b>integer</b> - выводить если значение меньше-равно указанного уровня</li>
	 */
	public function setLogLevel($value=null) {
		if( !is_null($value) ) {
			$this->g_logLevel = $value;
			return $this;
		}
		return $this->g_logLevel;
	}
	/**
	 * @var boolean Уровень логирования
	 */
	protected $logLevel = 0;
	/**
	 * Установить/получить уровень логирования
	 * @param null|boolean integer $value Новое значение уровня
	 *                            <li><b>null</b> - Получить текущее значение</li>
	 *                            <li><b>integer</b> - выводить если значение меньше-равно указанного уровня</li>
	 */
	public function logLevel($value=null) {
		if( !is_null($value) ) {
			$this->logLevel = $value;
			return $this;
		}
		return $this->logLevel;
	}
	/**
	 * Выводить?
	 * @return boolean Выводится лог или нет
	 */
	public function isOutput() { 
		if( $this->g_logLevel===true ) {
			return true;
		}
		if( $this->g_logLevel===false ) {
			return false;
		}
		if( $this->logLevel<=$this->g_logLevel ) {
			return true;
		}
		return false;
	}
}
