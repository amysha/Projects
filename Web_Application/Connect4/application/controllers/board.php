<?php

class Board extends CI_Controller {
     
    function __construct() {
    		// Call the Controller constructor
	    	parent::__construct();
	    	session_start();
    } 
          
    public function _remap($method, $params = array()) {
	    	// enforce access control to protected functions	
    		
    		if (!isset($_SESSION['user']))
   			redirect('account/loginForm', 'refresh'); //Then we redirect to the index page again
 	    	
	    	return call_user_func_array(array($this, $method), $params);
    }
    
    
    function index() {
		$user = $_SESSION['user'];
    		    	
	    	$this->load->model('user_model');
	    	$this->load->model('invite_model');
	    	$this->load->model('match_model');
	    	
	    	$user = $this->user_model->get($user->login);

	    	$invite = $this->invite_model->get($user->invite_id);
	    	
	    	if ($user->user_status_id == User::WAITING) {
	    		$invite = $this->invite_model->get($user->invite_id);
	    		$otherUser = $this->user_model->getFromId($invite->user2_id);
	    		$opponent = 2;
	    		
	    	} else if ($user->user_status_id == User::PLAYING) {
	    		$match = $this->match_model->get($user->match_id);
	    		if ($match->user1_id == $user->id){
	    			$otherUser = $this->user_model->getFromId($match->user2_id);
	    			$opponent = 2;
	    		} else {
	    			$otherUser = $this->user_model->getFromId($match->user1_id);
	    			$opponent = 1;
	    		}
	    		
	    	}
	    	$data['opponent']=$opponent;
	    	$data['user']=$user;
	    	$data['otherUser']=$otherUser;
	    	
	    	
	    	switch($user->user_status_id) {
	    		case User::PLAYING:	
	    			$data['status'] = 'playing';
	    			break;
	    		case User::WAITING:
	    			$data['status'] = 'waiting';
	    			break;
	    	}	
		$this->load->view('match/board',$data);
    }

 	function postMsg() {
 		$this->load->library('form_validation');
 		$this->form_validation->set_rules('msg', 'Message', 'required');
 		
 		if ($this->form_validation->run() == TRUE) {
 			$this->load->model('user_model');
 			$this->load->model('match_model');

 			$user = $_SESSION['user'];
 			 
 			$user = $this->user_model->getExclusive($user->login);
 			if ($user->user_status_id != User::PLAYING) {	
				$errormsg="Not in PLAYING state";
 				goto error;
 			}
 			
 			$match = $this->match_model->get($user->match_id);			
 			
 			$msg = $this->input->post('msg');
 			
 			if ($match->user1_id == $user->id)  {
 				$msg = $match->u1_msg == ''? $msg :  $match->u1_msg . "\n" . $msg;
 				$this->match_model->updateMsgU1($match->id, $msg);
 			}
 			else {
 				$msg = $match->u2_msg == ''? $msg :  $match->u2_msg . "\n" . $msg;
 				$this->match_model->updateMsgU2($match->id, $msg);
 			}
 				
 			echo json_encode(array('status'=>'success'));
 			 
 			return;
 		}
		
 		$errormsg="Missing argument";
 		
		error:
			echo json_encode(array('status'=>'failure','message'=>$errormsg));
 	}
 
	function getMsg() {
 		$this->load->model('user_model');
 		$this->load->model('match_model');
 			
 		$user = $_SESSION['user'];
 		 
 		$user = $this->user_model->get($user->login);
 		if ($user->user_status_id != User::PLAYING) {	
 			$errormsg="Not in PLAYING state";
 			goto error;
 		}
 		// start transactional mode  
 		$this->db->trans_begin();
 			
 		$match = $this->match_model->getExclusive($user->match_id);			
 			
 		if ($match->user1_id == $user->id) {
			$msg = $match->u2_msg;
 			$this->match_model->updateMsgU2($match->id,"");
 		}
 		else {
 			$msg = $match->u1_msg;
 			$this->match_model->updateMsgU1($match->id,"");
 		}

 		if ($this->db->trans_status() === FALSE) {
 			$errormsg = "Transaction error";
 			goto transactionerror;
 		}
 		
 		// if all went well commit changes
 		$this->db->trans_commit();
 		
 		//**
 		$board_state = json_decode($match->board_state);
 		$match_state = $match->match_status_id;
 		
 		echo json_encode(array('status'=>'success','message'=>$msg, 'board_state'=>$board_state, 'match_state'=>$match_state));
		return;
		
		transactionerror:
		$this->db->trans_rollback();
		
		error:
		echo json_encode(array('status'=>'failure','message'=>$errormsg));
 	}
 	
 	function move() {
 		$this->load->model('user_model');
 		$this->load->model('match_model');
 			
 		// get current user
 		$user = $_SESSION['user'];
 		$user = $this->user_model->get($user->login);
 		
 		// get current match
 		$match = $this->match_model->get($user->match_id);
 		$board_state = json_decode($match->board_state, true);
 		
 		//check turns ; player1 = host player
 		$player = (int)$board_state["player"];
 		if ($player == 1 && $user->id != $match->user1_id) {
 			$errormsg="It is not your turn yet";
 			goto error; 			
 		} else if ($player == 2 && $user->id != $match->user2_id){
 			$errormsg="It is not your turn yet";
 			goto error;
 		}
 		
 		/* Warning message if column is full*/
 		$column = $this->input->post('column');
 		if(!($this->drop($match->id, (int)$column, $board_state["board"], $player))){
 			$errormsg = "invalid move!";
 			goto error;
 		}
 		
 		return;
 			
 		error:
 			echo json_encode(array('status'=>'failure', 'message'=>$errormsg));
 			return;
 	}
 	
 	/* Drop disk on the play board  */
 	function drop($match_id, $col, $board, $cur_player) {
 		$this->load->model('user_model');
 		$this->load->model('match_model');
 		
 		for ($row=5; $row>=0; $row--){
 			if ($board[$row][$col] == 0){
 				$board[$row][$col] = $cur_player;
 				
 				//switch players
 				if($cur_player==1){
 					$next_player = 2;
 				}else{
 					$next_player = 1;
 				}
 				
 				// insert into database 
 				$this->match_model->updateBoard($match_id, array("board"=>$board, "player"=>$next_player));
 				
 				//check match status after every move 
 				$this->scorecheck($match_id, $board, $row, $col, $cur_player);
 				return TRUE;
 			}
 		}
 		return FALSE;
 	}
 	
 	
 	function scorecheck($match_id, $board, $row, $col, $cur_player){
 		$this->load->model('user_model');
 		$this->load->model('match_model');
 		
 		/* Check if tie */
 		$tie = TRUE;
 		foreach($board as $hor){
 			foreach($hor as $ver){
 				if ($ver == 0){
 					$tie = FALSE;
					break;
 				}
 			}
 		}
 		
 		if ($tie){
 			$status = Match::TIE;
 			$this->match_model->updateStatus($match_id, $status);
 			return;
 		}
 		
 		// '\' up-left diagonal check
 		$count = 0;
 		$c = $col;
 		$r = $row;
 		$i = 0;
 		while($r >=0 && $c >=0) {
 			if($board[$r][$c]==$cur_player){
 				$count++;
 			}
 			$r--;
 			$c--;
 			$i++;
 			if($i == 4) break;
 		}
 		if($count == 4){
 			goto done;
 		}
 		
 		// '/' down-left diagonal check
 		$count = 0;
 		$c = $col;
 		$r = $row;
 		$i = 0;
 		while($r <= 5 && $c >=0) { 
 			if($board[$r][$c]==$cur_player){
 				$count++;
 			}
 			$r++;
 			$c--;
 			$i++;
 			if($i == 4) break;
 		}
 		if($count >= 4){
 			goto done;
 		}
 		
 	 	/* '/' up-right diagonal check */
 		$count = 0;
 		$c = $col;
 		$r = $row;
 		$i = 0;
 		while($r >= 0 && $c <= 6) { 
 			if($board[$r][$c]==$cur_player){
 				$count++;
 			}
 			$r--;
 			$c++;
 			$i++;
 			if($i == 4) break;
 		}
 		if($count == 4){
 			goto done;
 		}		
 		
 		/* '\' down-right diagonal check */
 		$count = 0;
 		$c = $col;
 		$r = $row;
 		$i = 0;
 		while($r <= 5 && $c <= 6) {
 			if($board[$r][$c]==$cur_player){
 				$count++;
 			}
 			$r++;
 			$c++;
 			$i++;
 			if($i == 4) break;
 		}
 		if($count == 4){
 			goto done;
 		}
 		 			
 		/* vertical check */
 		$count = 0;
 		$i = 0;
 		for ($r = $row; $r <= 5; $r++){
 			if($board[$r][$col]==$cur_player){
 				$count++;
 			}
 			$i ++;
 			if($i == 4) break;
 		}
 		if($count == 4){
			goto done; 			
 		}
 		
 		/* horizontal check */
 		if($col == 3){
 			$count = 0;
 			$i = 0;
 			// '->'  right check
 			for ($c=$col; $c<=6; $c++){
 				if($board[$row][$c]==$cur_player){
 					$count ++;
 				}
 				$i ++;
 				if($i == 4) break;
 			}
 			
 			if($count == 4){
 				goto done;
 			}
 			
 			// '<-' left check
 			$count = 0;
 			$i = 0;
 			for ($c=$col; $c>=0; $c--){
 				if($board[$row][$c]==$cur_player){
 					$count ++;
 				}
 				$i ++;
 				if($i == 4) break;
 			}
 			if($count == 4){
 				goto done;
 			}
 			
 		} else if($col < 3){
 			$count = 0;
 			$i = 0;
 			// '->'  right check
 			for ($c=$col; $c<=6; $c++){
 				if($board[$row][$c]==$cur_player){
 					$count ++;
 				}
 				$i ++;
 				if($i == 4) break;
 			}
 			
 			if($count == 4){
 				goto done;
 			}
 			
 		} else if($col > 3){
 			$count = 0;
 			$i = 0;
 			// '<-' left check
 			for ($c=$col; $c>=0; $c--){
 				if($board[$row][$c]==$cur_player){
 					$count ++;
 				}
 				$i ++;
 				if($i == 4) break;
 			}
 			if($count == 4){
 				goto done;
 			}
 		}
	 		
 		return;
 		
 		done:
			if ($cur_player == 1) {
				$status = Match::U1WON;
			} else {
				$status = Match::U2WON;
			}
			$this->match_model->updateStatus($match_id, $status );
			return;	
 	}
 }

