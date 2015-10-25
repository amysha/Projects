<?php
/*Very IMPORTATNT!!*/
session_start();
class Store extends CI_Controller {  
     
    function __construct() {
    		// Call the Controller constructor
	    	parent::__construct();
	    	
	    	$config['upload_path'] = './images/product/';
	    	$config['allowed_types'] = 'gif|jpg|png';
/*	    	$config['max_size'] = '100';
	    	$config['max_width'] = '1024';
	    	$config['max_height'] = '768';
*/
	    	$this->load->library('upload', $config);
    }

    /*
     * Main page after use logged in 
     */
    function index() {
    	// Check is user logged in
    	if (!isset($_SESSION['userLogin']) || $_SESSION['userLogin'] == "")
    	{
    		redirect('store/login', 'refresh');
    		return;
    	}
    	// if admin, redirect to admin page
    	else if ($_SESSION['userLogin'] == 'admin')
    	{
    		// if logged in as admin
    		$this->load->model('product_model');
    		$products = $this->product_model->getAll();
    		$data['products']=$products;
    		$this->load->view('user/adminHome.php',$data);
    	}
    	else { 
    		// if logged in as customer, reirect to user page
    		$this->load->model('product_model');
    		$products = $this->product_model->getAll();
    		$data['products']=$products;
    		$this->load->view('user/customerHome.php',$data);
    	}
    }
    
    /*
     * For admin use: direct to creating new product page
     */
    function newForm() {
	    $this->load->view('product/newForm.php');
    }
    
    /*
     * For admin use: create a new product
     */
	function create() {
		$this->load->library('form_validation');
		$this->form_validation->set_rules('name','Name','required|is_unique[products.name]');
		$this->form_validation->set_rules('description','Description','required');
		$this->form_validation->set_rules('price','Price','required');
		
		$fileUploadSuccess = $this->upload->do_upload();
		
		if ($this->form_validation->run() == true && $fileUploadSuccess) {
			$this->load->model('product_model');

			$product = new Product();
			$product->name = $this->input->get_post('name');
			$product->description = $this->input->get_post('description');
			$product->price = $this->input->get_post('price');
			
			$data = $this->upload->data();
			$product->photo_url = $data['file_name'];
			
			$this->product_model->insert($product);

			//Then we redirect to the index page again
			redirect('store/index', 'refresh');
		}
		else {
			if ( !$fileUploadSuccess) {
				$data['fileerror'] = $this->upload->display_errors();
				$this->load->view('product/newForm.php',$data);
				return;
			}
			
			$this->load->view('product/newForm.php');
		}	
	}
	
	/*
	 * Reviewing a specific product detail 
	 */
	function read($id) {
		$this->load->model('product_model');
		$product = $this->product_model->get($id);
		$data['product']=$product;
		$this->load->view('product/read.php',$data);
	}
	
	/*
	 * For admin use: edit product information
	 */
	function editForm($id) {
		$this->load->model('product_model');
		$product = $this->product_model->get($id);
		$data['product']=$product;
		$this->load->view('product/editForm.php',$data);
	}
	
	/*
	 * For admin use: Update a product $id information
	 */
	function update($id) {
		$this->load->library('form_validation');
		$this->form_validation->set_rules('name','Name','required');
		$this->form_validation->set_rules('description','Description','required');
		$this->form_validation->set_rules('price','Price','required');
		
		if ($this->form_validation->run() == true) {
			$product = new Product();
			$product->id = $id;
			$product->name = $this->input->get_post('name');
			$product->description = $this->input->get_post('description');
			$product->price = $this->input->get_post('price');
			
			$this->load->model('product_model');
			$this->product_model->update($product);
			//Then we redirect to the index page again
			redirect('store/index', 'refresh');
		}
		else {
			$product = new Product();
			$product->id = $id;
			$product->name = set_value('name');
			$product->description = set_value('description');
			$product->price = set_value('price');
			$data['product']=$product;
			$this->load->view('product/editForm.php',$data);
		}
	}
    
	/*
	 * For admin use: delete a product by $id
	 */
	function delete($id) {
		$this->load->model('product_model');
		
		if (isset($id)) 
			$this->product_model->delete($id);
		
		//Then we redirect to the index page again
		redirect('store/index', 'refresh');
	}
    
	/*
	 * Main page: login
	 */
	function login() {		
		$this->load->library('form_validation');
		$this->form_validation->set_rules('username','Username','required');
		$this->form_validation->set_rules('password','Password','required');
	
		if ($this->form_validation->run() == true) {
	
			$user = new Customer();
			$user->login = $this->input->get_post('username');
			$user->password = $this->input->get_post('password');
	
			// check database for valid login info
			$this->load->model('customer_model');
			$customer = $this->customer_model->get($user->login, $user->password);
	
			// if user and pass are admin, log in as admin
			if ($user->login == 'admin' && $user->password == 'admin') {
				$_SESSION['userLogin'] = "admin";
				redirect('store/index', 'refresh');
			}
			// if valid credentials, log in as customer
			else if ($customer != NULL) {
				$_SESSION['userLogin'] = $customer->id;
				redirect('store/index', 'refresh');
			}
			// invalid credentials, go back to login
			else {
				$data['errorMsg'] = "Invalid username or password";
				$this->load->view('login.php', $data);
			}
		}
		else {
			$data['errorMsg'] = "";
			$this->load->view('login.php', $data);
	
		}
	}
	
	/*
	 * When log out, reset sessions and direct to index page
	 */
	function logout(){
		session_unset();
		session_destroy();
		redirect('store/index', 'refresh');
	}
	
	/*
	 * For user signup.
	 * Username: has to be unique and not used by other users
	 * Password: at least 6 length
	 * Email: 1) Unique, not used by other users; 
	 *        2) system will use the registered email address to email receipt
	 */
	function signup(){
		$this->load->library('form_validation');
 		$this->form_validation->set_rules('first','First Name','required');
 		$this->form_validation->set_rules('last','Last Name','required');
 		$this->form_validation->set_rules('username','Username','required|is_unique[customers.login]');
 		$this->form_validation->set_rules('password','Password','required|min_length[6]');
 		$this->form_validation->set_rules('passconf','Password Confirmation','required|matches[password]');
 		$this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[customers.email]');
		
		if ($this->form_validation->run() == true) {
			$this->load->model('customer_model');
			$customer = new Customer();
			$customer->first = $this->input->get_post('first');
			$customer->last = $this->input->get_post('last');
			$customer->login = $this->input->get_post('username');
			$customer->password = $this->input->get_post('password');
			$customer->email = $this->input->get_post('email');
			$this->customer_model->insert($customer);
			//Then we redirect to the index page again to login use newly registered username
			redirect('store/logout', 'refresh');
		}
		else {
			$this->load->view('signup.php');
		}
	}
	
	/*
	 * 1) if cart does not exist
	 * 			create a cart
	 * 2) if item already exists
	 * 		   increment the quantity
	 *    else 
	 * 		   add to cart
	 * */
	function addToCart($id){
		if (!isset($_SESSION['userLogin']) || $_SESSION['userLogin'] == "")
		{
			redirect('store/login', 'refresh');
			return;
		}
		//If there is no cart, create a new cart
		if (!isset($_SESSION['cart']) || !$_SESSION['cart']) {
			$_SESSION['cart'] = serialize(array());
		}
		$item_exist = false;
		$order_items = unserialize($_SESSION['cart']);
		//check if the item exists in the cart
		foreach ($order_items as $item) {
			if ($item->product_id == $id) {
				$item->quantity++;
				$item_exist = true;
				break;
			}
		}
		//if does not exist, add to the cart
		if (!$item_exist) {
			$item = new Order_items();
			$item->product_id = $id;
			$item->quantity = 1;	
			array_push($order_items, $item);
		}		
		// update the cart
		$_SESSION['cart'] = serialize($order_items);
		// re-direct to the shopping cart view
		redirect('store/showShoppingCart', 'refresh');
	}
	
	/*
	 * For user use: view products in shopping cart
	 */
	function showShoppingCart(){
		if (!isset($_SESSION['userLogin']) || $_SESSION['userLogin'] == "")
		{
			redirect('store/login', 'refresh');
			return;
		}
		$total = 0;
		$order_items = array();
		$product_list = array();
		
		if (isset($_SESSION['cart'])) {
			$order_items = unserialize($_SESSION['cart']);
			$this->load->model('product_model');
			foreach ($order_items as $item) {
				$product = $this->product_model->get($item->product_id);
				array_push($product_list, $product);
				// calculate total amount
				$total += $item->quantity * $product->price;
			}
		}
		$data['order_items'] = $order_items;
		$data['product_list'] = $product_list;
		$data['total'] = $total;
		$_SESSION['total'] = $total; //store total in seesion for future reference
		$this->load->view('checkout/cartView.php',$data);
	}
	
	// User has the choice to change quantity of products
	function updateQuantity($i) {
		if (!isset($_SESSION['userLogin']) || $_SESSION['userLogin'] == "")
		{
			redirect('store/login', 'refresh');
			return;
		}
		
		$input = $this->input->get_post('quantity');
		//check whether the quantity input is positive integer
		if (preg_match('/^[1-9]+[0-9]*$/', $input)) { 
			$order_items = unserialize($_SESSION['cart']);
			$order_items[$i]->quantity = $input;
			//Update the cart
			$_SESSION['cart'] = serialize($order_items);
			
		}
		//if the input quantity is not positive integer, rollback to the quantity before update
		redirect('store/showShoppingCart', 'refresh');
	}
	
	function removeFromCart($i) {
		if (!isset($_SESSION['userLogin']) || $_SESSION['userLogin'] == "")
		{
			redirect('store/login', 'refresh');
			return;
		}
		$order_items = unserialize($_SESSION['cart']);
		unset($order_items[$i]);
		$order_items = array_values($order_items);
		$_SESSION['cart'] = serialize($order_items);
		redirect("store/showShoppingCart", 'refresh');
	}
	
	function collectPayment(){
		date_default_timezone_set("America/New_York");
		
		if (!isset($_SESSION['userLogin']) || $_SESSION['userLogin'] == "") 
		{
			redirect('store/index','refresh');
			return;
		}

		// if cart is empty
		if (!isset($_SESSION['cart']) || !$_SESSION['cart']) {
			redirect('store/showShoppingCart','refresh');
			return;
		}
		$data['errorMsg'] = "";
		$data['total'] = $_SESSION['total'];

		$this->load->library('form_validation');
		$this->form_validation->set_rules('creditcard_number','Credit Card Number','required|numeric|min_length[16]|max_length[16]');
		$this->form_validation->set_rules('creditcard_month','Expiry Month (MM)','required|numeric|less_than[13]|greater_than[0]');
		$this->form_validation->set_rules('creditcard_year','Expiry Year (YY)','required|numeric|greater_than[0]');

		if ($this->form_validation->run() == true) {
			$this->load->model('order_model');
			$this->load->model('order_items_model');
			$this->load->model('customer_model');
			$this->load->model('product_model');

			// create the order
			$order = new Orders();
			$order->customer_id = $_SESSION['userLogin'];
			$order->order_date = date("Y-m-d", time());
			$order->order_time = date("G:i:s", time());
			$order->total = $_SESSION['total'];
			$order->creditcard_number = $this->input->get_post('creditcard_number'); 
			$order->creditcard_month = $this->input->get_post('creditcard_month');
			$order->creditcard_year = $this->input->get_post('creditcard_year');

			// if card is expired, raise error and return
			if (($order->creditcard_year == date("y", time()) && $order->creditcard_month < date("m", time())) 
				|| $order->creditcard_year < date("y", time())) {
				$data['errorMsg'] = "Your credit card is expired! Plese enter a new card.";
				$this->load->view('checkout/paymentForm',$data);
				return;
			}

			//get customer info to check if there is an email address, just in case
			$customer = $this->customer_model->getByID($order->customer_id);
			if (!$customer->email) {
				$data['errorMsg'] = "Your account doesn't have an email address!";
				$this->load->view('checkout/paymentForm',$data);
				return;
			}

			// insert order to database
			$order_id = $this->order_model->insert($order);

			// create arrays for reciept information
			$product_name = array();
			$quantity = array();
			$price = array();

			// add order_items to database
			$order_items = unserialize($_SESSION['cart']);
			foreach ($order_items as $item) {
				// set order_id, and add to database
				$item->order_id = $order_id;
				$this->order_items_model->insert($item);
				// get associated product
				$product = $this->product_model->get($item->product_id);
				// add data to arrays for reciepts
				array_push($product_name, $product->name);
				array_push($quantity, $item->quantity);
				array_push($price, $product->price);
			}

			// empty/clear the cart 
			unset($_SESSION['cart']);

			$data['product_name'] = $product_name;
			$data['quantity'] = $quantity;
			$data['price'] = $price;
			$data['order_id'] = $order_id;
			$data['total'] = $order->total;
			
			// send mail
			$config['mailtype'] = 'html';	
// 		    DO NOT including SMTP server info as requested in assignment description		
// 			$config['smtp_host'] = 'smtp.gmail.com';
// 			$config['smtp_user'] = 'baseballcardkingdom@gmail.com';
// 			$config['smtp_pass'] = 'baseballcardkingdom123';
// 			$config['smtp_port'] = '465';

			$this->email->initialize($config);
			$this->email->from('baseballcardkingdom@gmail.com', 'Baseball Card Kingdom');
			$this->email->to($customer->email); 
			$this->email->subject('Your Recent Purchage Reciept @ Baseball Card Kingdom');
			$message = $this->load->view('checkout/email.php', $data, TRUE);
			$this->email->message($message);
			$this->email->send();

			$this->load->view('checkout/receipt.php', $data);
		}
		else {
			$this->load->view('checkout/paymentForm.php',$data);
		}	
	}
	
	function viewFinalOrders(){
		if (!isset($_SESSION['userLogin']) || $_SESSION['userLogin'] != "admin")
		{
			redirect('store/index', 'refresh');
			return;
		}
		// get all orders and pass to view
		$this->load->model('order_model');
		$orders = $this->order_model->getAll();
		$data['orders']=$orders;
		$this->load->view('checkout/viewFinalOrders.php', $data);
	}
	
	/*
	 * Allow admin to delete all order and user informatiom
	 */
	function deleteAll(){
		// if it's not admin, direct to index page
		if (!isset($_SESSION['userLogin']) || $_SESSION['userLogin'] != "admin")
		{
			redirect('store/index', 'refresh');
			return;
		}
		$this->load->model('order_model');
		$this->load->model('order_items_model');
		$this->load->model('customer_model');
	
		// get all items needed to be deleted
		$orders = $this->order_model->getAll();
		$order_items = $this->order_items_model->getAll();
		$customers = $this->customer_model->getAll();
	
		// delete orders, order items and customer information
		foreach ($orders as $order) {
			$this->order_model->delete($order->id);
		}
		foreach ($order_items as $order_item) {
			$this->order_items_model->delete($order_item->id);
		}
		foreach ($customers as $customer) {
			$this->customer_model->delete($customer->id);
		}
		redirect('store/viewFinalOrders', 'refresh');
	}
}

