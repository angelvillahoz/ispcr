<?php
/**
 * This file contains the routes for the application and uses the Slim Framework
 * router library which version is 3.X
 */
$c = $app->getContainer();
// -----------------------------------------------------------------------------
// datasource
// -----------------------------------------------------------------------------
$app->post("/search", function ($request, $response, $args) {
    $query = CCR\BLAT\Datasource\Query\GetAlignmentList::fromRequest($request);
    $results = $this->get("dispatcher")->request($query);
    return $response->withJson($results);
})->add($container->get("debug-middleware"));
