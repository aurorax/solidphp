<?php

define('_CORE_',dirname(__FILE__).'/solidphp');

require  _CORE_.'/Solid.php';

Solid::init(dirname(_CORE_).'/config.php');

#Solid::run('Index','action');
Solid::run();