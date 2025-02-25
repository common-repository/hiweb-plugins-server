<?php
	
	
	/**
	 * Класс для работы хуков WP
	 * Class hw_plugins_server_hooks
	 */
	class hw_plugins_server_hooks{
		
		public function ajax_host_toggle_status(){
			hiweb_plugins_server()->host()->toggle_status();
			include HW_PLUGINS_SERVER_DIR_TEMPLATES . '/options-page.php';
			wp_die();
		}
		
		
		public function ajax_host_toggle_kickback_status(){
			hiweb_plugins_server()->host()->toggle_kickback_status();
			include HW_PLUGINS_SERVER_DIR_TEMPLATES . '/options-page.php';
			wp_die();
		}
		
		
		public function ajax_host_plugin_action(){
			$do = $_POST['do'];
			$slug = $_POST['plugin'];
			$R = false;
			switch( $do ){
				case 'host':
					$R = hiweb_plugins_server()->host()->plugin( $slug )->do_host();
					break;
				case 'remove':
					$R = hiweb_plugins_server()->host()->plugin( $slug )->remove();
					break;
				case 'unhost':
					$R = hiweb_plugins_server()->host()->plugin( $slug )->do_unhost( false );
					break;
				case 'update':
					$R = hiweb_plugins_server()->host()->plugin( $slug )->do_update();
					break;
				case 'install':
					$R = hiweb_plugins_server()->host()->plugin( $slug )->install();
					break;
				default:
					$R = false;
					break;
			}
			ob_start();
			_hw_plugins_server_page();
			$html = ob_get_clean();
			echo json_encode( array( 'result' => $R, 'html' => $html ) );
			wp_die();
		}
		
		
		public function ajax_remote_plugin_action(){
			ob_start();
			$do = $_POST['do'];
			$slug = $_POST['plugin'];
			$R = false;
			if( $do == 'download' ){
				$R = hiweb_plugins_server()->remote()->plugin( $slug )->download();
			}
			if( $do == 'activate' ){
				if( !hiweb_plugins_server()->local()->plugin( $slug )->is_exists() )
					hiweb_plugins_server()->remote()->plugin( $slug )->download();
				$R = hiweb_plugins_server()->local()->plugin( $slug )->activate();
			}
			if( $do == 'deactivate' ){
				$R = hiweb_plugins_server()->local()->plugin( $slug )->deactivate();
			}
			if( $do == 'remove' ){
				$R = hiweb_plugins_server()->local()->plugin( $slug )->remove();
			}
			_hw_plugins_server_remote_page();
			$html = ob_get_clean();
			echo json_encode( array( 'result' => $R, 'html' => $html ) );
			wp_die();
		}
		
		
		public function ajax_remote_url_update(){
			$bool = update_option( HW_PLUGINS_SERVER_OPTIONS_REMOTE_URL, $_POST['url'] );
			if( $bool == false ){
				$R = array( 'result' => false, 'message' => 'Не удалось внедрить значение [' . $_POST['url'] . '] ключа [' . HW_PLUGINS_SERVER_OPTIONS_REMOTE_URL . '] в опции...' );
			}else{
				$R = array( 'result' => true, 'message' => hiweb_plugins_server()->remote()->status( $_POST['url'] ) );
			}
			echo json_encode( $R );
			wp_die();
		}
		
		
		public function ajax_server_get(){
			$R = array(
				'status' => hiweb_plugins_server()->host()->status(), 'url_root' => HW_PLUGINS_SERVER_ROOT_URL, 'plugins' => array()
			);
			if( $R['status'] ){
				$R['plugins'] = array();
				foreach( hiweb_plugins_server()->host()->plugins( true ) as $slug => $plugin ){
					$R['plugins'][ $slug ] = $plugin->data();
					$R['plugins'][ $slug ]['url'] = $plugin->url();
					$R['plugins'][ $slug ]['url_info'] = $plugin->url( true );
					$R['plugins'][ $slug ]['file_name'] = $plugin->file_name();
					$R['plugins'][ $slug ]['filemtime'] = filemtime( $plugin->path() );
				}
			}
			echo json_encode( $R );
			wp_die();
		}
		
		
		public function plugin_action_links( $links, $plugin ){
			if( hiweb_plugins_server()->remote()->status() == true ){
				if( $plugin != 'hiweb-plugins-server/hiweb-plugins-server.php' ){
					//$links[] = '<a href=""><i class="dashicons dashicons-upload"></i> Upload To Server</a>';
				}
			}
			return $links;
		}
		
		
		public function plugin_action_links_settings( $links ){
			$links[] = '<a href="' . esc_url( get_admin_url( null, 'options-general.php?page=' . HW_PLUGINS_SERVER_OPTIONS_PAGE_SLUG ) ) . '">Client / Server Settings</a>';
			return $links;
		}
		
		
		public function admin_notices(){
			if( get_current_screen()->base == 'plugins' ){
				ob_start();
			}
		}
		
		
		public function pre_current_active_plugins(){
			if( get_current_screen()->base == 'plugins' ){
				$html = ob_get_clean();
				$button = '<a href="' . self_admin_url( 'plugins.php?page=hiweb-plugins-server-remote' ) . '" title="Add New Plugin from hiWeb Remote Server" class="page-title-action">Add Remote Plugins</a>';
				echo str_replace( '</h1>', $button . '</h1>', $html );
			}
		}
		
	}