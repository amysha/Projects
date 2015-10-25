<!DOCTYPE html>

<html>
<head>
<link rel="stylesheet" href="<?= base_url() ?>/css/board.css">
<script src="http://code.jquery.com/jquery-latest.js"></script>
<script src="http://code.jquery.com/ui/1.9.1/jquery-ui.js"></script>
<script src="<?php echo base_url(); ?>js/jquery.color-2.1.2.min.js"></script>
<script src="<?= base_url() ?>js/jquery.timers.js"></script>
<script>

		var otherUser = "<?= $otherUser->login ?>";
		var user = "<?= $user->login ?>";
		var status = "<?= $status ?>";
		var opponent = "<?= $opponent ?>";
		var match_state = 1;
		
		$(function(){
			$('body').everyTime(1000,function(){
					if (status == 'waiting') {
						$.getJSON('<?= base_url() ?>index.php/arcade/checkInvitation',function(data, text, jqZHR){
								if (data && data.status=='rejected') {
									alert("Sorry, your invitation to play was declined!");
									window.location.href = '<?= base_url() ?>index.php/arcade/index';
								}
								if (data && data.status=='accepted') {
									status = 'playing';
									$('#status').html('Playing ' + otherUser);
								}
								
						});
					}
					var url = "<?= base_url() ?>index.php/board/getMsg";
					$.getJSON(url, function (data,text,jqXHR){
						if (data && data.status=='success') {
							var conversation = $('[name=conversation]').val();
							var msg = data.message;
							if (msg.length > 0){
								$('[name=conversation]').val(conversation + "\n" + otherUser + ": " + msg);
							}
			
						/* ****************************************************** */
						if(match_state == 1){ 
							var msg = "";
							match_state = data.match_state;
							if (data.match_state == 1){
								msg = "Active";
							} else if (data.match_state == 4) {
								msg = "Tie";
								$("#turn").html("");
								alert(msg);
							} else if (data.match_state == 2) {
								if(opponent == 2){
									msg = "You won!"
								} else {
									msg = otherUser + " won. You lost.";
								}
								$("#turn").html("");
								alert(msg);
							} else {
								// if data.match_state == 3
								if(opponent == 1){
									msg = "You won!";
								} else {
									msg = otherUser + " won. You lost.";
								}
								$("#turn").html("");
								alert(msg);
							}

							$("#gameStatus").html("Match Status: " + msg);
							
			                var board_state = data.board_state;
			                var cur_player = board_state.player;
			                if(opponent != cur_player){
			                    $('#turn').html("Your turn.");
			                }else{
			                    $('#turn').html(otherUser+"'s turn.");
			                }
			                
			                var board = board_state.board;
			                for (var c=0; c<=6; c++) {
				                for (var r=0; r<=5; r++) {
					                //div = $("#game").find("div").eq(c).find("div").eq(r);
					                div = $(".col#"+c+" > .empty").filter("#"+r);
					                if (div.attr("player") != board[r][c]) {
						                div.attr("player", board[r][c]);
				                	}
			                	}
			                }
						}
			            /* ********************************************************** */
						}});
			});

			$('form').submit(function(){
				var arguments = $(this).serialize();
				var url = "<?= base_url() ?>index.php/board/postMsg";
				$.post(url,arguments, function (data,textStatus,jqXHR){
						var conversation = $('[name=conversation]').val();
						var msg = $('[name=msg]').val();
						$('[name=conversation]').val(conversation + "\n" + user + ": " + msg);
						});
				return false;
				});	
		});

		$(document).ready(function() {
			  for(var i=0; i<7; i++){
                  $("#game").append('<div class="col" id='+i+'></div>')
              }
              for(var j=0; j<6; j++){
                      $(".col").append('<div class="empty" player="0" id= '+j+' ></div>')
                  }
              $(".col").mouseenter(function(){
                  $(this).animate({
                      backgroundColor: "#C0C0C0"
                  }, 200);
              });
              $(".col").mouseleave(function(){
                  $(this).animate({
                      backgroundColor: "blue"
                  }, 200);
              });
              $(".col").click(function(){
                  $(this).effect("highlight");
                  if (status == 'playing'){
                      var url = "<?php echo base_url(); ?>index.php/board/move";
                      $.post(url, "column="+this.id, function (data){
                          if (data) {
                              //console.log(data);
                              data = $.parseJSON(data);
                              if (data.status=='failure'){                
                                  var msg = data.message;
                                  alert(msg);
                              }
                          }
                      });
                  }                  
              })
		});
	
	</script>
</head>
<body>
	<h1>Game Area</h1>

	<div>
		Hello, 
		<?= $user->fullName()?>
		<?= anchor('arcade/index', '(Back)')?>
		<?= anchor('account/logout','(Logout)')?>
	</div>

	<div id='gameInfo'>
		<div id='status'>
			<?php
			if ($status == "playing")
				echo "Playing with: " . $otherUser->login;
			else
				echo "Wating on " . $otherUser->login;
			?>
		</div>
		<div id="gameStatus"></div>
		<div id='turn'></div>
	</div>
	<div id="game"></div>

	<?php
	if ($opponent == 1) {
		$num = 2;
		$player = 'yellow';
	} else {
		$num = 1;
		$player = 'red';
	}
    echo '<div> You:' . '&nbsp&nbsp' . '<span p=' . $num . '>' . $player . '</span>'. '</div>';
		
	echo form_textarea ( 'conversation' );
	
	echo form_open ();
	echo form_input ( 'msg' );
	echo form_submit ( 'Send', 'Send' );
	echo form_close ();
	//echo '<div class=empty id=' . $player . '></div>';
	?>
</body>

</html>

