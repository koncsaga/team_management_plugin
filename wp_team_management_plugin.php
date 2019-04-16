<?php
/*
Plugin Name: WordPress Team Management Plugin
Plugin URI:  
Description: A simple plugin to manage your team members in wordpress
Version:     1.0.0
Author:      Koncsag Alpar
Author URI:  
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

defined( 'ABSPATH' ) or die( 'No direct access' );

class wp_team_management_plugin
{
	public static $WP_TEAM_MEMBERS_POST_TYPE = "wp_team_members";
	public static $WP_TEAM_MEMBER_NONCE = "wp_team_member_nonce";
	public static $WP_TEAM_MEMBER_NONCE_FIELD = "wp_team_member_nonce_field";

	public function __construct()
	{
		$this->init();
		$this->register_hooks();
		$this->load_scripts_and_styles();
	}

	public function init()
	{
		add_action('init', array($this, 'register_team_management_content_type'));
		add_action('init', array($this, 'register_team_management_custom_taxonomy') );
		add_action('add_meta_boxes', array($this, 'add_team_member_meta_boxes'));
		add_action('save_post', array($this, 'save_team_member'));
		add_filter( 'enter_title_here', array($this, 'wpb_change_title_text') );
		add_filter('archive_template', array($this, 'get_custom_archive_template'));
		add_image_size( 'team-member', 318, 180, true );
	}
	public function register_hooks()
	{
		register_activation_hook(__FILE__, array($this, 'plugin_activate'));
		register_deactivation_hook(__FILE__, array($this, 'plugin_deactivate'));
	}
	public function register_team_management_content_type(){

		$labels = array(
			'name' => 'Team manager',
			'singular_name' => 'Team',
			'name_admin_bar' => 'Team',
			'add_new' => 'Add new',
			'add_new_item' => 'Add new team',
			'new_item' => 'New team',
			'edit_item' => 'Edit team',
			'all_items' => 'Members',
			'search_items' => 'Search team',
			'not_found' => 'No team found',
			'not_found_in_trash' => 'No team found in trash',
			'featured_image'        => 'Member photo',
			'set_featured_image'    => 'Set photo',
			'remove_featured_image' => 'Remove photo',
			'use_featured_image'    => 'Use as member photo',
			);

		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable'=> true,
			'show_ui'           => true,
			'show_in_nav'       => true,
			'query_var'         => true,
			'hierarchical'      => false,
				'supports'          => array('title', 'editor', 'thumbnail'),
			'has_archive'       => true,
			'menu_position'     => 2,
			'show_in_admin_bar' => true,
			'menu_icon'         => 'dashicons-admin-users',
			'rewrite'            => array('slug' => 'team-members', 'with_front' => 'true')
			);

		register_post_type(self::$WP_TEAM_MEMBERS_POST_TYPE, $args);
	}
	public function register_team_management_custom_taxonomy() {
		
		$args = [
			'labels'            => array('name' =>  'Department'),
			'hierarchical'      => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
		];

		register_taxonomy( 'department', self::$WP_TEAM_MEMBERS_POST_TYPE , $args );
	}
	public function add_team_member_meta_boxes(){
		add_meta_box(
			'wp_team_member_meta_box',
			'Team Information',
			array($this, 'team_member_meta_box_display'),
			self::$WP_TEAM_MEMBERS_POST_TYPE,
			'normal',
			'default'
		);
	}
	public function team_member_meta_box_display($post){

		wp_nonce_field(self::$WP_TEAM_MEMBER_NONCE, self::$WP_TEAM_MEMBER_NONCE_FIELD);

		$wp_position = get_post_meta($post->ID, 'wp_position', true);
		$wp_twitter_url = get_post_meta($post->ID, 'wp_twitter_url', true);
		$wp_facebook_url = get_post_meta($post->ID, 'wp_facebook_url', true);
		
		do_action('wp_team_members_admin_form_start'); 
		
		$html = '
			<div class="container-fluid">
				<div class="row">
					<div class="col-lg-12">
						<div class="form-group">
							<label class="control-label">Position</label>
							<input type="text" name="wp_position" id="wp_position" value="'.$wp_position.'" class="form-control"/>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<div class="form-group">
							<label class="control-label">Twitter url</label>
							<input type="text" name="wp_twitter_url" id="wp_twitter_url" value="'.$wp_twitter_url.'" class="form-control"/>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
					<div class="form-group">
						<label class="control-label">Facebook url</label>
						<input type="text" name="wp_facebook_url" id="wp_facebook_url" value="'.$wp_facebook_url.'" class="form-control"/>
					</div>
					</div>
				</div>
				
			</div>
		
		';
		
		echo $html;
		
		do_action('wp_team_members_admin_form_end'); 
		
	}
	public function add_team_member_to_content($content){
		global $post, $post_type;
		
		if($post_type == self::$WP_TEAM_MEMBERS_POST_TYPE && is_singular(self::$WP_TEAM_MEMBERS_POST_TYPE)){
			$wp_team_member_id = $post->ID;
			$wp_team_member_position = get_post_meta($post->ID, 'wp_position', true);
			$wp_team_member_twitter_url = get_post_meta($post->ID, 'wp_twitter_url', true);
			$wp_team_member_facebook_url = get_post_meta($post->ID, 'wp_facebook_url', true);
			
			$html = '';
			$html .= '<section class="meta-data">';
			
			do_action('wp_team_member_meta_data_output_start', $wp_team_member_id);
			
			$html .= '<p>';

			if(!empty($wp_team_member_position)){
				$html .= '<b>Position</b> ' . $wp_team_member_position . '</br>';
			}
			
			if(!empty($wp_team_member_twitter_url)){
				$html .= '<b>Twitter url</b> ' . $wp_team_member_twitter_url . '</br>';
			}
			
			if(!empty($wp_team_member_facebook_url)){
				$html .= '<b>Facebook url</b> ' . $wp_team_member_facebook_url . '</br>';
			}
			
			do_action('wp_team_member_meta_data_output_end', $wp_team_member_id);
			
			$html .= '</section>';
			$html .= $content;
			
			return $html;
		}else{
			return $content;
		}
	}
	public function get_team_member_output($arguments = ''){
		$default_args = array(
			'team_member_id'   => '',
			'number_of_members'   => -1
		);
		
		if(!empty($arguments) && is_array($arguments)){
			
			foreach($arguments as $arg_key => $arg_val){
				
				if(array_key_exists($arg_key, $default_args)){
					$default_args[$arg_key] = $arg_val;
				}
			}
		}
		
		$team_member_args = array(
			'post_type'     => self::$WP_TEAM_MEMBERS_POST_TYPE,
			'posts_per_page'=> $default_args['number_of_members'],
			'post_status'   => 'publish'
		);
		
		if(!empty($default_args['team_member_id'])){
			$team_member_args['include'] = $default_args['team_member_id'];
		}
		
		$html = '';
		
		$team_members = get_posts($team_member_args);
		
		if($team_members){
			$html .= '<article class="team_member_list cf">';
			
			foreach($team_members as $member){
				$html .= '<section class="location">';

				$wp_team_member_id = $member->ID;
				$wp_member_title = get_the_title($wp_team_member_id);
				$wp_member_thumbnail = get_the_post_thumbnail($wp_team_member_id,'thumbnail');

				$wp_member_content = apply_filters('the_content', $member->post_content);

				if(!empty($wp_member_content)){
					$wp_member_content = strip_shortcodes(wp_trim_words($wp_member_content, 40, '...'));
				}

				$wp_member_permalink = get_permalink($wp_team_member_id);
				$wp_member_position = get_post_meta($wp_team_member_id,'wp_position',true);
				$wp_member_twitter_url = get_post_meta($wp_team_member_id,'wp_twitter_url',true);
				$wp_member_facebook_url = get_post_meta($wp_team_member_id,'wp_facebook_url',true);

				$html = apply_filters('wp_team_member_before_main_content', $html);

				$html .= '<h2 class="title">';
				$html .= '<a href="' . $wp_member_permalink . '" title="view location">';
				$html .= $wp_member_title;
				$html .= '</a>';
				$html .= '</h2>';

			if(!empty($wp_member_thumbnail) || !empty($wp_member_content)){

			    $html .= '<p class="image_content">';
				
			    if(!empty($wp_member_thumbnail)){
					$html .= $wp_member_thumbnail;
			    }
			    if(!empty($wp_member_content)){
					$html .=  $wp_member_content;
			    }

			    $html .= '</p>';
			}
				
			$html .= '<p class="member-data">';

			    if(!empty($wp_member_position)){
					$html .= '<b>Twitter url: </b>' . $wp_member_twitter_url . '</br>';
			    }

				if(!empty($wp_member_twitter_url)){
					$html .= '<b>Twitter url: </b>' . $wp_member_twitter_url . '</br>';
			    }

				if(!empty($wp_member_facebook_url)){
					$html .= '<b>Facebook url: </b>' . $wp_member_facebook_url . '</br>';
			    }

				$html .= '</p>';

				$html = apply_filters('wp_team_member_after_main_content', $html);

				$html .= '<a class="link" href="' . $wp_member_permalink . '" title="view member">View Member</a>';

				$html .= '</section>';
			
			}
			
			$html .= '</article>';
			$html .= '<div class="cf"></div>';
			
		}
		
		return $html;
	}
	public function save_team_member($post_id){

        if(!isset($_POST[self::$WP_TEAM_MEMBER_NONCE_FIELD])){
            return $post_id;
        }

        if(!wp_verify_nonce($_POST[self::$WP_TEAM_MEMBER_NONCE_FIELD], self::$WP_TEAM_MEMBER_NONCE)){
            return $post_id;
        }

        if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){
            return $post_id;
        }

        $wp_position = isset($_POST['wp_position']) ? sanitize_text_field($_POST['wp_position']) : '';
        $wp_twitter_url = isset($_POST['wp_twitter_url']) ? sanitize_text_field($_POST['wp_twitter_url']) : '';
        $wp_facebook_url = isset($_POST['wp_facebook_url']) ? sanitize_text_field($_POST['wp_facebook_url']) : '';

        update_post_meta($post_id, 'wp_position', $wp_position);
        update_post_meta($post_id, 'wp_twitter_url', $wp_twitter_url);
        update_post_meta($post_id, 'wp_facebook_url', $wp_facebook_url);

        do_action('wp_team_member_admin_save',$post_id, $_POST);
    }
	public function wpb_change_title_text($title)
	{
		$screen = get_current_screen();

		if  ( self::$WP_TEAM_MEMBERS_POST_TYPE == $screen->post_type ) {
			$title = 'Person name';
		}

		return $title;
	}
	public function get_custom_archive_template($template) {

		if (is_post_type_archive(self::$WP_TEAM_MEMBERS_POST_TYPE)) {
			$template = dirname( __FILE__ ) . '/templates/archive-template.php';
		}

		return $template;
	}
	public function load_scripts_and_styles(){

		wp_register_script('cpt_jquery', '//code.jquery.com/jquery-3.2.1.slim.min.js');
		wp_register_script('cpt_bootstrap_popper_js', '//cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js');
		wp_register_script('cpt_bootstrap_js', '//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js');
		wp_register_style('cpt_bootstrap_css', '//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css');
		wp_register_style('cpt_fontawesome', '//use.fontawesome.com/releases/v5.8.1/css/all.css');

		if(!is_admin()){
		    wp_enqueue_script('cpt_jquery');
		}

		wp_enqueue_script('cpt_bootstrap_popper_js');
		wp_enqueue_script('cpt_bootstrap_js');
		wp_enqueue_style('cpt_bootstrap_css');
		wp_enqueue_style('cpt_fontawesome');

		add_action('admin_enqueue_scripts', array($this,'enqueue_admin_scripts_and_styles'));
		add_action('wp_enqueue_scripts', array($this,'enqueue_public_scripts_and_styles'));
	}
	public function enqueue_admin_scripts_and_styles(){

        if(is_admin()){
            wp_enqueue_style('wp_team_management_admin_styles', plugin_dir_url(__FILE__) . '/css/wp_team_management_admin_styles.css');
            wp_enqueue_script('wp_team_management_admin_scripts', plugin_dir_url(__FILE__) . '/js/wp_team_management_admin_scripts.js');
        }
	}
	public function enqueue_public_scripts_and_styles(){
		wp_enqueue_style('wp_team_management_public_styles', plugin_dir_url(__FILE__). '/css/wp_team_management_public_styles.css');
		wp_enqueue_script('wp_team_management_public_scripts', plugin_dir_url(__FILE__). '/js/wp_team_management_public_scripts.js');
	}
	public function plugin_activate(){
		$this->register_team_management_content_type();
		flush_rewrite_rules();
	}
	public function plugin_deactivate(){
		flush_rewrite_rules();
	}
}

new wp_team_management_plugin();
?>
