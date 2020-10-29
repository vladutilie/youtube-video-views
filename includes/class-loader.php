<?php
/**
 * Hooks loader class.
 *
 * @class Loader
 * @package YTVV\Includes
 */

namespace YTVV\Includes;

/**
 * Class Loader
 * @package YTVV\Includes
 *
 * @since 1.0.0
 */
class Loader {

	/**
	 * Actions.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $actions = [];

	/**
	 * Filters.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $filters = [];

	/**
	 * Adds actions.
	 *
	 * Adds the actions in the property array.
	 *
	 * @param string $hook Required. Name of the hook.
	 * @param string $component Required. Name of the class which $callback belongs to.
	 * @param string $callback Required. Name of the method (function) from the $component class.
	 * @param int $priority Optional. Default 10. Priority of the $callback.
	 * @param int $acc_args Optional. Default 1. Number of the arguments of the $callback.
	 *
	 * @since 1.0.0
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $acc_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $acc_args );
	}

	/**
	 * Adds filters.
	 *
	 * Adds the filters in the property array.
	 *
	 * @param string $hook Required. Name of the hook.
	 * @param string $component Required. Name of the class which $callback belongs to.
	 * @param string $callback Required. Name of the method (function) from the $component class.
	 * @param int $priority Optional. Default 10. Priority of the $callback.
	 * @param int $acc_args Optional. Default 1. Number of the arguments of the $callback.
	 *
	 * @since 1.0.0
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $acc_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $acc_args );
	}

	/**
	 * Adds filters and actions.
	 *
	 * Adds the hooks for filters and actions in the array.
	 *
	 * @param string $hooks Required. The hooks property array.
	 * @param string $hook Required. Name of the hook.
	 * @param string $component Required. Name of the class which $callback belongs to.
	 * @param string $callback Required. Name of the method (function) from the $component class.
	 * @param int $priority Optional. Default 10. Priority of the $callback.
	 * @param int $acc_args Optional. Default 1. Number of the arguments of the $callback.
	 *
	 * @return array $hooks
	 * @since 1.0.0
	 */
	private function add( $hooks, $hook, $component, $callback, $priority, $acc_args ) {
		$hooks[] = [
			'hook'      => $hook, // tag.
			'component' => $component, // class.
			'callback'  => $callback, // method.
			'priority'  => $priority, // priority.
			'acc_args'  => $acc_args, // accepted args.
		];

		return $hooks;
	}

	/**
	 * Run the filters and the actions.
	 *
	 * @since 1.0.0
	 *
	 * @see add_filter function is relied on
	 * @link https://developer.wordpress.org/reference/functions/add_filter/
	 *
	 * @see add_action function is relied on
	 * @link https://developer.wordpress.org/reference/functions/add_action/
	 */
	public function run() {
		foreach ( $this->filters as $hook ) {
			add_filter( $hook['hook'], [
				$hook['component'],
				$hook['callback']
			], $hook['priority'], $hook['acc_args'] );
		}
		foreach ( $this->actions as $hook ) {
			add_action( $hook['hook'], [
				$hook['component'],
				$hook['callback']
			], $hook['priority'], $hook['acc_args'] );
		}
	}
}
