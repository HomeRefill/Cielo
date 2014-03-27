<?php
define('OUTPUT', __DIR__ . DIRECTORY_SEPARATOR . 'output' . DIRECTORY_SEPARATOR);
/**
 * @author Artur
 */
$load = require_once(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');
$load->add('Tritoq', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src');