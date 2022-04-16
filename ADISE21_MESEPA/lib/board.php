<?php 
function show_board($input) {

	global $mysqli;
	$b=current_player($input['token']);
	//print_r($b);
	if($b) {
		show_board_by_player($b);
	} else {
		header('Content-type: application/json');
		print json_encode(read_board(), JSON_PRETTY_PRINT);
	}
}

function reset_board() {
    global $mysqli;

    $sql='call clean_board';
    $mysqli->query($sql);
    //show_board();
}

function show_board_by_player($b) {

	global $mysqli;

	
	$board=read_board();
	
	$status = read_status();
	if($status['status']=='started' && $status['p_turn']==$b && $b!=null) {
		// It my turn !!!!
		$n = add_valid_moves_to_board($board);
		
		// Εάν n==0, τότε έχασα !!!!!
		// Θα πρέπει να ενημερωθεί το game_status.
	}
	header('Content-type: application/json');
	print json_encode($board, JSON_PRETTY_PRINT);
}
function convert_board(&$orig_board) {
	$board=[];
	foreach($orig_board as $i=>&$row) {
		$board[$row['x']][$row['y']] = &$row;
	} 
	return($board);
}



function move_piece($x,$y,$color,$piece,$token) {
	
	if($token==null || $token=='') {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg'=>"token is not set."]);
		exit;
	}
	
	$player = current_player($token);
	if($player==null ) {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg'=>"You are not a player of this game."]);
		exit;
	}
	$status = read_status();
	if($status['status']!='started') {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg'=>"Game is not in action."]);
		exit;
	}
	if($status['p_turn']!=$player) {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg'=>"It is not your turn."]);
		exit;
	}
	// $orig_board=read_board();
	$board=read_board();
	$n = add_valid_moves_to_piece($x,$y);
	if($n==0) {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg'=>"This piece cannot be placed."]);
		exit;
	}
    else{
    do_move($x,$y,$color,$piece);
	update_game_status();
        exit;
    }

	// header("HTTP/1.1 400 Bad Request");
	// print json_encode(['errormesg'=>"This move is illegal."]);
	// exit;
}
function do_move($x,$y,$color,$piece) {

    global $mysqli;
    $sql = 'call place_piece(?,?,?,?);';
    $st = $mysqli->prepare($sql);
    $st->bind_param('ssss',$x,$y,$piece,$color );
    $st->execute();

    header('Content-type: application/json');
    print json_encode(read_board(), JSON_PRETTY_PRINT);

}


function read_board() {
	global $mysqli;
	$sql = 'select * from board';
	$st = $mysqli->prepare($sql);
	$st->execute();
	$res = $st->get_result();
	return($res->fetch_all(MYSQLI_ASSOC));
}


function add_valid_moves_to_piece($x,$y){
$a=read_board();
// echo "edy";
// print_r($a);
for ($i = 0; $i <= 16; $i++) {

    if($a[$i]['x']==$x){
            if($a[$i]['y']==$y){
                if($a[$i]['piece']==NULL){
                    return true;
                }else{
                    return false;}
            }
}

}
}

function add_valid_moves_to_board($board) {
	$number_of_moves=0;
	for ($i = 0; $i <= 16; $i++) {
		for ($j = 0; $j <= 16; $j++) {
			if($board==NULL){
				$number_of_moves=$number_of_moves +1;
			}	
	
	    }
}
}


function show_piece($x,$y) {
	global $mysqli;
	
	$sql = 'select * from board where x=? and y=?';
	$st = $mysqli->prepare($sql);
	$st->bind_param('ii',$x,$y);
	$st->execute();
	$res = $st->get_result();
	header('Content-type: application/json');
	print json_encode($res->fetch_all(MYSQLI_ASSOC), JSON_PRETTY_PRINT);
}

function read_piece_box(){
	global $mysqli;
	$sql = 'select * from piece';
	$st = $mysqli->prepare($sql);
	$st->execute();
	$res = $st->get_result();
	return($res->fetch_all(MYSQLI_ASSOC));
}
function show_piece_box($input){
		header('Content-type: application/json');
		print json_encode(read_piece_box(), JSON_PRETTY_PRINT);

}

function delete_piece($input){
	$piece=$input['piece'];
	$piece_color=$input['piece_color'];
	
	global $mysqli;
	$sql = 'update piece 
	set piece_color=null,
	piece=null 
	where piece_color=? and piece=?';
	$st = $mysqli->prepare($sql);
	$st->bind_param('ss',$piece_color,$piece);
	$st->execute();
	print_r($piece);
	
	
}
?> 