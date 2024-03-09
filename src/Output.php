<?php

/**
 * Класс вывода данных на консоль в цвете
 */

namespace Shasoft\Console;

/**
 * Класс вывода данных на консоль в цвете
 */
class Output
{
	// Типаж для вывода логов
	use TraitLog;
	/**
	 * @var \Shasoft\Output\Output Текущий вывод
	 */
	static protected $output = false;
	/**
	 * Получить консоль
	 * @return \Shasoft\Output\Output
	 */
	static function get(): static
	{
		if (self::$output == false) {
			self::$output = new Output;
			// Цвета по умолчанию
			self::$output
				->setDefault('white', 'black')->reset()
				// Стили по умолчанию
				->setStyle('error', 'light_red', null)
				->setStyle('info', 'light_blue', null)
				->setStyle('ok', 'light_green', null)
				->setStyle('debug', 'yellow', null);
		}
		return self::$output;
	}
	/**
	 * @var array[2] Значения по умолчанию
	 */
	protected $defColors;
	/**
	 * Установить пробельный символ
	 * @param string $color Цвет текста
	 * @param string $bgColor Цвет фона
	 */
	public function setDefault(string $color, string $bgColor)
	{
		$this->defColors = [$color, $bgColor];
		return $this;
	}
	/** 
	 * Изменение цвета фона и/или цвета фона
	 */
	protected function _onChange(): static
	{
		// Цвет текста
		$color = array_key_exists($this->color, self::$colors) ? self::$colors[$this->color] : $this->color;
		// Цвет фона
		$bgColor = array_key_exists($this->bgColor, self::$bgColors) ? self::$bgColors[$this->bgColor] : $this->bgColor;
		// Вывести в консоль изменение цветов
		return $this->write("\033[" . $color . "m" . "\033[" . $bgColor . "m");
	}
	/**
	 * @var string пробельный символ
	 */
	protected $space = ' ';
	/**
	 * Установить пробельный символ
	 * @param string $space Пробельный символ
	 */
	public function setSpace($space)
	{
		$this->space = $space;
		return $this;
	}
	/**
	 * @var integer Размер табулятора в пробелах
	 */
	protected $tab = 3;
	/**
	 * Установить значение размера табулятора 
	 * @param integer $tab Новый размер табулятора
	 */
	public function setTabSize($tab)
	{
		$this->tab = $tab;
		return $this;
	}
	/**
	 * @var integer Отступ (задается в табуляторах)
	 */
	protected $indent = 0;
	/**
	 * Установить/получить значение размера табулятора. 
	 *
	 * Метод позволяет получить текущее значение отступа (вызов без параметров).
	 * При вызове с одним параметром устанавливает значение отступа в это значение.
	 * При вызове с двумя параметрами и вторым параметром true значение изменяется на значение первого параметра.
	 * @param null|integer $delta optional Изменение отступа
	 * @param null|true $abs optional Значение $delta нужно установить как новое значение
	 * @return integer|\Shasoft\Output\Output Значение отступа или указатель на вывод (для случая если идет изменение значения отступа)
	 */
	public function indent($delta = null, $abs = null)
	{
		if (!is_null($delta)) {
			if (!is_null($abs) && $abs === true) {
				$this->indent = $delta;
			} else {
				$this->indent += $delta;
			}
			return $this;
		}
		return $this->indent;
	}
	/**
	 * Закончить вывод строки
	 * @param boolean $resetColor Сбрасывает цвета в значения по умолчанию
	 * @return \Shasoft\Output\Output Указатель на вывод
	 */
	public function enter(bool $resetColor = true): static
	{
		if ($resetColor) {
			$this->reset();
		}
		return $this->write("\n");
	}
	/**
	 * @var string Буфер вывода
	 */
	protected $buffer = '';
	/**
	 * Вывести строку
	 * @param array $args Значения для вывода
	 * @return \Shasoft\Output\Output Указатель на вывод
	 */
	public function write(...$args): static
	{
		// Нужно выводить?
		if ($this->isOutput()) {
			// Преобразуем все значения в строку
			$str = '';
			foreach ($args as $arg) {
				$str .= $this->_val2str($arg);
			}
			// Добавим строку к буферу
			$this->buffer .= $str;
			// Разделим буфер по строкам
			$lines = explode("\n", $this->buffer);
			// Последнюю строку вернём в буфер (может в эту строку ещё что-то будут выводить)
			$this->buffer = array_pop($lines);
			// Отступ в пробелах
			$spaces = str_repeat($this->space, $this->indent * $this->tab);
			foreach ($lines as $i => $line) {
				echo "\n" . $spaces . $line;
			}
		}
		return $this;
	}
	/**
	 * write+enter()
	 * @param array $args Значения для вывода
	 * @return \Shasoft\Output\Output Указатель на вывод
	 */
	public function writeln(...$args): static
	{
		call_user_func_array([$this, 'write'], $args);
		return $this->enter();
	}
	/**
	 * Трансформировать значение в строку для вывода
	 * @param mixed $val Значение для трансформации
	 * @return string Строковое представление
	 */
	protected function _val2str($val)
	{
		$ret = $val;
		if (!is_string($val)) {
			if (is_callable($val)) {
				$ret = 'callable::';
			} else if (is_resource($val)) {
				$ret = get_resource_type($val);
				//} else if( is_object($val) ) {
				//$ret = get_resource_type($val);
			} else {
				$ret = @var_export($val, true);
			}
		}
		return $ret;
	}
	/**
	 * @var array Цвет текста
	 */
	static protected $colors = [
		'black' => '0;30',
		'dark_gray' => '1;30',
		'blue' => '0;34',
		'light_blue' => '1;34',
		'green' => '0;32',
		'light_green' => '1;32',
		'cyan' => '0;36',
		'light_cyan' => '1;36',
		'red' => '0;31',
		'light_red' => '1;31',
		'purple' => '0;35',
		'light_purple' => '1;35',
		'brown' => '0;33',
		'yellow' => '1;33',
		'light_gray' => '0;37',
		'white' => '1;37'
	];
	/**
	 * @var string Цвет текста
	 */
	protected $color = '';
	/**
	 * Установить цвет текста
	 * @param string $color Значение цвета
	 * @return \Shasoft\Output\Output Указатель на вывод
	 */
	public function color($color)
	{
		$this->color = $color;
		return $this->_onChange();
	}
	/**
	 * @var array Цвета фона
	 */
	static protected $bgColors = [
		'black' => '40',
		'red' => '41',
		'green' => '42',
		'yellow' => '43',
		'blue' => '44',
		'magenta' => '45',
		'cyan' => '46',
		'light_gray' => '47'
	];
	/**
	 * @var string Цвет фона
	 */
	protected $bgColor = '';
	/**
	 * Установить цвет фона
	 * @param string $color Значение цвета
	 * @return \Shasoft\Output\Output Указатель на вывод
	 */
	public function bgColor($color)
	{
		$this->bgColor = $color;
		return $this->_onChange();
	}
	/**
	 * Установить цвета по умолчанию
	 * @return \Shasoft\Output\Output Указатель на вывод	 
	 */
	public function reset()
	{
		$this->color = $this->defColors[0];
		$this->bgColor = $this->defColors[1];
		return $this->_onChange();
	}
	/**
	 * @var array Стили
	 */
	protected $styles = [];
	/**
	 * Установить параметры стиля
	 * @param string $name Имя стиля
	 * @param null|string $color Цвет текста
	 * @param null|string $bgColor Цвет фона
	 * @return \Shasoft\Output\Output Указатель на вывод	 
	 */
	public function setStyle($name, $color = null, $bgColor = null)
	{
		$this->styles[$name] = [$color, $bgColor];
		return $this;
	}
	/**
	 * Включить стиль
	 * @param string $name Имя стиля
	 * @return \Shasoft\Output\Output Указатель на вывод	 
	 */
	public function style(string $name): static
	{
		$this->reset();
		if (array_key_exists($name, $this->styles)) {
			$colors = $this->styles[$name];
			if (!is_null($colors[0])) {
				$this->color($colors[0]);
			}
			if (!is_null($colors[1])) {
				$this->bgColor($colors[1]);
			}
		}
		return $this;
	}
	/**
	 * Тестовый вывод всех цветов
	 */
	public function show_colors(): void
	{

		// Цвета фона
		$bgColors = array_keys(self::$bgColors);
		$bglen = 0;
		foreach ($bgColors as $bgColor) {
			$bglen = max($bglen, strlen($bgColor));
		}
		// Цвета текста
		$colors = array_keys(self::$colors);
		$len = 0;
		foreach ($colors as $color) {
			$len = max($len, strlen($color));
		}
		//
		$this->enter()->reset();
		$this->write(str_pad('', $len));
		foreach ($bgColors as $bgColor) {
			$this->reset()->write('|');
			if ($bgColor == 'light_gray') {
				$this->color('black');
			}
			$this->bgColor($bgColor)->write(str_pad($bgColor, $len, ' ', STR_PAD_BOTH))->reset();
		}
		$this->enter()->writeln(str_repeat('-', $len * (count($bgColors) + 1) + count($bgColors)));
		//
		foreach ($colors as $color) {
			if ($color == 'black') {
				$this->bgColor('light_gray');
			}
			$this->reset()->color($color)->write(str_pad($color, $len, ' ', STR_PAD_LEFT));
			foreach ($bgColors as $bgColor) {
				$this->reset()->write('|');
				$this->color($color)->bgColor($bgColor)->write(str_pad($color, $len, ' ', STR_PAD_BOTH));
			}
			$this->enter();
		}
		$this->enter();
	}
	/**
	 * Магический метод для перехвата вызова всех функций и если указан цвет, то вызов соответствующей функции
	 * @param string $name Имя функции
	 * @param array $arguments Аргументы
	 * @see \Shasoft\Console\Output
	 **/
	public function __call(string $name, array $arguments)
	{
		$color = strtolower($name);
		if (substr($color, 0, 2) == 'bg') {
			$color = substr($color, 2);
			if (substr($color, 0, 1) == '_') {
				$color = substr($color, 1);
			}
			if (array_key_exists($color, self::$bgColors)) {
				return $this->bgColor($color);
			}
		} else {
			if (array_key_exists($color, self::$colors)) {
				return $this->color($color);
			}
		}
		return $this;
	}
}
