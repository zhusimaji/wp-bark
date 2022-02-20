<?php
/**
 * @package wordpress plugin for bark
 * @version 1.0
 */
/*
Plugin Name: bark
Plugin URI: http://wordpress.org/plugins/bark/
Description:
Author: zhusimaji
Version: 1.0
Author URI: https://www.deeplearn.me
*/
/**
 * 初始化设置项
 */
function bark_activate() {
	add_option('bark_url','https://api.day.app');
	add_option('bark_key','');
	add_option('bark_param','');
	add_option('bark_scence','');
}
register_activation_hook( __FILE__, 'bark_activate' );

function bark_reset() {
	update_option('bark_url','https://api.day.app');
	update_option('bark_key','');
	update_option('bark_param','');
	update_option('bark_scence','');
}
/**
 * 菜单项注册
 */

/**
 * 注册菜单项
 */
function bark_custom_menu(){
	add_menu_page(
		'bark 首页',
		'bark',
		'manage_options',
		'bark_optionpage',
		'bark_custom_page',
		'dashicons-admin-generic',
		100
	);

}
add_action( 'admin_menu', 'bark_custom_menu' );

function bark_custom_page(){
	?>
	<?php
	if ($_POST && $_POST['bark_url'] != null && $_POST['save'] != null && check_admin_referer( 'bark_copyright' )){

		update_option( 'bark_url', $_POST['bark_url'] );
		update_option( 'bark_key', $_POST['bark_key'] );
		update_option( 'bark_param', $_POST['bark_param'] );
        // 需要将当前post里面的数据包装成 json 字符串存储
        $clean_option=option_save( $_POST['bark_scence']);
        $option_save=json_encode($clean_option);
		update_option( 'bark_scence',  $option_save);
		$bark_url = $_POST['bark_url'];
		$bark_key = $_POST['bark_key'];
		$bark_param= $_POST['bark_param'];
        $bark_scence =$clean_option;

		?>
		<div id="message" class="updated"><p><strong>信息更新成功！</strong></p>
		</div>
		<?php
    }

    else{
		$bark_url  = get_option('bark_url');
		$bark_key  = get_option('bark_key');
		$bark_param  = get_option('bark_param');
		$bark_scence = option_decode(get_option('bark_scence'));
	}
	?>
	<h1>bark 参数设置</h1>
	<form method="POST" action="">
		<table class="form-table">
            <tbody>
                <tr>
                    <th><label for="input-example">后台服务网址</label></th>
                    <td><input id="bark_url" name="bark_url" value="<?php echo $bark_url;?>"/></td>
                </tr>
                <tr>
                    <th><label for="input-example">key</label></th>
                    <td><input id="bark_key" name="bark_key" value="<?php echo $bark_key;?>" /></td>
                </tr>
                <tr>
                    <th><label for="input-example">消息格式</label></th>
                    <td><input id="bark_param" name="bark_param"  value="<?php echo $bark_param;?>" /></td>
                </tr>
                <tr>
                    <th scope="row">场景</th>
                    <td><fieldset><legend class="screen-reader-text"><span>场景</span></legend>
                            <label for="bark_comment"><input type="checkbox" name="bark_scence[]" value="1" <?php checked( 1 == $bark_scence[0] ); ?>> 评论时推送消息</label>
                            <br>
                            <label for="bark_sign"><input name="bark_scence[]" type="checkbox"  value="1" <?php checked( 1 == $bark_scence[1] ); ?>> 用户在注册时发送bark通知</label>
                            <br>
                </tr>

            </tbody>
		</table>

        <p class="submit"><input type="submit" name="save"  class="button button-primary" value="保存"></p>
		<?php
		wp_nonce_field('bark_copyright');
		?>
	</form>
	<?php
}
function option_save( $options ) {
	if( !is_array( $options ) || empty( $options ) || ( false === $options ) )
		return array();

	$clean_options = array();

	for($i=0;$i< count($options);$i++){
		if( isset( $options[$i] ) && ( 1 == $options[$i] ) )
			$clean_options[$i] = 1;
        else
	        $clean_options[$i] = 0;
	}
	return $clean_options;
}

function option_decode( $options ) {
    $output=array();
    if(empty($options))
        return array([0,0]);
    $decode_option=json_decode($options);
	for($i=0;$i< count($decode_option);$i++){

        $output[$i]=(int)$decode_option[$i];
	}

    return $output;
}

function bark_comment_msg_send_callback($comment_ID) {

	$comment = get_comment( $comment_ID );

	$message = $comment->comment_content;
	$bark_url  = get_option('bark_url');
	$bark_key  = get_option('bark_key');
	$bark_param  = get_option('bark_param');
	$bark_scence = option_decode(get_option('bark_scence'));

    if(empty($bark_param)){
	    $url = $bark_url."/".$bark_key."/".$message;
    }else{
	    $url = $bark_url."/".$bark_key."/".$message."/?".$bark_param;
    }



	if($bark_scence[0]==1)
	    echo file_get_contents($url);


}
// hook in comment  when user post comment
add_action( 'comment_post', 'bark_comment_msg_send_callback',10);

function bark_user_reg_send_callback($user_id){
	$user = get_userdata( $user_id );
	$message="大人，有新用户注册了,注册邮箱是"." ".$user->user_email;
	$bark_url  = get_option('bark_url');
	$bark_key  = get_option('bark_key');
	$bark_param  = get_option('bark_param');
	$bark_scence = option_decode(get_option('bark_scence'));

	if(empty($bark_param)){
		$url = $bark_url."/".$bark_key."/".$message;
	}else{
		$url = $bark_url."/".$bark_key."/".$message."/?".$bark_param;
	}



	if($bark_scence[0]==1)
		echo file_get_contents($url);

}


add_action( 'user_register', 'bark_user_reg_send_callback',10);

