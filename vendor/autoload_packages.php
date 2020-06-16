<?php
/**
 * This file `autoload_packages.php`was generated by automattic/jetpack-autoloader.
 *
 * From your plugin include this file with:
 * require_once . plugin_dir_path( __FILE__ ) . '/vendor/autoload_packages.php';
 *
 * @package automattic/jetpack-autoloader
 */

// phpcs:disable PHPCompatibility.LanguageConstructs.NewLanguageConstructs.t_ns_separatorFound
// phpcs:disable PHPCompatibility.Keywords.NewKeywords.t_namespaceFound
// phpcs:disable PHPCompatibility.Keywords.NewKeywords.t_ns_cFound

namespace Automattic\Jetpack\Autoloader;

if ( ! function_exists( __NAMESPACE__ . '\enqueue_package_class' ) ) {
	global $jetpack_packages_classes;

	if ( ! is_array( $jetpack_packages_classes ) ) {
		$jetpack_packages_classes = array();
	}
	/**
	 * Adds the version of a package to the $jetpack_packages global array so that
	 * the autoloader is able to find it.
	 *
	 * @param string $class_name Name of the class that you want to autoload.
	 * @param string $version Version of the class.
	 * @param string $path Absolute path to the class so that we can load it.
	 */
	function enqueue_package_class( $class_name, $version, $path ) {
		global $jetpack_packages_classes;

		if ( ! isset( $jetpack_packages_classes[ $class_name ] ) ) {
			$jetpack_packages_classes[ $class_name ] = array(
				'version' => $version,
				'path'    => $path,
			);

			return;
		}
		// If we have a @dev version set always use that one!
		if ( 'dev-' === substr( $jetpack_packages_classes[ $class_name ]['version'], 0, 4 ) ) {
			return;
		}

		// Always favour the @dev version. Since that version is the same as bleeding edge.
		// We need to make sure that we don't do this in production!
		if ( 'dev-' === substr( $version, 0, 4 ) ) {
			$jetpack_packages_classes[ $class_name ] = array(
				'version' => $version,
				'path'    => $path,
			);

			return;
		}
		// Set the latest version!
		if ( version_compare( $jetpack_packages_classes[ $class_name ]['version'], $version, '<' ) ) {
			$jetpack_packages_classes[ $class_name ] = array(
				'version' => $version,
				'path'    => $path,
			);
		}
	}
}

if ( ! function_exists( __NAMESPACE__ . '\enqueue_package_file' ) ) {
	global $jetpack_packages_files;

	if ( ! is_array( $jetpack_packages_files ) ) {
		$jetpack_packages_files = array();
	}
	/**
	 * Adds the version of a package file to the $jetpack_packages_files global array so that
	 * we can load the most recent version after 'plugins_loaded'.
	 *
	 * @param string $file_identifier Unique id to file assigned by composer based on package name and filename.
	 * @param string $version Version of the file.
	 * @param string $path Absolute path to the file so that we can load it.
	 */
	function enqueue_package_file( $file_identifier, $version, $path ) {
		global $jetpack_packages_files;

		if ( ! isset( $jetpack_packages_files[ $file_identifier ] ) ) {
			$jetpack_packages_files[ $file_identifier ] = array(
				'version' => $version,
				'path'    => $path,
			);

			return;
		}
		// If we have a @dev version set always use that one!
		if ( 'dev-' === substr( $jetpack_packages_files[ $file_identifier ]['version'], 0, 4 ) ) {
			return;
		}

		// Always favour the @dev version. Since that version is the same as bleeding edge.
		// We need to make sure that we don't do this in production!
		if ( 'dev-' === substr( $version, 0, 4 ) ) {
			$jetpack_packages_files[ $file_identifier ] = array(
				'version' => $version,
				'path'    => $path,
			);

			return;
		}
		// Set the latest version!
		if ( version_compare( $jetpack_packages_files[ $file_identifier ]['version'], $version, '<' ) ) {
			$jetpack_packages_files[ $file_identifier ] = array(
				'version' => $version,
				'path'    => $path,
			);
		}
	}
}

if ( ! function_exists( __NAMESPACE__ . '\file_loader' ) ) {
	/**
	 * Include latest version of all enqueued files. Should be called after all plugins are loaded.
	 */
	function file_loader() {
		global $jetpack_packages_files;
		foreach ( $jetpack_packages_files as $file_identifier => $file_data ) {
			if ( empty( $GLOBALS['__composer_autoload_files'][ $file_identifier ] ) ) {
				require $file_data['path'];

				$GLOBALS['__composer_autoload_files'][ $file_identifier ] = true;
			}
		}
	}
}

if ( ! function_exists( __NAMESPACE__ . '\autoloader' ) ) {
	/**
	 * Used for autoloading jetpack packages.
	 *
	 * @param string $class_name Class Name to load.
	 */
	function autoloader( $class_name ) {
		global $jetpack_packages_classes;

		if ( isset( $jetpack_packages_classes[ $class_name ] ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				// TODO ideally we shouldn't skip any of these, see: https://github.com/Automattic/jetpack/pull/12646.
				$ignore = in_array(
					$class_name,
					array(
						'Automattic\Jetpack\Connection\Manager',
					),
					true
				);

				if ( ! $ignore && function_exists( 'did_action' ) && ! did_action( 'plugins_loaded' ) ) {
					_doing_it_wrong(
						esc_html( $class_name ),
						sprintf(
						/* translators: %s Name of a PHP Class */
							esc_html__( 'Not all plugins have loaded yet but we requested the class %s', 'jetpack' ),
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							$class_name
						),
						esc_html( $jetpack_packages_classes[ $class_name ]['version'] )
					);
				}
			}

			require_once $jetpack_packages_classes[ $class_name ]['path'];

			return true;
		}

		return false;
	}

	// Add the jetpack autoloader.
	spl_autoload_register( __NAMESPACE__ . '\autoloader' );
}
/**
 * Prepare all the classes for autoloading.
 */
function enqueue_packages_8c5cbc0c99ddfbe7a21a900eda4dc1e0() {
	$class_map = require_once dirname( __FILE__ ) . '/composer/autoload_classmap_package.php';
	foreach ( $class_map as $class_name => $class_info ) {
		enqueue_package_class( $class_name, $class_info['version'], $class_info['path'] );
	}

	$autoload_file = __DIR__ . '/composer/autoload_files_package.php';

	$includeFiles = file_exists( $autoload_file )
		? require $autoload_file
		: array();

	foreach ( $includeFiles as $fileIdentifier => $file_data ) {
		enqueue_package_file( $fileIdentifier, $file_data[ 'version' ], $file_data[ 'path' ] );
	}

	if ( function_exists( 'has_action') && function_exists( 'did_action' ) && ! did_action( 'plugins_loaded' ) && false === has_action( 'plugins_loaded', __NAMESPACE__ . '\file_loader' ) ) {
		// Add action if it has not been added and has not happened yet.
		// Priority -10 to load files as early as possible in case plugins try to use them during `plugins_loaded`.
		add_action( 'plugins_loaded', __NAMESPACE__ . '\file_loader', 0, -10 );
	} elseif( ! function_exists( 'did_action' ) || did_action( 'plugins_loaded' ) ) {
		file_loader(); // Either WordPress is not loaded or plugin is doing it wrong. Either way we'll load the files so nothing breaks.
	}
}
enqueue_packages_8c5cbc0c99ddfbe7a21a900eda4dc1e0();
