<?php

defined('_JEXEC') or die;

require_once __DIR__ . '/helper.php';

$articles = ModArticlesRandomHelper::getArticles();

require JModuleHelper::getLayoutPath( 'mod_articles_random' );
