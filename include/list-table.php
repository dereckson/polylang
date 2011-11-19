<?php

// Thanks to Matt Van Andel (http://www.mattvanandel.com) for most of this code !

if(!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' ); // since WP 3.1
}

class Polylang_List_Table extends WP_List_Table {
	function __construct() {             
		parent::__construct( array(
			'singular' => __('Language','polylang'),
			'plural' => __('Languages','polylang'),
			'ajax'=> false)
		);        
	}

	function column_default( $item, $column_name){
		switch($column_name){
			case 'description':
			case 'slug':
			case 'count':
				return $item[$column_name];
			default:
		}
	}

	function column_name($item){
		$edit_link = esc_url(admin_url('admin.php?page=mlang&amp;action=edit&amp;lang='.$item['term_id']));
		$delete_link = wp_nonce_url('?page=mlang&amp;action=delete&amp;noheader=true&amp;lang=' . $item['term_id'], 'delete-lang');
		$actions = array(
			'edit'   => '<a href="' . $edit_link . '">' . __('Edit','polylang') . '</a>',
			'delete' => '<a href="' . $delete_link .'">' . __('Delete','polylang') .'</a>'
		);
        
		return $item['name'].$this->row_actions($actions);
	}

	function column_cb($item){
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ esc_attr($this->_args['singular']),
			/*$2%s*/ esc_attr($item['term_id'])
		);
	}

  function get_columns(){
		$columns = array(
			'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
			'name'     => __('Full name', 'polylang'),
			'description'    => __('Locale', 'polylang'),
			'slug'  => __('Code', 'polylang'),
			'count'  => __('Posts', 'polylang')
		);
		return $columns;
	}

	function get_sortable_columns() {
		$sortable_columns = array(
			'name' => array('name',true), // sorted by name by default
			'description' => array('description',false),
			'slug' => array('slug',false),
			'count' => array('count',false)
		);
		return $sortable_columns;
	}

/*
	function get_bulk_actions() {
		return array('delete' => 'Delete');
	}  
*/ 
    
	function prepare_items($data = array()) {
		$per_page = 5; // 5 languages per page
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
        
		$this->_column_headers = array($columns, $hidden, $sortable);

		function usort_reorder($a,$b){
			$orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'name'; //If no sort, default to title
			$order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
			$result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
			return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
		}
		usort($data, 'usort_reorder');
               
		$current_page = $this->get_pagenum();
		$total_items = count($data);
		$data = array_slice($data,(($current_page-1)*$per_page),$per_page);
		$this->items = $data;

		$this->set_pagination_args( array(
			'total_items' => $total_items,                  //WE have to calculate the total number of items
			'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
			'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
		) );
	}    
}
?>
