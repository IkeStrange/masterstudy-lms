<?php

/** @var \MasterStudy\Lms\Routing\Router $router */

$router->post(
	'/google-meets',
	\MasterStudy\Lms\Pro\AddonsPlus\GoogleMeet\Http\Controllers\CreateController::class,
	\MasterStudy\Lms\Pro\AddonsPlus\GoogleMeet\Routing\Swagger\Create::class,
);

$router->get(
	'/google-meets/{meeting_id}',
	\MasterStudy\Lms\Pro\AddonsPlus\GoogleMeet\Http\Controllers\GetController::class,
	\MasterStudy\Lms\Pro\AddonsPlus\GoogleMeet\Routing\Swagger\Get::class,
);

$router->put(
	'/google-meets/{meeting_id}',
	\MasterStudy\Lms\Pro\AddonsPlus\GoogleMeet\Http\Controllers\UpdateController::class,
	\MasterStudy\Lms\Pro\AddonsPlus\GoogleMeet\Routing\Swagger\Update::class,
);

$router->delete(
	'/google-meets/{meeting_id}',
	\MasterStudy\Lms\Pro\AddonsPlus\GoogleMeet\Http\Controllers\DeleteController::class,
	\MasterStudy\Lms\Pro\AddonsPlus\GoogleMeet\Routing\Swagger\Delete::class,
);
