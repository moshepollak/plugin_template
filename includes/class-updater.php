<?php

/**
 * Register all actions and filters for the plugin
 *
 * @link       Webduck
 * @since      1.0.0
 *
 * @package    Replace_name
 * @subpackage Replace_name/includes
 */

/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 *
 * @package    Replace_name
 * @subpackage Replace_name/includes
 * @author     Webduck <office@webduck.co.il>
 */
class WebDuckUpdater_replace_name
{

	private $plugin_name;
	private $version;

    private $file;

	private $plugin;

	private $basename;

	private $active;

	private $username;

	private $repository;

	private $authorize_token;

	private $github_response;
   
    public function __construct( $plugin_name, $version , $git_user_name = "" ,$git_auth)
    {


        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->set_username( $git_user_name );
        $this->set_repository( $this->plugin_name );
        $this->authorize( $git_auth );
        $this->file =  plugin_dir_path(dirname(__FILE__)) . $this->plugin_name . ".php";

        add_action( 'admin_init', array( $this, 'set_plugin_properties' ) );

        add_filter('plugins_api', [$this,'plugin_info'], 999, 3);
        add_filter('site_transient_update_plugins', [$this, 'push_update']);
        add_action('upgrader_process_complete', [$this,'after_update'], 10, 2);
        add_filter('plugin_row_meta', [$this, 'plugin_row_meta' ], 10, 2);
        add_filter( 'upgrader_pre_download',
            function() {
                add_filter( 'http_request_args', [ $this, 'download_package' ], 15, 2 );
                return false; // upgrader_pre_download filter default return value.
            }
        
       );
        add_filter( 'upgrader_post_install', array( $this, 'after_install' ), 10, 3 );
    }
    public function set_plugin_properties() {
		$this->plugin	= get_plugin_data( $this->file );
		$this->basename = plugin_basename( $this->file );
		$this->active	= is_plugin_active( $this->basename );
	}
    private function get_repository_info() {
	    if ( is_null( $this->github_response ) ) { // Do we have a response?
		$args = array();
	        $request_uri = sprintf( 'https://api.github.com/repos/%s/%s/releases', $this->username, $this->repository ); // Build URI
		    
		$args = array();

	        if( $this->authorize_token ) { // Is there an access token?
		          $args['headers']['Authorization'] = "token {$this->authorize_token}"; // Set the headers
	        }

	        $response = json_decode( wp_remote_retrieve_body( wp_remote_get( $request_uri, $args ) ), true ); // Get JSON and parse it

	        if( is_array( $response ) ) { // If it is an array
	            $response = current( $response ); // Get the first item
	        }

	        return $response; // Set it to our property
	    }
	}
    public function set_username( $username ) {
		$this->username = $username;
	}

	public function set_repository( $repository ) {
		$this->repository = $repository;
	}

	public function authorize( $token ) {
		$this->authorize_token = $token;
	}
    public function plugin_row_meta($plugin_meta, $plugin_file)
    {
        if (	$this->basename === $plugin_file) {
            $plugin_slug = $this->plugin_name;
            $plugin_name = __('Noti', $this->plugin_name);
    
            $row_meta = [
                'view-details' => sprintf(
                    '<a href="%s" class="thickbox open-plugin-details-modal" aria-label="%s" data-title="%s">%s</a>',
                    esc_url(network_admin_url('plugin-install.php?tab=plugin-information&plugin=' . $plugin_slug . '&TB_iframe=true&width=600&height=550')),
                    esc_attr(sprintf(__('More information about %s', $this->plugin_name), $plugin_name)),
                    esc_attr($plugin_name),
                    __('View details', $this->plugin_name)
                )
            ];
    
            $plugin_meta = array_merge($plugin_meta, $row_meta);
        }
    
        return $plugin_meta;
    }
    public function plugin_info($res, $action, $args)
    {
        // do nothing if this is not about getting plugin information
        if ('plugin_information' !== $action) {
            return false;
        }
            
        $plugin_slug = $this->plugin_name; // we are going to use it in many places in this function
            
        // do nothing if it is not our plugin
        if ($plugin_slug !== $args->slug) {
            return false;
        }
            

            // info.json is the file with the actual plugin information on your server
            // $remote = wp_remote_get(
            //     'http://plugins.webduck.co.il/plugins/'.$this->plugin_name.'/'.$this->plugin_name.'.json',['timeout' => 40 ]       
            // );
            $remote = $this->get_repository_info();
     
            
        if (! is_wp_error($remote) ) {
            
            $plugin = array(
                'name'				=> $this->plugin["Name"],
                'slug'				=> $this->basename,
                'requires'					=> '3.3',
                'tested'						=> '4.4.1',
                'rating'						=> '100.0',
                'num_ratings'				=> '10823',
                'downloaded'				=> '14249',
                'added'							=> '2016-01-05',
                'version'			=> $remote['tag_name'],
                'author'			=> $this->plugin["AuthorName"],
                'author_profile'	=> $this->plugin["AuthorURI"],
                'last_updated'		=> $remote['published_at'],
                'homepage'			=> $this->plugin["PluginURI"],
                'short_description' => $this->plugin["Description"],
                'sections'			=> array(
                    'Description'	=> $this->plugin["Description"],
                    'Updates'		=> $remote['body'],
                ),
                'download_link'		=> $remote['zipball_url']
            );
            set_transient('misha_upgrade_' . $plugin_slug, $plugin, 43200); // 12 hours cache

            return (object) $plugin; // Return the data
  
        }
            
        return false;
    }
    public function push_update($transient)
    {
        if (empty($transient->checked)) {
            return $transient;
        }
             
        if (false == $remote = get_transient('misha_upgrade_'.$this->plugin_name)) {
                   
            $remote = $this->get_repository_info();
			set_transient('misha_upgrade_'.$this->plugin_name, $remote, 43200); // 12 hours cache

        }
             

        if ($remote && version_compare($this->version, $remote['version'], '<') ) {

                $remote['plugin'] = 	$this->basename; // it could be just YOUR_PLUGIN_SLUG.php if your plugin doesn't have its own directory
                $res = new stdClass();
                $res->slug = $this->plugin_name;
                $res->plugin = 	$this->basename; // it could be just YOUR_PLUGIN_SLUG.php if your plugin doesn't have its own directory
                $res->new_version = $remote['version'];
                $res->tested = $remote['tested'];
                $res->package = $remote['download_link'];
                $transient->response[$remote['plugin']] = (object) $res;
        }
        
        return  $transient;
    }
    public function after_update($upgrader_object, $options)
    {
        if ($options['action'] == 'update' && $options['type'] === 'plugin') {
            delete_transient('misha_upgrade_'.$this->plugin_name);
        }
    }
    public function download_package( $args, $url ) {

		if ( null !== $args['filename'] ) {
			if( $this->authorize_token ) { 
				$args = array_merge( $args, array( "headers" => array( "Authorization" => "token {$this->authorize_token}" ) ) );
			}
		}
		
		remove_filter( 'http_request_args', [ $this, 'download_package' ] );

		return $args;
    }
    public function after_install( $response, $hook_extra, $result ) {
		global $wp_filesystem; // Get global FS object

		$install_directory = plugin_dir_path( $this->file ); // Our plugin directory
		$wp_filesystem->move( $result['destination'], $install_directory ); // Move files to the plugin dir
		$result['destination'] = $install_directory; // Set the destination for the rest of the stack

		if ( $this->active ) { // If it was active
			activate_plugin( $this->basename ); // Reactivate
		}

		return $result;
	}
}