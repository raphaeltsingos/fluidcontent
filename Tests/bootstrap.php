<?php
// Register composer autoloader
$autoloaderFolders = [
    trim(shell_exec('pwd')) . '/vendor/',
    __DIR__ . '/../vendor/'
];
foreach ($autoloaderFolders as $autoloaderFolder) {
    if (file_exists($autoloaderFolder . 'autoload.php')) {
        /** @var Composer\Autoload\ClassLoader $autoloader */
        $autoloader = require $autoloaderFolder . 'autoload.php';
        if (!getenv('TYPO3_PATH_ROOT')) {
            $path = realpath($autoloaderFolder . '../') . '/';
            $pwd = trim(shell_exec('pwd'));
            if (file_exists($pwd . '/composer.json')) {
                $json = json_decode(file_get_contents($pwd . '/composer.json'), true);
                if ($json['extra']['typo3/cms']['web-dir'] ?? false) {
                    $path .= $json['extra']['typo3/cms']['web-dir'] . '/';
                }
            }
            putenv('TYPO3_PATH_ROOT=' . $path);
        }
        break;
    }
}

if (!isset($autoloader)) {
    throw new \RuntimeException(
        'Could not find autoload.php, make sure you ran composer.'
    );
}

$autoloader->addPsr4('FluidTYPO3\\Fluidcontent\\Tests\\', __DIR__ . '/');

\FluidTYPO3\Development\Bootstrap::initialize(
	$autoloader,
	array(
		'fluid_template' => \FluidTYPO3\Development\Bootstrap::CACHE_PHP_NULL,
		'cache_core' => \FluidTYPO3\Development\Bootstrap::CACHE_PHP_NULL,
		'cache_rootline' => \FluidTYPO3\Development\Bootstrap::CACHE_NULL,
		'cache_runtime' => \FluidTYPO3\Development\Bootstrap::CACHE_NULL,
		'extbase_object' => \FluidTYPO3\Development\Bootstrap::CACHE_NULL,
		'extbase_datamapfactory_datamap' => \FluidTYPO3\Development\Bootstrap::CACHE_NULL,
		'extbase_typo3dbbackend_tablecolumns' => \FluidTYPO3\Development\Bootstrap::CACHE_NULL,
		'extbase_typo3dbbackend_queries' => \FluidTYPO3\Development\Bootstrap::CACHE_NULL,
		'fluidcontent' => \FluidTYPO3\Development\Bootstrap::CACHE_NULL,
		'l10n' => \FluidTYPO3\Development\Bootstrap::CACHE_NULL,
	)
);

/** @var $extbaseObjectContainer \TYPO3\CMS\Extbase\Object\Container\Container */
$extbaseObjectContainer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\Container\Container');
$extbaseObjectContainer->registerImplementation('TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface', 'FluidTYPO3\Flux\Configuration\ConfigurationManager');
