<?php

namespace MasterStudy\Lms\Pro\addons\assignments\Routing\Swagger;

use MasterStudy\Lms\Routing\Swagger\RequestInterface;
use MasterStudy\Lms\Routing\Swagger\ResponseInterface;
use MasterStudy\Lms\Routing\Swagger\Route;

final class Create extends Route implements RequestInterface, ResponseInterface {
	public function request(): array {
		return array(
			'title'         => array(
				'type'        => 'string',
				'description' => 'Assignment title',
			),
			'content'       => array(
				'type'        => 'string',
				'description' => 'Assignment content',
			),
			'attempts'      => array(
				'type'        => 'integer',
				'description' => 'Assignment attempts',
			),
			'passing_grade' => array(
				'type'        => 'integer',
				'description' => 'Assignment passing grade',
			),
		);
	}

	public function response(): array {
		return array(
			'id' => array(
				'type'        => 'integer',
				'description' => 'Assignment ID',
			),
		);
	}

	public function get_summary(): string {
		return 'Create new assignment';
	}

	public function get_description(): string {
		return 'Create new assignment';
	}
}
