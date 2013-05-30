<?php

/**
 * Исключение API
 */
class Pro_Api_Exception extends Exception {

	/**
	 * Ошибка
	 *
	 * @var string
	 */
	private $error;

	/**
	 * Описание ошибки
	 *
	 * @var string
	 */
	private $description;

	/**
	 * Диалог
	 *
	 * @var Pro_Api_Dialogue
	 */
	private $dialogue;


	/**
	 * Конструктор
	 *
	 * @param string           $error       Ошибка
	 * @param string           $description Описание ошибки
	 * @param Pro_Api_Dialogue $dialogue    Диалог
	 */
	public function __construct($error, $description, Pro_Api_Dialogue $dialogue) {
		$this->error       = $error;
		$this->dialogue    = $dialogue;
		$this->description = $description;
		parent::__construct($error, $dialogue->getHttpCode());
	}

	/**
	 * Возвращает ошибку
	 *
	 * @return string
	 */
	public function getError() {
		return $this->error;
	}

	/**
	 * Возвращает описание ошибки
	 *
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Возвращает диалог
	 *
	 * @return string
	 */
	public function getDialogue() {
		return $this->dialogue;
	}

}