<?php
/**
 * Hook and filter loader class
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Loader class
 */
class UNBSB_Loader {

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
	 * Add action
	 *
	 * @param string $hook          Hook name.
	 * @param object $component     Class instance.
	 * @param string $callback      Callback method.
	 * @param int    $priority      Priority.
	 * @param int    $accepted_args Number of accepted arguments.
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Add filter
	 *
	 * @param string $hook          Hook name.
	 * @param object $component     Class instance.
	 * @param string $callback      Callback method.
	 * @param int    $priority      Priority.
	 * @param int    $accepted_args Number of accepted arguments.
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Add hook to array
	 *
	 * @param array  $hooks         Hooks array.
	 * @param string $hook          Hook name.
	 * @param object $component     Class instance.
	 * @param string $callback      Callback method.
	 * @param int    $priority      Priority.
	 * @param int    $accepted_args Number of accepted arguments.
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
	 * Register all hooks
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
