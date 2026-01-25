<?php
/**
 * Hook ve filter loader sınıfı
 *
 * @package Appointment_General
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Loader sınıfı
 */
class AG_Loader {

	/**
	 * Actions array
	 *
	 * @var array
	 */
	protected $actions;

	/**
	 * Filters array
	 *
	 * @var array
	 */
	protected $filters;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->actions = array();
		$this->filters = array();
	}

	/**
	 * Action ekle
	 *
	 * @param string $hook          Hook adı.
	 * @param object $component     Sınıf instance.
	 * @param string $callback      Callback method.
	 * @param int    $priority      Öncelik.
	 * @param int    $accepted_args Kabul edilen argüman sayısı.
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Filter ekle
	 *
	 * @param string $hook          Hook adı.
	 * @param object $component     Sınıf instance.
	 * @param string $callback      Callback method.
	 * @param int    $priority      Öncelik.
	 * @param int    $accepted_args Kabul edilen argüman sayısı.
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Hook'u diziye ekle
	 *
	 * @param array  $hooks         Hooks array.
	 * @param string $hook          Hook adı.
	 * @param object $component     Sınıf instance.
	 * @param string $callback      Callback method.
	 * @param int    $priority      Öncelik.
	 * @param int    $accepted_args Kabul edilen argüman sayısı.
	 *
	 * @return array
	 */
	private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {
		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		);

		return $hooks;
	}

	/**
	 * Tüm hook'ları kaydet
	 */
	public function run() {
		foreach ( $this->filters as $hook ) {
			add_filter(
				$hook['hook'],
				array( $hook['component'], $hook['callback'] ),
				$hook['priority'],
				$hook['accepted_args']
			);
		}

		foreach ( $this->actions as $hook ) {
			add_action(
				$hook['hook'],
				array( $hook['component'], $hook['callback'] ),
				$hook['priority'],
				$hook['accepted_args']
			);
		}
	}
}
