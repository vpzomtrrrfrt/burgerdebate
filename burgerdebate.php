<?php
/**
 * Plugin Name: Burger Debate
 */
function debug($message) {
	if(WP_DEBUG===true) {
		error_log($message);
	}
}
function bd_install($reset_cookie_id=true) {
	global $wpdb;
	$table1_name = $wpdb->prefix."bd_posts";
	$table2_name = $wpdb->prefix."bd_votes";
	$table3_name = $wpdb->prefix."bd_login";
	$sql1 = "CREATE TABLE IF NOT EXISTS `$table1_name` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int(11) NOT NULL,
  `text` text NOT NULL,
  `debate_id` int(16) not null,
  PRIMARY KEY (`id`)
)";
	$sql2 = "CREATE TABLE IF NOT EXISTS `$table2_name` (
	cookie_id int(32) not null,
	vote int(11) not null,
	debate_id int(16) not null,
	UNIQUE (`cookie_id`)
)";
	$sql3 = "CREATE TABLE IF NOT EXISTS `$table3_name` (
	user1pass text not null,
	user2pass text not null,
	user3pass text not null,
	debate_id int(16) not null
)";
	require_once(ABSPATH.'wp-admin/includes/upgrade.php');
	dbDelta($sql1);
	dbDelta($sql2);
	dbDelta($sql3);
	if($reset_cookie_id){add_option('bd_cookie_id',1);}
}
register_activation_hook(__FILE__,'bd_install');
function bd_plugin_settings_page() {
	if(!current_user_can('manage_options')) {
		echo('You don\'t have sufficient permissions to access this page.');
	}
?>
	<div class="wrap">
		<h2>Burger Debate</h2>
		<script type="text/javascript">
			var stuff = {};
			var wordbank="burger debate web site seventeen money class awesome numbers extra chat whiteboard journalism online student classic school mission prepare meaning life leader canvas method mentor".split(' ');
			function generatePass(obj) {
				obj.value=wordbank[Math.floor(Math.random()*wordbank.length)]+wordbank[Math.floor(Math.random()*wordbank.length)]+Math.floor(Math.random()*1000);
			}
			function loadPasswords(did) {
				jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>',{action: 'LoadBDPasswords', debate_id: did},function(d){
						d=JSON.parse(d);
						stuff.d=d;
						console.log(d);
						console.log("^^");
						document.getElementById('debate_id').value=d.debate_id;
						document.getElementById('debate_id_text').innerHTML=d.debate_id;
						document.getElementById('user1pass').value=d.user1pass;
						document.getElementById('user2pass').value=d.user2pass;
						document.getElementById('user3pass').value=d.user3pass;
				}); 
			}
			function bdSettingsTravel(d) {
				loadPasswords(d+parseInt(document.getElementById('debate_id').value,10));
			}
			function resetDatabase() {
				jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>',{action: 'ResetBDTables'}, function(d) {});
			}
			jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>',{action: 'GetRecentDebate'},function(d){loadPasswords(d);});
		</script>
		<button onclick="bdSettingsTravel(-1)"><<</button>Debate <span id="debate_id_text"></span><button onclick="bdSettingsTravel(1)">>></button>
		<form method="post" onsubmit="jQuery.post('<?php echo admin_url("admin-ajax.php"); ?>',{action:'SaveBDPasswords',user1pass:document.getElementById('user1pass').value,user2pass:document.getElementById('user2pass').value,user3pass:document.getElementById('user3pass').value,debate_id:document.getElementById('debate_id').value},function(d){console.log(d);});return false;">
			<table class="form-table">
				<input type="hidden" name="debate_id" id="debate_id" />
				<tr valign="top">
					<th scope="row"><label for="user1pass">User 1 Password</label></th>
					<td><input type="text" name="user1pass" id="user1pass" /><button onclick="generatePass(user1pass)">Generate</button></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="user2pass">User 2 Password</label></th>
					<td><input type="text" name="user2pass" id="user2pass" /><button onclick="generatePass(user2pass)">Generate</button></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="user3pass">Moderator Password</label></th>
					<td><input type="text" name="user3pass" id="user3pass" /><button onclick="generatePass(user3pass)">Generate</button></td>
				</tr>
			</table>
			<?php @submit_button(); ?>
		</form>
	</div>
<?php
}
function bd_add_menu() {
	$whatever = add_menu_page('Burger Debate', 'Burger Debate', 'manage_options', 'bd-admin', 'bd_plugin_settings_page');
}
add_action('admin_menu','bd_add_menu');
function bd_plugin_settings_link($links) {
	array_unshift($links, '<a href="admin.php?page=bd-admin">Settings</a>');
	return $links;
}
add_filter("plugin_action_links_".plugin_basename(__FILE__), 'bd_plugin_settings_link');
function bd_shortcode_handler($atts) {
	$ajaxurl = addslashes(admin_url('admin-ajax.php'));
	$pa = shortcode_atts(array('debate_id'=>0),$atts);
	$scdi = intval($pa['debate_id']);
	return <<<HTML
		<style type="text/css">
			.bdpost {border-radius: 10px; border: 1px solid #999; padding: 5px; width: 70%; position: relative}
			.bdpost p {display: inline}
			.bdbutton {
   border: 1px solid #000000;
   background: #2f447d;
   background: -webkit-gradient(linear, left top, left bottom, from(#495d9b), to(#2f447d));
   background: -webkit-linear-gradient(top, #495d9b, #2f447d);
   background: -moz-linear-gradient(top, #495d9b, #2f447d);
   background: -ms-linear-gradient(top, #495d9b, #2f447d);
   background: -o-linear-gradient(top, #495d9b, #2f447d);
   padding: 10px 5px;
   -webkit-border-radius: 2px;
   -moz-border-radius: 2px;
   border-radius: 2px;
   -webkit-box-shadow: rgba(0,0,0,.4) 0 2px 0;
   -moz-box-shadow: rgba(0,0,0,.4) 0 2px 0;
   box-shadow: rgba(0,0,0,.4) 0 2px 0;
   text-shadow: rgba(0,0,0,.4) 0 2px 0;
   color: white;
   font-size: 16px;
   font-family: Georgia, Serif;
   text-decoration: none;
   vertical-align: middle;
   margin: 2px
   }
.bdbutton:hover {
   border-top-color: #2b2e65;
   background: #2b2e65;
   color: #ffffff;
   -webkit-box-shadow: rgba(0,0,0,.4) 0 1px 0;
   -moz-box-shadow: rgba(0,0,0,.4) 0 1px 0;
   box-shadow: rgba(0,0,0,.4) 0 1px 0;
   }
.bdbutton:active {
   border-top-color: #2b2f65;
   background: #2b2f65;
   }
			.user1post {background: radial-gradient(#f00, #b00); background: -webkit-radial-gradient(#f00, #b00); margin-right: 15%}
			.user2post {background: radial-gradient(#00f, #00b); background: -webkit-radial-gradient(#00f, #00b); margin-left: 15%}
			.user3post {background: radial-gradient(#0f0, #0b0); background: -webkit-radial-gradient(#0f0, #0b0); margin-right: 7%; margin-left: 7%}
			.modbox {display: none; height: 0px}
			.bd_mod_loggedin div .modbox {display: block; height: auto; font-size: 8pt; color: #0a0}
		</style>
		<script type="text/javascript">
			var bdplugindebugdata = {};
			var ajaxurl="$ajaxurl";
			var debate_id="$scdi";
			function loadBDPosts(ele,callback) {
				jQuery.getJSON(
					ajaxurl,
					{
						action: 'loadBDPosts',
						debate_id: debate_id,
					},
					function(d) {
						var doMessage = true;
						console.log(d);
						for(var p in d) {
							doMessage=false;
							var post = document.createElement('div');
							var txt = document.createElement('p');
							txt.className='bdposttxt';
							var txtbox = document.createElement('textarea');
							txtbox.className='bdtxtbox';
							txtbox.style.display='none';
							var savebtn=document.createElement('button');
							savebtn.className='bdbutton bdsavebtn';
							savebtn.style.display='none';
							savebtn.innerHTML='Update';
							post.className='user'+d[p].user_id+'post bdpost';
							txt.innerHTML=d[p].text;
							post.appendChild(txt);
							post.appendChild(txtbox);
							post.appendChild(savebtn);
							var modbox = document.createElement('div');
							modbox.className='modbox';
							var editbtn = document.createElement('a');
							editbtn.innerHTML='Edit';
							savebtn.bdPostId=d[p].id;
							savebtn.bdPostDiv=post;
							console.log(editbtn.bdPostId);
							editbtn.onclick=function() {
								this.parentNode.style.display='none';
								this.parentNode.parentNode.getElementsByClassName('bdtxtbox')[0].value=this.parentNode.parentNode.getElementsByClassName('bdposttxt')[0].innerHTML;
								this.parentNode.parentNode.getElementsByClassName('bdtxtbox')[0].style.display='inline';
								this.parentNode.parentNode.getElementsByClassName('bdposttxt')[0].style.display='none';
								this.parentNode.parentNode.getElementsByClassName('bdsavebtn')[0].style.display='inline';
								this.parentNode.parentNode.getElementsByClassName('bdsavebtn')[0].onclick=function() {
									console.log(this.bdPostId);
									jQuery.ajax({
										url: ajaxurl,
										data: {action: 'ModEditBDPost', bdpostid: this.bdPostId, key: mod_password, text: this.bdPostDiv.getElementsByClassName('bdtxtbox')[0].value, debate_id: debate_id},
										type: "POST",
										success: function(d) {
											if(d=="success") {
												reloadBDPosts();
											}
											else {
												alert(d);
											}
										}
									});
								}
							};
							modbox.appendChild(editbtn);
							post.appendChild(modbox);
							ele.appendChild(post);
						}
						if(doMessage) {
							ele.innerHTML="No posts yet!";
						}
						document.getElementById('loading').style.display='none';
						document.getElementById('bd-post-area').style.display='inline';
						callback(ele);
					}
				);
			}
			function reloadBDPosts() {
				document.getElementById('post').value="";
				document.getElementById('bd-form-area').style.display='none';
				document.getElementById('bdformexpand').style.display='inline';
				loadBDPosts(document.createElement('div'), function(d) {var bdpa = document.getElementById('bd-post-area');
				while(bdpa.firstChild) {
					bdpa.removeChild(bdpa.firstChild);
				}bdpa.appendChild(d);});
			}
			window.onload=function(){reloadBDPosts();};
			function loadBDPoll() {
				jQuery.getJSON(
					ajaxurl,
					{
						action: 'loadBDPoll',
						debate_id: debate_id
					},
					function(d) {
						console.log(d);
						var cnvs = document.createElement('canvas');
						cnvs.width=100;
						cnvs.height=100;
						var ctx = cnvs.getContext('2d');
						if(!d.counts[1]){d.counts[1]=0;}
						if(!d.counts[2]){d.counts[2]=0;}
						var totalvotes = d.counts[1]+d.counts[2];
						console.log(totalvotes);
						var linepos = d.counts[1]*2*Math.PI/totalvotes;
						console.log(linepos);
						ctx.fillStyle="red";
						ctx.beginPath();
						ctx.moveTo(50,50);
						ctx.arc(50,50,50,0,linepos);
						ctx.lineTo(50,50);
						ctx.fill();
						ctx.fillStyle="blue";
						ctx.beginPath();
						ctx.moveTo(50,50);
						ctx.arc(50,50,50,linepos,Math.PI*2);
						ctx.lineTo(50,50);
						ctx.fill();
						document.getElementById('bd_poll_chart').src=cnvs.toDataURL();
						var bdvf = document.getElementById('bd_vote');
						bdvf.onsubmit=function(e){e.preventDefault();return false;}
						var u1o = document.createElement('input');
						var u2o = document.createElement('input');
						u1o.type="radio";
						u2o.type="radio";
						u1o.name="bdpollvote";
						u2o.name="bdpollvote";
						u1o.id="u1o";
						u2o.id="u2o";
						var u1l = document.createElement('label');
						var u2l = document.createElement('label');
						u1l.appendChild(u1o);
						u2l.appendChild(u2o);
						u1l.innerHTML+="Red";
						u2l.innerHTML+="Blue";
						bdvf.appendChild(u1l);
						bdvf.appendChild(u2l);
						if(d.uservote=="1"){document.getElementById('u1o').checked='checked';}
						else if(d.uservote=="2"){document.getElementById('u2o').checked='checked';}
						var votebutton = document.createElement('button');
						votebutton.innerHTML='Vote';
						votebutton.className='bdbutton';
						votebutton.onclick=function() {
							var uservote = 0;
							if(document.getElementById('u2o').checked==true) {uservote=2;}
							if(document.getElementById('u1o').checked==true) {uservote=1;}
							if(uservote!=0) {
								console.log(uservote);
								jQuery.ajax({
									url: ajaxurl,
									data: {
										action: 'BDPollVote',
										vote: uservote,
										debate_id: debate_id
									},
									type: "POST",
									success: function(d) {
										while(bdvf.firstChild) {bdvf.removeChild(bdvf.firstChild);}
										loadBDPoll();
									}
								});
							}
							else {
								alert("Click the debater you are voting for!");
							}
						};
						bdvf.appendChild(votebutton);
					}
				);
			}
			loadBDPoll();
			function postBDMessage() {
				jQuery.ajax({
					dataType: "text",
					type: "POST",
					url: ajaxurl,
					data: {
						action: 'addBDPost',
						key: document.getElementById('bd_login').value,
						text: document.getElementById('post').value,
						debate_id: debate_id
					},
					success: function(d) {
						if(d=="success") {
							reloadBDPosts();
						}
						else {
							alert(d);
						}
					}
				});
			}
			var mod_password;
			function mod_button(ele) {
				mod_password=prompt("Enter mod password: ");
				if(!mod_password) {
					return;
				}
				jQuery.ajax({
					type: "POST",
					url: ajaxurl,
					data: {
						action: 'BDModLogin',
						key: mod_password,
						debate_id: debate_id
					},
					success: function(d) {
						if(d=="success") {
							ele.style.display="none";
							document.getElementById('bd-post-area').className="bd_mod_loggedin";
							document.getElementById('bd_login').value=mod_password;
							document.getElementById('bd_login').style.display="none";
							document.getElementById('maybetext').style.display="inline";
							document.getElementById('maybetext').innerHTML="Posting as moderator.";
							document.getElementById('bdformexpand').innerHTML='Post';
						}
						else {
							alert("Incorrect password.");
						}
					}
				});
			}
		</script>
		<div id="bd-area">
			<a onclick="mod_button(this)" style="cursor: pointer">Mod Login</a><br />
			<button class="bdbutton" id="bdformexpand" onclick="this.style.display='none';document.getElementById('bd-form-area').style.display='inline';">Post (only for debaters)</button>
			<a href="javascript:reloadBDPosts()" style="text-decoration: none; font-size: 20pt">&#8635</a>
			<div id="bd-form-area" style="display: none">
				<input type="password" id="bd_login" placeholder="Password" style="width: 100%" /><br />
				<p id="maybetext" style="display: none"></p>
				<textarea id="post"></textarea>
				<button class="bdbutton" onclick="if(confirm('Are you sure you want to post?')) {postBDMessage();}">Post</button>
				<button class="bdbutton" onclick="document.getElementById('bd-form-area').style.display='none';document.getElementById('bdformexpand').style.display='inline';">Cancel</button>
			</div>
			<br /><br />
			<p id="loading">Loading...</p>
			<div id="bd-post-area" style="display: none"></div>
			<h4>Voting</h4><img id="bd_poll_chart" /><form id="bd_vote"></form>
		</div>
HTML;
}
add_shortcode('bd-content', 'bd_shortcode_handler');
$debate_id=0;
if(isset($_REQUEST['debate_id'])) {
	$debate_id=intval($_REQUEST['debate_id']);
}
$pwds = bd_get_passwords($debate_id);
function bd_load_posts() {
	global $wpdb;
	global $debate_id;
	$table1_name=$wpdb->prefix."bd_posts";
	$rows = $wpdb->get_results("SELECT * FROM $table1_name WHERE debate_id=$debate_id ORDER BY id;");
	echo json_encode($rows);
	die();
}
add_action('wp_ajax_loadBDPosts','bd_load_posts');
add_action('wp_ajax_nopriv_loadBDPosts','bd_load_posts');
function bd_load_poll() {
	global $wpdb;
	global $debate_id;
	if(!isset($_COOKIE['bd_id'])) {
		$cookie_id=get_option('bd_cookie_id');
		update_option('bd_cookie_id',$cookie_id+1);
	}
	else {
		$cookie_id=$_COOKIE['bd_id'];
	}
	setcookie('bd_id',$cookie_id,time()+60*60*24*365);
	$table2_name=$wpdb->prefix."bd_votes";
	$rows = $wpdb->get_results("SELECT * FROM $table2_name WHERE debate_id=$debate_id");
	$theirvote=0;
	$votecount=array();
	foreach($rows as $v) {
		if($v->cookie_id==$cookie_id) {
			$theirvote=$v->vote;
		}
		if(!isset($votecount[$v->vote])){$votecount[$v->vote]=0;}
		$votecount[$v->vote]++;
	}
	echo json_encode(array('uservote'=>$theirvote,'counts'=>$votecount));
	die();
}
add_action('wp_ajax_loadBDPoll','bd_load_poll');
add_action('wp_ajax_nopriv_loadBDPoll','bd_load_poll');
function bd_poll_vote() {
	global $wpdb;
	global $debate_id;
	$table2_name=$wpdb->prefix."bd_votes";
	$cid = addslashes($_COOKIE['bd_id']);
	$wpdb->query("DELETE FROM $table2_name WHERE cookie_id=\"$cid\" AND debate_id=$debate_id");
	$wpdb->insert($table2_name,array("cookie_id"=>$cid,"vote"=>$_POST['vote'],"debate_id"=>$debate_id));
	die();
}
add_action('wp_ajax_BDPollVote','bd_poll_vote');
add_action('wp_ajax_nopriv_BDPollVote','bd_poll_vote');
function bd_add_post() {
	global $pwds;
	$user = 0;
	if($_POST['key']==$pwds['user1pass']) {
		$user=1;
	}
	else if($_POST['key']==$pwds['user2pass']) {
		$user=2;
	}
	else if($_POST['key']==$pwds['user3pass']) {
		$user=3;
	}
	if($user==0) {
		echo 'Incorrect password.';
	}
	else {
		global $wpdb;
		global $debate_id;
		$table1_name=$wpdb->prefix."bd_posts";
		if($wpdb->insert($table1_name,array("user_id"=>$user,"text"=>stripslashes($_POST['text']),"debate_id"=>$debate_id))) {
			echo 'success';
		}
		else {
			echo 'Could not add to the database.';
		}
	}
	die();
}
add_action('wp_ajax_nopriv_addBDPost','bd_add_post');
add_action('wp_ajax_addBDPost','bd_add_post');
function bd_mod_login() {
	global $pwds;
	if($_POST['key']==$pwds['user3pass']) {
		echo 'success';
	}
	else {
		echo 'failure';
	}
	die();
}
add_action('wp_ajax_BDModLogin','bd_mod_login');
add_action('wp_ajax_nopriv_BDModLogin','bd_mod_login');
function bd_mod_edit_post() {
	global $pwds;
	if($_POST['key']==$pwds['user3pass']) {
		global $wpdb;
		global $debate_id;
		if($wpdb->update($wpdb->prefix."bd_posts",array('text'=>stripslashes($_POST['text'])),array('id'=>$_POST['bdpostid']))) {
			die('success');
		}
		die('Failed to modify post');
	}
	die('This website is more secure than that!');
}
add_action('wp_ajax_ModEditBDPost','bd_mod_edit_post');
add_action('wp_ajax_nopriv_ModEditBDPost','bd_mod_edit_post');
function bd_get_passwords($dbi) {
	global $wpdb;
	$rows = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."bd_login WHERE debate_id=".$dbi,ARRAY_A);
	if(count($rows)<1) {
		$tr = array('user1pass'=>'leftleft','user2pass'=>'rightright','user3pass'=>'middleman','debate_id'=>$dbi); 
		$wpdb->insert($wpdb->prefix."bd_login",$tr);
		return $tr;
	}
	return $rows[0];
}
function bd_load_passwords() {
	$row = bd_get_passwords($_POST['debate_id']);
	die(json_encode($row));
}
add_action('wp_ajax_LoadBDPasswords','bd_load_passwords');
function bd_recent_debate() {
	global $wpdb;
	$rows = $wpdb->get_results("SELECT debate_id FROM ".$wpdb->prefix."bd_posts ORDER BY id DESC LIMIT 1;",ARRAY_A);
	if(count($rows)>0) {
		die($rows[0]['debate_id']);
	}
	else {die('0');}
}
add_action('wp_ajax_GetRecentDebate','bd_recent_debate');
function bd_save_passwords() {
	global $wpdb;
	$select = array('user1pass','user2pass','user3pass');
	$filtered = array();
	foreach($select as $sel) {
		$filtered[$sel]=$_POST[$sel];
	}
	$wpdb->update($wpdb->prefix."bd_login",$filtered,array('debate_id'=>$_POST['debate_id']));
	die('success');
}
add_action('wp_ajax_SaveBDPasswords','bd_save_passwords');
function bd_reset_tables() {
	global $wpdb;
	$wpdb->query('DROP TABLE IF EXISTS '.$wpdb->prefix.'bd_posts;');
	bd_install(false);
	die();
}
add_action('wp_ajax_ResetBDTables','bd_reset_tables');
?>
