<?php
class Order_items_model extends CI_Model {

	function getAll()
	{  
		$query = $this->db->get('order_items');
		return $query->result('Order_items');
	}  
	
	function get($id)
	{
		$query = $this->db->get_where('order_items',array('id' => $id));
		return $query->row(0,'Order_items');
	}
	
	function delete($id) {
		return $this->db->delete("order_items",array('id' => $id ));
	}
	
	function insert($order_item) {
		return $this->db->insert("order_items", array('order_id' => $order_item->order_id,
				                                  'product_id' => $order_item->product_id,
											      'quantity' => $order_item->quantity));
	}
}
?>