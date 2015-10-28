<?php

/*
Template Name: Two Column
*/

$context = Timber::get_context();
$post = new TimberPost();
$context['post'] = $post;
Timber::render( array( 'falcone-two-column.twig' ), $context );