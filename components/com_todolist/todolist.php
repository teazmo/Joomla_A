<?php
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

JLoader::registerPrefix('Todolist', JPATH_COMPONENT);
JLoader::register('TodolistController', JPATH_COMPONENT . '/controller.php');

$controller = JControllerLegacy::getInstance('Todolist');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();