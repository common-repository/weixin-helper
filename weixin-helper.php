<?php
/*
Plugin Name: 微信群发助手(WeChat Helper)
Author: 水脉烟香
Author URI: https://wptao.com/smyx
Plugin URI: https://wptao.com/weixin-helper.html
Description: 使用微信公众号、微博粉丝服务的[高级群发接口]实现WordPress自动群发给用户
Version: 1.0.1
*/

define('WECHAT_HELPER_VERSION', '1.0.1');
define("WEIXIN_HELPER_URL", plugins_url('weixin-helper'));

function weixin_helper_init() {
    load_plugin_textdomain( 'wechat', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action('init', 'weixin_helper_init');

add_action('admin_menu', 'weixin_helper_add_page');
function weixin_helper_add_page() {
	if (function_exists('add_menu_page')) {
		add_menu_page(__('WeChat Helper', 'wechat'), __('WeChat Helper', 'wechat'), 'manage_options', 'weixin-helper', 'weixin_helper_do_page', WEIXIN_HELPER_URL .'/images/weixin-logo.png');
	} 
	if (function_exists('add_submenu_page')) {
		add_submenu_page('weixin-helper', __('WeChat Helper', 'wechat'), __('General Settings', 'wechat'), 'manage_options', 'weixin-helper');
		add_submenu_page('weixin-helper', '微信手动群发', '手动群发', 'manage_options', 'weixin-helper&tag=hand', 'weixin_helper_do_page');
		add_submenu_page('weixin-helper', '群发记录', '群发记录', 'manage_options', 'weixin-helper-log', 'weixin_helper_log_do_page');
	} 
}
add_action('plugin_action_links_' . plugin_basename(__FILE__), 'weixin_helper_plugin_actions');
function weixin_helper_plugin_actions ($links) {
    $new_links = array();
    $new_links[] = '<a href="admin.php?page=weixin-helper">' . __('Settings') . '</a>';
    return array_merge($new_links, $links);
}
// 群发
function weixin_helper_do_page() {
	$tag = !empty($_GET['tag']) ? $_GET['tag'] : 'setting';
	$weixin_url = WEIXIN_HELPER_URL;
	echo '<div class="error"><p><strong>本插件为付费插件，此处仅作为后台展示，不能使用功能，如果您有需求，请【<a href="https://wptao.com/weixin-helper.html" target="_blank">点击这里</a>】购买插件，买后卸载本插件，<a href="https://wptao.com/download" target="_blank">重新下载</a>安装后使用。</strong></p></div>';
	?>
<style type="text/css">
.postbox label{float:none}
.postbox .hndle{border-bottom:1px solid #eee}
.nav-tab-wrapper{margin-bottom:15px}
.wptao-grid a{text-decoration:none}
.wptao-main{width:80%;float:left}
.wptao-sidebar{width:19%;float:right}
.wptao-sidebar ol{margin-left:10px}
.wptao-box{margin:10px 0px;padding:10px;border-radius:3px 3px 3px 3px;border-color:#cc99c2;border-style:solid;border-width:1px;clear:both}
.wptao-box.yellow{background-color:#FFFFE0;border-color:#E6DB55}
@media (max-width:782px){
.wptao-grid{display:block;float:none;width:100%}
}
.input-panel{min-height:42px;max-height:120px;overflow:auto;width:250px}
</style>
<div class="wrap">
  <h2><img src="<?php echo $weixin_url .'/images/icon_weixin.png';?>" /><?php _e('WeChat Helper', 'wechat');?> <code>v<?php echo WECHAT_HELPER_VERSION;?></code> <code><a target="_blank" href="https://wptao.com/weixin-helper.html">官网</a></code></h2>
  <div id="poststuff">
    <div id="post-body">
      <div class="nav-tab-wrapper">
		<a id="group-1-tab" class="nav-tab" title="基本设置" href="?page=weixin-helper#group-1">基本设置</a>
		<a id="group-2-tab" class="nav-tab" title="定时群发" href="?page=weixin-helper#group-2">定时群发</a>
		<a id="group-3-tab" class="nav-tab<?php echo ($tag == 'hand') ? ' nav-tab-active':'';?>" title="手动群发" href='?page=weixin-helper&tag=hand'>手动群发</a>
		<a id="group-4-tab" class="nav-tab" title="群发记录" href='?page=weixin-helper-log'>群发记录(包括素材)</a>
      </div>
      <div class="wptao-container">
        <div class="wptao-grid wptao-main">
<?php
if ($tag == 'setting') {
	if (!$helper) {
		$helper['cron'] = array('hour' => 22);
	}
?>
<form method="post" action="">
<?php wp_nonce_field('weixin-helper');?>
<div id="group-1" class="group" style="display: block;">
  <div class="postbox">
	<h3 class="hndle">
	  <label for="title">基本设置</label>
	</h3>
	<div class="inside">
	  <table class="form-table">
		<tbody>
		<?php
		if (function_exists('get_post_types')) { // 自定义文章类型
			$post_types = (array)get_post_types(array('public' => true, '_builtin' => false), 'objects', 'and');
			if ($post_types) {
			if (!$helper['post_types']) $helper['post_types'] = array('post');
		?>
		<tr>
		  <th scope="row">选择文章类型</th>
		  <td><div class="input-panel">
		  <label><input type="checkbox" name="helper[post_types][]" value="post"<?php checked(in_array('post', $helper['post_types']));?>>文章</label><br />
		  <label><input type="checkbox" name="helper[post_types][]" value="page"<?php checked(in_array('page', $helper['post_types']));?>>页面</label><br />
			<?php
			foreach ($post_types as $type_key => $object) {
				echo '<label><input type="checkbox" name="helper[post_types][]" value="'.$type_key.'" '. checked(in_array($type_key, $helper['post_types']), true, false) .'>'.$object -> labels -> name.'</label><br />';
			} ?></div>
			<p><code>提示：这边的设置会影响到【最新文章和热门文章的内容】</code></p>
		  </td>
		</tr>
		<?php }} ?>
		<tr>
		  <th scope="row">群发条数</th>
		  <td><input name="helper[item]" type="text" value="<?php echo $helper['item'] ? $helper['item'] : 6;?>" maxlength="1" size="5" onkeyup="value=value.replace(/[^\d]/g,'')"/> (最多8条)</td>
		</tr>
		<tr>
		  <th scope="row">当文章没有图片时</th>
		  <td><select name="helper[nopic]" id="helper_nopic">
			  <option value="0"<?php selected($helper['nopic'] == '0');?>>过滤掉文章</option>
			  <option value="1"<?php selected($helper['nopic'] == 1);?>>使用默认缩略图</option>
			</select></td>
		</tr>
		<tr id="helper_image_tr"<?php if($helper['nopic'] != 1) echo ' style="display:none"';?>>
		  <th scope="row">默认缩略图：</th>
		  <td><input name="helper[image]" id="upid-helper_image" type="text" size="40" value="<?php echo $helper['image'];?>" /> <input type="button" class="button upload_button" upid="helper_image" value="上传" /></td>
		</tr>
		<tr>
		  <th scope="row">正文头部</th>
		  <td><textarea name="helper[top]" cols="60" rows="3"><?php echo trim(stripslashes($helper['top']));?></textarea><p><code>支持HTML标签，建议插入图片(使用img标签)，提醒用户关注</code></p></td>
		</tr>
		<tr>
		  <th scope="row">正文内容</th>
		  <td><select name="helper[content]">
			  <option value="1"<?php selected($helper['content'] == 1);?>>全文</option>
			  <option value="5000"<?php selected($helper['content'] == 5000);?>>截取文章前5000个字符</option>
			  <option value="500"<?php selected($helper['content'] == 500);?>>截取文章前500个字符</option>
			  <option value="2"<?php selected($helper['content'] == 2);?>>文章摘要</option>
			  <option value="0"<?php selected($helper['content'] == '0');?>>留空</option>
			</select></td>
		</tr>
		<tr>
		  <th scope="row">正文底部</th>
		  <td><textarea name="helper[bottom]" cols="60" rows="3"><?php echo trim(stripslashes($helper['bottom']));?></textarea><p><code>支持HTML标签</code></p></td>
		</tr>
		</tbody>
	  </table>
	</div>
	<!-- end of inside -->
  </div>
  <!-- end of postbox -->
  <div class="postbox">
	<h3 class="hndle">
	  <label for="title">微信公众平台</label>
	</h3>
	<div class="inside">
	  <table class="form-table">
		<tbody>
		<tr>
		  <th scope="row">开发者ID(AppID)</th>
		  <td><input name="helper[weixin][0]" type="text" value="<?php echo $helper['weixin'][0];?>" size="40" autocomplete="off" /></td>
		</tr>
		<tr>
		  <th scope="row">开发者密码(AppSecret)</th>
		  <td><input name="helper[weixin][1]" type="text" value="<?php echo $helper['weixin'][1];?>" size="40" autocomplete="off" /></td>
		</tr>
		<tr>
		  <th scope="row">微信测试用户(openid)</th>
		  <td><input name="mp_users[weixin]" type="text" value="<?php echo $mp_users['weixin'];?>" size="40" autocomplete="off" />
		  <p><code>选填，<a href="http://www.smyx.net/getopenid.html" target="_blank">如何获取?</a></code></p></td>
		</tr>
		<tr>
		  <th scope="row">公众号类型</th>
		  <td><select name="helper[weixin][2]">
			  <option value="0"<?php selected($helper['weixin'][2] == '0');?>>未认证订阅号</option>
			  <option value="1"<?php selected($helper['weixin'][2] == 1);?>>已认证订阅号</option>
			  <option value="2"<?php selected($helper['weixin'][2] == 2);?>>未认证服务号</option>
			  <option value="3"<?php selected($helper['weixin'][2] == 3);?>>已认证服务号</option>
			</select>
			<p><code>提示：未认证公众号只能创建永久素材/获取素材列表，如需群发必须通过微信认证。</p></td>
		</tr>
		<tr>
		  <th scope="row">评论</th>
		  <td><select name="helper[comment]">
			  <option value="0"<?php selected($helper['comment'] == '0');?>>不开启</option>
			  <option value="1"<?php selected($helper['comment'] == 1);?>>所有人可评论</option>
			  <option value="2"<?php selected($helper['comment'] == 2);?>>粉丝才可评论</option>
			</select>
			<p><code>提示：请确保您的公众号在创建素材时有留言权限，否则可能会报错。</p></td>
		</tr>
		</tbody>
	  </table>
	</div>
	<!-- end of inside -->
  </div>
  <!-- end of postbox -->
  <div class="postbox">
	<h3 class="hndle">
	  <label for="title">微博粉丝服务（需要通过微博认证，即加V用户才能使用）</label>
	</h3>
	<div class="inside">
	  <table class="form-table">
		<tbody>
		<tr>
		  <th scope="row">access_token</th>
		  <td><input name="helper[weibo][0]" type="text" value="<?php echo $helper['weibo'][0];?>" size="40" autocomplete="off" /></td>
		</tr>
		<tr>
		  <th scope="row">微博测试用户(uid)</th>
		  <td><input name="mp_users[weibo]" type="text" value="<?php echo $mp_users['weibo'];?>" size="40" autocomplete="off" />
		  <p><code>选填，<a href="http://www.smyx.net/getopenid.html" target="_blank">如何获取?</a></code></p></td>
		</tr>
		</tbody>
	  </table>
	</div>
	<!-- end of inside -->
  </div>
  <!-- end of postbox -->
  <!--
  <div class="postbox">
	<h3 class="hndle">
	  <label for="title">易信公众平台</label>
	</h3>
	<div class="inside">
	  <table class="form-table">
		<tbody>
		<tr>
		  <th scope="row">开发者ID(AppID)</th>
		  <td><input name="helper[yixin][0]" type="text" value="<?php echo $helper['yixin'][0];?>" size="40" autocomplete="off" /></td>
		</tr>
		<tr>
		  <th scope="row">开发者密码(AppSecret)</th>
		  <td><input name="helper[yixin][1]" type="text" value="<?php echo $helper['yixin'][1];?>" size="40" autocomplete="off" /></td>
		</tr>
		<tr>
		  <th scope="row">易信测试用户(openid)</th>
		  <td><input name="mp_users[yixin]" type="text" value="<?php echo $mp_users['yixin'];?>" size="40" autocomplete="off" />
		  <p><code>选填，<a href="http://www.smyx.net/getopenid.html" target="_blank">如何获取?</a></code></p></td>
		</tr>
		</tbody>
	  </table>
	</div>
  </div>
  <!-- end of postbox -->
</div>
<div id="group-2" class="group" style="display: block;">
  <div class="postbox">
	<h3 class="hndle">
	  <label for="title">定时群发</label>
	</h3>
	<div class="inside">
	  <table class="form-table">
		<tbody>
		<tr>
		  <th scope="row">定时群发</th>
		  <td><label><input name="helper[cron][open]" type="checkbox" value="1" <?php if($helper['cron']['open']) echo "checked "; ?>> <?php _e('On', 'wechat');?></label></td>
		</tr>
		<tr>
		  <th scope="row">发送时间</th>
		  <td><select name="helper[cron][date]">
			  <option value="0"<?php selected(!$helper['cron']['date']);?>>每天</option>
<?php
$weeks = array('', '一', '二', '三','四','五','六', '日');
for ($week=1; $week<=7; $week++) {
	echo '<option value="'.$week.'"'.($helper['cron']['date'] == $week ? ' selected="selected"' : '').'>每周'.$weeks[$week].'</option>';
}
?>
			</select>
			<select name="helper[cron][hour]">
<?php
for ($hour=0; $hour<=23; $hour++) {
	echo '<option value="'.$hour.'"'.($helper['cron']['hour'] == $hour ? ' selected="selected"' : '').'>'.$hour.'</option>';
}
?>
</select>
点<select name="helper[cron][min]">
<?php
for ($min=0; $min<=11; $min++) {
	$_min = $min * 5;
	if ($_min < 10) $_min = '0' . $_min;
	echo '<option value="'.$_min.'"'.($helper['cron']['min'] == $_min ? ' selected="selected"' : '').'>'.$_min.'</option>';
}
$next_cron = 0;
?>
			</select>分 (当前服务器时间:<code><?php echo date('Y-m-d H:i:s');?></code><?php if(!empty( $next_cron )) echo ',下次群发时间:<code>' . date('Y-m-d H:i', $next_cron) . '</code>';?>)</td>
		</tr>
		<tr>
		  <th scope="row">当前没有文章时</th>
		  <td><select name="helper[cron][nopost]">
			  <option value="0"<?php selected($helper['cron']['nopost'] == '0');?>>不群发</option>
			  <option value="1"<?php selected($helper['cron']['nopost'] == 1);?>>随机文章</option>
			</select></td>
		</tr>
		<tr>
		  <th scope="row">发送内容</th>
		  <td><select name="helper[cron][post]">
			  <option value="1"<?php selected($helper['cron']['post'] == 1);?>>当天最新文章</option>
			  <option value="2"<?php selected($helper['cron']['post'] == 2);?>>当天热门文章(需要安装WP-PostViews插件)</option>
			  <option value="11"<?php selected($helper['cron']['post'] == 11);?>>七日内最新文章</option>
			  <option value="12"<?php selected($helper['cron']['post'] == 12);?>>七日内热门文章(需要安装WP-PostViews插件)</option>
			</select></td>
		</tr>
		<tr>
		  <th scope="row">发送类型</th>
		  <td><select name="helper[cron][send]">
			  <option value="1"<?php selected($helper['cron']['send'] == 1);?>>群发</option>
			  <option value="5"<?php selected($helper['cron']['send'] == 5);?>>创建永久素材</option>
			</select></td>
		</tr>
		<tr>
		  <th scope="row">发送后邮件通知，填写邮箱</th>
		  <td><input name="helper[cron][email]" type="text" value="<?php echo $helper['cron']['email'];?>" size="40" autocomplete="off" /></td>
		</tr>
		<tr>
		  <th scope="row">发送到</th>
		  <td>
		  <p><label><input name="helper[cron][weixin]" type="checkbox" value="1"<?php checked($helper['cron']['weixin']);?> />发给微信粉丝（由于服务号每月限4条，建议按周定时）</label></p>
		  <p><label><input name="helper[cron][weibo]" type="checkbox" value="1"<?php checked($helper['cron']['weibo']);?> />发给微博粉丝</label></p>
		  <!--<p><label><input name="helper[cron][yixin]" type="checkbox" value="1"<?php checked($helper['cron']['yixin']);?>/>发给易信粉丝</label></p>-->
		  </td>
		</tr>
		</tbody>
	  </table>
	</div>
	<!-- end of inside -->
  </div>
  <!-- end of postbox -->
</div>
<p class="submit">
  <input type="submit" name="weixin_helper_options" class="button-primary" value="<?php _e('Save Changes') ?>" />
</p>
</form>
<?php } elseif ($tag == 'hand') { ?>
<?php
if (!$helper) $helper = array();
?>
<div id="group-hand" class="group" style="display: block;">
  <div class="postbox">
	<h3 class="hndle">
	  <label for="title">手动群发</label>
	</h3>
	<div class="inside">
	  <table class="form-table" id="handSetting">
		<tbody>
		<tr>
		  <th scope="row">消息类型</th>
		  <td><select id="handType">
			  <option value="1"<?php selected($type === 1);?>>图文</option>
			  <option value="0"<?php selected($type === 0);?>>文本</option>
			</select></td>
		</tr>
		<tr id="content1"<?php if($type === 0) echo 'style="display:none"';?>>
		  <th scope="row">发送内容</th>
		  <td><select id="handMsg">
			  <option value="1"<?php selected($msg == 1);?>>今日最新文章</option>
			  <option value="2"<?php selected($msg == 2);?>>今日热门文章(需要安装WP-PostViews插件)</option>
			  <option value="11"<?php selected($msg == 11);?>>七日内最新文章</option>
			  <option value="12"<?php selected($msg == 12);?>>七日内热门文章(需要安装WP-PostViews插件)</option>
			  <option value="9"<?php selected($msg == 9);?>>自定义内容</option>
			  <?php if($msg == 3) { ?>
			  <option value="3"<?php selected($msg == 3);?>>微信图文素材(<?php echo $getid;?>)</option>
			  <?php } elseif($msg == 4) { ?>
			  <option value="4"<?php selected($msg == 4);?>>自定义图文（来自微信自定义回复素材）(<?php echo $getid;?>)</option>
			  <?php } ?>
			  <option value="44">自定义图文（来自微信自定义回复素材）</option>
			</select></td>
		</tr>
		<tr id="content2"<?php if(!isset($content)) echo 'style="display:none"';?>>
		  <th scope="row">自定义内容</th>
		  <td><textarea id="handContent" cols="60" rows="4"><?php echo $content;?></textarea><br>如果选择“图文”，自定义内容处请填写文章ID，多篇文章用英文逗号隔开，如: 1,2,3</td>
		</tr>
		<tr>
		  <th scope="row">
			  <?php if($msg == 3 || $msg == 4) { ?>
			  <input type="hidden" value="<?php echo $getid;?>" id="getid" />
			  <?php } ?>
		  </th>
		  <td>
		  <p><label><input type="checkbox" value="weixin" id="site0"<?php echo $site0;?> />发给微信粉丝</label> （<label><input type="checkbox" value="1" id="material"<?php echo $material;?> />创建永久素材</label>） <span id="ret0"></span></p>
		  <p><label><input type="checkbox" value="weibo" id="site2"<?php echo $site2;?> />发给微博粉丝</label> <span id="ret2"></span></p>
		  <!--<p><label><input type="checkbox" value="yixin" id="site1"<?php echo $site1;?> />发给易信粉丝</label> <span id="ret1"></span></p>-->
		  </td>
		</tr>
		</tbody>
	  </table>
		<?php
		$buttons = array(array(2 => '测试群发', 1 => '立即群发', 3 => '客服群发'), array(0 => '预览', 4 => '创建临时素材', 5 => '创建永久素材'));
		$disabled = ($helper['weixin'] && ($helper['weixin'][2] == '0' || $helper['weixin'][2] == '2'));
		foreach($buttons as $button) {
			echo '<p>';
			foreach($button as $btn_i => $btn_text) {
				echo '<input class="button button-primary" type="button" id="send'.$btn_i.'" value="'.$btn_text.'"'.($disabled && !in_array($btn_i, array('0', 5)) ? ' title="公众号未认证，没有相关权限，如果已经认证，可以在【基本设置】修改公众号类型" disabled' : '').' /> ';
			}
			echo '</p>';
		}
		?>
		<div id="massPreview"></div>
		<p><strong>使用说明</strong></p>
		<p>【临时素材】和【永久素材】的区别 - 永久素材会在<a target="_blank" href="http://mp.weixin.qq.com/">微信公众平台</a>网站的[素材管理]中显示，而临时素材不会显示，并且在微信服务器仅保留3天，如果下次还需使用，会再次上传素材。永久素材的数量是有上限的，图文消息素材和图片素材的上限为100000（图文消息正文中的图片不限制），其他类型为1000，临时素材没有限制。</p>
		<p>【测试群发】 - 顾名思义就是在APP中预览群发后的效果，需要在[群发设置]绑定测试帐号。</p>
		<p>【立即群发】和【客服群发】的区别 - 目前微信订阅号每日可以群发1条，服务号每月4条，易信和微博每日1条，发完就不能继续群发了（提示：跟公众平台网站共用配额）。而客服群发没有限制次数，但是只能群发给48小时内跟您的公众号有互动的粉丝（比如用户发送消息，关注/订阅事件，点击自定义菜单，扫描二维码事件等）</p>
	</div>
	<!-- end of inside -->
  </div>
  <!-- end of postbox -->
</div>
<?php } ?>
        </div>
        <div class="wptao-grid wptao-sidebar">
          <div class="postbox" style="min-width: inherit;">
            <h3 class="hndle">
              <label for="title">联系作者</label>
            </h3>
            <div class="inside">
              <p>QQ群①：<a href="http://shang.qq.com/wpa/qunwpa?idkey=ad63192d00d300bc5e965fdd25565d6e141de30e4f6b714708486ab0e305f639" target="_blank">88735031</a></p>
              <p>QQ群②：<a href="http://shang.qq.com/wpa/qunwpa?idkey=c2e8566f2ab909487224c1ebfc34d39ea6d84ddff09e2ecb9afa4edde9589391" target="_blank">149451879</a></p>
              <p>QQ：<a href="http://wpa.qq.com/msgrd?v=3&uin=3249892&site=qq&menu=yes" target="_blank">3249892</a></p>
			  <p>微信号：<a href="http://img2.wptao.cn/3/small/62579065gy1fqx11pit2mj20by0bygme.jpg" target="_blank">wptaocom</a></p>
			<p><a href="https://wptao.com/weixin-helper.html" target="_blank">官方网站</a></p>
			</div>
          </div>
          <div class="postbox" style="min-width: inherit;">
            <h3 class="hndle">
              <label for="title">产品推荐</label>
            </h3>
            <div class="inside">
			  <?php $source = urlencode(home_url());?>
              <ol><li><a target="_blank" href="https://wptao.com/product-lists.html?source=<?php echo $source;?>">产品套餐（付费一次拥有以下所有插件，超级划算）</a></li>
			  <li><a target="_blank" href="https://wptao.com/wp-connect.html?source=<?php echo $source;?>">WordPress连接微博专业版（一键登录网站，同步到微博、博客，社会化评论）</a></li>
			  <li><a target="_blank" href="https://wptao.com/wechat.html?source=<?php echo $source;?>">WordPress连接微信(微信机器人)</a></li>
			  <li><a target="_blank" href="https://wptao.com/blog-optimize.html?source=<?php echo $source;?>">WordPress优化与增强插件：博客优化</a></li>
			  <li><a target="_blank" href="https://wptao.com/wptao-sms.html?source=<?php echo $source;?>">WordPress短信服务（支持手机号注册/登录，短信通知等）</a></li>
			  <li><a target="_blank" href="https://wptao.com/wp-taomall.html?source=<?php echo $source;?>">WordPress淘宝客主题：wp-taomall (自动获取商品信息和推广链接)</a></li>
			  <li><a target="_blank" href="https://wptao.com/wptao.html?source=<?php echo $source;?>">WordPress淘宝客插件 (一键获取及自动填充商品信息和推广链接)</a></li>
			  <li><a target="_blank" href="https://wptao.com/wptao-app.html?source=<?php echo $source;?>">WordPress淘宝客APP/小程序</a></li>
			  <li><a target="_blank" href="https://wptao.com/wp-user-center.html?source=<?php echo $source;?>">WordPress用户中心</a></li>
			  <li><a target="_blank" href="https://wptao.com/weixin-cloned.html?source=<?php echo $source;?>">WordPress微信分身（避免微信封杀网站域名）</a></li>
			  </ol>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php }